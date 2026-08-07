<?php

namespace App\Http\Controllers;

use App\Enums\CashflowType;
use App\Http\Requests\Cashflow\StoreCashflowRequest;
use App\Http\Requests\Cashflow\UpdateCashflowRequest;
use App\Models\Cashflow;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CashflowController extends Controller
{
    /**
     * Display a listing of cashflows with filters, summary, and pagination.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('cashflow.view');

        $search = $request->input('search');
        $type = $request->input('type');
        $categoryId = $request->input('category_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sort = $request->input('sort', 'transaction_date');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'amount', 'type', 'transaction_date', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'transaction_date';
        }

        $allowedDirections = ['asc', 'desc'];
        if (! in_array($direction, $allowedDirections, true)) {
            $direction = 'desc';
        }

        $query = Cashflow::query()
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, fn ($q, $type) => $q->where('type', $type))
            ->when($categoryId, fn ($q, $catId) => $q->where('category_id', $catId))
            ->when($dateFrom, fn ($q, $from) => $q->whereDate('transaction_date', '>=', $from))
            ->when($dateTo, fn ($q, $to) => $q->whereDate('transaction_date', '<=', $to));

        // Calculate summary from filtered records
        $summaryQuery = clone $query;
        $totalInflow = (int) (clone $summaryQuery)->where('type', CashflowType::INFLOW->value)->sum('amount');
        $totalOutflow = (int) (clone $summaryQuery)->where('type', CashflowType::OUTFLOW->value)->sum('amount');
        $netBalance = $totalInflow - $totalOutflow;

        $cashflows = $query->with('category')
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->select(['id', 'name', 'type'])
            ->orderBy('name')
            ->get();

        return Inertia::render('cashflows/index', [
            'cashflows' => $cashflows,
            'categories' => $categories,
            'summary' => [
                'total_inflow' => $totalInflow,
                'total_outflow' => $totalOutflow,
                'net_balance' => $netBalance,
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'category_id' => $categoryId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'can' => [
                'create' => Gate::allows('cashflow.create'),
                'edit' => Gate::allows('cashflow.edit'),
                'delete' => Gate::allows('cashflow.delete'),
            ],
        ]);
    }

    /**
     * Store a newly created cashflow transaction in storage.
     */
    public function store(StoreCashflowRequest $request): RedirectResponse
    {
        Gate::authorize('cashflow.create');

        $category = Category::findOrFail((int) $request->input('category_id'));
        $requestedType = $request->enum('type', CashflowType::class);

        if ($category->type !== $requestedType) {
            return back()->withErrors([
                'category_id' => __('Tipe kategori tidak sesuai dengan tipe transaksi yang dipilih.'),
            ]);
        }

        DB::transaction(function () use ($request) {
            Cashflow::create([
                ...$request->validated(),
                'created_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Transaksi berhasil dicatat.'),
        ]);

        return back();
    }

    /**
     * Update the specified cashflow transaction in storage.
     */
    public function update(UpdateCashflowRequest $request, Cashflow $cashflow): RedirectResponse
    {
        Gate::authorize('cashflow.edit');

        $category = Category::findOrFail((int) $request->input('category_id'));
        $requestedType = $request->enum('type', CashflowType::class);

        if ($category->type !== $requestedType) {
            return back()->withErrors([
                'category_id' => __('Tipe kategori tidak sesuai dengan tipe transaksi yang dipilih.'),
            ]);
        }

        DB::transaction(function () use ($request, $cashflow) {
            $cashflow->update([
                ...$request->validated(),
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Transaksi berhasil diperbarui.'),
        ]);

        return back();
    }

    /**
     * Remove the specified cashflow transaction from storage.
     */
    public function destroy(Cashflow $cashflow): RedirectResponse
    {
        Gate::authorize('cashflow.delete');

        $cashflow->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Transaksi berhasil dihapus.'),
        ]);

        return back();
    }
}
