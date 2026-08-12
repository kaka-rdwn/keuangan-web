<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori keuangan beserta paginasi, pencarian, dan penyaringan tipe.
     *
     * @param  Request  $request  Objek HTTP request yang berisi kriteria pencarian dan filter.
     * @return Response Komponen halaman Inertia untuk daftar kategori.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('category.view');

        $sortBy = $request->input('sort_by') ?? $request->input('sort');
        $sortDir = $request->input('sort_dir') ?? $request->input('direction');

        $filters = $request->only(['search', 'type']);

        $categories = Category::query()
            ->filter($filters)
            ->sortBy($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('categories/index', [
            'categories' => $categories,
            'filters' => [
                'search' => $filters['search'] ?? null,
                'type' => $filters['type'] ?? null,
                'sort_by' => $sortBy ?? 'created_at',
                'sort_dir' => $sortDir ?? 'desc',
            ],
            'can' => [
                'create' => Gate::allows('category.create'),
                'edit' => Gate::allows('category.edit'),
                'delete' => Gate::allows('category.delete'),
            ],
        ]);
    }

    /**
     * Menampilkan halaman formulir pembuatan kategori keuangan baru.
     *
     * @return Response Komponen halaman Inertia untuk pembuatan kategori.
     */
    public function create(): Response
    {
        Gate::authorize('category.create');

        return Inertia::render('categories/create');
    }

    /**
     * Menyimpan kategori keuangan baru ke dalam basis data.
     *
     * @param  StoreCategoryRequest  $request  Objek request pembuatan kategori yang tervalidasi.
     * @return RedirectResponse Respons pengalihan ke daftar kategori dengan notifikasi flash.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('category.create');

        DB::transaction(function () use ($request) {
            Category::create([
                ...$request->validated(),
                'created_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Kategori berhasil ditambahkan.'),
        ]);

        return to_route('categories.index');
    }

    /**
     * Menampilkan halaman formulir penyuntingan kategori keuangan.
     *
     * @param  Category  $category  Model kategori yang akan disunting.
     * @return Response Komponen halaman Inertia untuk penyuntingan kategori.
     */
    public function edit(Category $category): Response
    {
        Gate::authorize('category.edit');

        return Inertia::render('categories/edit', [
            'category' => $category,
        ]);
    }

    /**
     * Memperbarui data kategori keuangan yang ditentukan di dalam basis data.
     *
     * @param  UpdateCategoryRequest  $request  Objek request pembaruan kategori yang tervalidasi.
     * @param  Category  $category  Model kategori yang akan diperbarui.
     * @return RedirectResponse Respons pengalihan ke daftar kategori dengan notifikasi flash.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('category.edit');

        DB::transaction(function () use ($request, $category) {
            $category->update([
                ...$request->validated(),
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Kategori berhasil diperbarui.'),
        ]);

        return to_route('categories.index');
    }

    /**
     * Menghapus kategori keuangan yang ditentukan dari basis data jika tidak digunakan oleh transaksi.
     *
     * @param  Category  $category  Model kategori yang akan dihapus.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash.
     */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('category.delete');

        if ($category->cashflows()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Kategori tidak dapat dihapus karena masih digunakan oleh data transaksi.'),
            ]);

            return back();
        }

        $category->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Kategori berhasil dihapus.'),
        ]);

        return back();
    }
}
