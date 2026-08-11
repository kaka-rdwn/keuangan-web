<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Mail\UserCreatedMail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna beserta paginasi, pencarian, dan penyaringan peran (role).
     *
     * @param  Request  $request  Objek HTTP request yang berisi kriteria pencarian dan filter.
     * @return Response Komponen halaman Inertia untuk daftar pengguna.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('user.manage');

        $filters = $request->only(['search', 'role']);

        $users = User::query()
            ->with('role')
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('users/index', [
            'users' => $users,
            'roles' => Role::forDropdown()->get(),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'role' => $filters['role'] ?? null,
            ],
        ]);
    }

    /**
     * Menampilkan halaman formulir pembuatan pengguna baru.
     *
     * @return Response Komponen halaman Inertia untuk pembuatan pengguna.
     */
    public function create(): Response
    {
        Gate::authorize('user.manage');

        return Inertia::render('users/create', [
            'roles' => Role::forDropdown()->get(),
        ]);
    }

    /**
     * Menyimpan data pengguna baru ke database dan mengirimkan email kredensial setelah transaksi commit.
     *
     * @param  StoreUserRequest  $request  Form request yang berisi data validasi pengguna baru.
     * @return RedirectResponse Redirect ke halaman indeks pengguna dengan pesan sukses.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('user.manage');

        $validated = $request->validated();
        $plainPassword = $validated['password'];

        $user = DB::transaction(function () use ($validated, $plainPassword) {
            $role = Role::where('name', $validated['role'])->firstOrFail();

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'role_id' => $role->id,
                'email_verified_at' => null,
            ]);

            $newUser->syncRolePermissions($role);

            return $newUser;
        });

        Mail::to($user->email)->send(new UserCreatedMail($user, $plainPassword));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Pengguna berhasil ditambahkan dan email kredensial telah dikirim.'),
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan dan email kredensial telah dikirim.');
    }

    /**
     * Menampilkan halaman formulir penyuntingan pengguna.
     *
     * @param  User  $user  Instance pengguna yang akan disunting.
     * @return Response Komponen halaman Inertia untuk penyuntingan pengguna.
     */
    public function edit(User $user): Response
    {
        Gate::authorize('user.manage');

        $user->load('role');

        return Inertia::render('users/edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => $user->role,
            ],
            'roles' => Role::forDropdown()->get(),
        ]);
    }

    /**
     * Memperbarui data pengguna yang ada di database.
     *
     * @param  UpdateUserRequest  $request  Form request yang berisi data validasi perbaikan pengguna.
     * @param  User  $user  Instance pengguna yang akan diperbarui.
     * @return RedirectResponse Redirect ke halaman indeks pengguna dengan pesan sukses.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('user.manage');

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $user) {
            $role = Role::where('name', $validated['role'])->firstOrFail();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $role->id,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);
            $user->syncRolePermissions($role);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Data pengguna berhasil diperbarui.'),
        ]);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna tertentu dari database.
     *
     * @param  Request  $request  Objek HTTP request.
     * @param  User  $user  Instance pengguna yang akan dihapus.
     * @return RedirectResponse Redirect ke halaman indeks pengguna.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('user.manage');

        if ($user->id === $request->user()?->id) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Anda tidak dapat menghapus akun Anda sendiri.'),
            ]);

            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Pengguna berhasil dihapus.'),
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Menampilkan halaman kelola izin (permission) langsung untuk pengguna tertentu.
     *
     * @param  User  $user  Instance pengguna yang permission-nya akan dikelola.
     * @return Response Komponen halaman Inertia untuk kelola permission pengguna.
     */
    public function permissions(User $user): Response
    {
        Gate::authorize('user.manage');

        $user->load('role');

        $groupedPermissions = Permission::getGroupedPermissions();
        $userPermissionIds = $user->permissions()->pluck('permissions.id')->toArray();

        return Inertia::render('users/permissions', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ],
            'userRole' => $user->role?->name,
            'groupedPermissions' => $groupedPermissions,
            'userPermissionIds' => $userPermissionIds,
        ]);
    }

    /**
     * Memperbarui izin (permission) langsung pengguna di database.
     *
     * @param  Request  $request  Objek HTTP request yang berisi array ID permission.
     * @param  User  $user  Instance pengguna yang permission-nya diperbarui.
     * @return RedirectResponse Redirect ke halaman indeks pengguna dengan pesan sukses.
     */
    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('user.manage');

        $validated = $request->validate([
            'permissions' => 'present|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->permissions()->sync($validated['permissions']);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Permission pengguna berhasil diperbarui.'),
        ]);

        return redirect()->route('users.index')->with('success', 'Permission pengguna berhasil diperbarui.');
    }
}
