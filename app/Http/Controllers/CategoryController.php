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

        $search = $request->input('search');
        $type = $request->input('type');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'type', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $allowedDirections = ['asc', 'desc'];
        if (! in_array($direction, $allowedDirections, true)) {
            $direction = 'desc';
        }

        $categories = Category::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('categories/index', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'canCreate' => Gate::allows('category.create'),
            'canEdit' => Gate::allows('category.edit'),
            'canDelete' => Gate::allows('category.delete'),
        ]);
    }

    /**
     * Menyimpan kategori keuangan baru ke dalam basis data.
     *
     * @param  StoreCategoryRequest  $request  Objek request pembuatan kategori yang tervalidasi.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash toast.
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

        return back();
    }

    /**
     * Memperbarui data kategori keuangan yang ditentukan di dalam basis data.
     *
     * @param  UpdateCategoryRequest  $request  Objek request pembaruan kategori yang tervalidasi.
     * @param  Category  $category  Model kategori yang akan diperbarui.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash toast.
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

        return back();
    }

    /**
     * Menghapus kategori keuangan yang ditentukan dari basis data jika tidak digunakan oleh transaksi.
     *
     * @param  Category  $category  Model kategori yang akan dihapus.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash toast.
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
