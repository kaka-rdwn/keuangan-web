<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cashflow\StoreCashflowRequest;
use App\Http\Requests\Cashflow\UpdateCashflowRequest;
use App\Models\Cashflow;
use App\Models\Category;
use App\Services\CashflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CashflowController extends Controller
{
    /**
     * Membuat instance CashflowController baru dengan menyuntikkan dependency service.
     *
     * @param  CashflowService  $cashflowService  Layanan bisnis untuk kalkulasi agregat arus kas.
     */
    public function __construct(
        protected CashflowService $cashflowService
    ) {}

    /**
     * Menampilkan daftar transaksi arus kas beserta filter, ringkasan saldo, dan paginasi.
     *
     * @param  Request  $request  Objek HTTP request yang berisi kriteria filter dan pencarian.
     * @return Response Komponen halaman Inertia untuk daftar arus kas.
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

        $summary = $this->cashflowService->calculateSummary($query);

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
            'summary' => $summary,
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
     * Menyimpan transaksi arus kas baru ke dalam penyimpanan basis data.
     *
     * @param  StoreCashflowRequest  $request  Objek request yang terverifikasi dan tervalidasi.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash toast.
     */
    public function store(StoreCashflowRequest $request): RedirectResponse
    {
        Gate::authorize('cashflow.create');

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
     * Memperbarui data transaksi arus kas yang ditentukan di dalam basis data.
     *
     * @param  UpdateCashflowRequest  $request  Objek request pembaruan yang tervalidasi.
     * @param  Cashflow  $cashflow  Model data transaksi arus kas yang akan diperbarui.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash toast.
     */
    public function update(UpdateCashflowRequest $request, Cashflow $cashflow): RedirectResponse
    {
        Gate::authorize('cashflow.edit');

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
     * Menghapus transaksi arus kas yang ditentukan dari basis data (Soft Delete).
     *
     * @param  Cashflow  $cashflow  Model data transaksi arus kas yang akan dihapus.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash toast.
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
