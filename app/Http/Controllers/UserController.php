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

        $search = $request->input('search');
        $roleName = $request->input('role');

        $users = User::query()
            ->with('role')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleName, function ($query, $roleName) {
                $query->whereHas('role', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::select('id', 'name', 'description')->get();

        return Inertia::render('users/index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'role' => $roleName,
            ],
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

            if ($role->name === 'Admin') {
                $newUser->permissions()->sync(Permission::pluck('id'));
            } else {
                $defaultPermissions = Permission::whereIn('name', [
                    'cashflow.view',
                    'cashflow.create',
                    'cashflow.edit',
                    'cashflow.delete',
                    'category.view',
                ])->pluck('id');

                $newUser->permissions()->sync($defaultPermissions);
            }

            return $newUser;
        });

        Mail::to($user->email)->send(new UserCreatedMail($user, $plainPassword));

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan dan email kredensial telah dikirim.');
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

            if ($role->name === 'Admin') {
                $user->permissions()->sync(Permission::pluck('id'));
            } else {
                $defaultPermissions = Permission::whereIn('name', [
                    'cashflow.view',
                    'cashflow.create',
                    'cashflow.edit',
                    'cashflow.delete',
                    'category.view',
                ])->pluck('id');

                $user->permissions()->sync($defaultPermissions);
            }
        });

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
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
