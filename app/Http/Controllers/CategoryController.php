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
     * Display a listing of financial categories with pagination, filtering, and search.
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
            'canManage' => Gate::allows('category.manage'),
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('category.manage');

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
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('category.manage');

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
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('category.manage');

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
