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

        $sortBy = $request->input('sort_by') ?? $request->input('sort');
        $sortDir = $request->input('sort_dir') ?? $request->input('direction');

        $filters = $request->only(['search', 'type', 'category_id', 'date_from', 'date_to']);

        $query = Cashflow::query()->filter($filters);

        $summary = $this->cashflowService->calculateSummary($query);

        $cashflows = $query->with('category')
            ->sortBy($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        $categories = Category::forDropdown()->get();

        return Inertia::render('cashflows/index', [
            'cashflows' => $cashflows,
            'categories' => $categories,
            'summary' => $summary,
            'filters' => [
                'search' => $filters['search'] ?? null,
                'type' => $filters['type'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
                'sort_by' => $sortBy ?? 'transaction_date',
                'sort_dir' => $sortDir ?? 'desc',
            ],
            'can' => [
                'create' => Gate::allows('cashflow.create'),
                'edit' => Gate::allows('cashflow.edit'),
                'delete' => Gate::allows('cashflow.delete'),
            ],
        ]);
    }

    /**
     * Menampilkan halaman formulir pembuatan transaksi arus kas baru.
     *
     * @return Response Komponen halaman Inertia untuk pembuatan transaksi arus kas.
     */
    public function create(): Response
    {
        Gate::authorize('cashflow.create');

        return Inertia::render('cashflows/create', [
            'categories' => Category::forDropdown()->get(),
        ]);
    }

    /**
     * Menyimpan transaksi arus kas baru ke dalam penyimpanan basis data.
     *
     * @param  StoreCashflowRequest  $request  Objek request yang terverifikasi dan tervalidasi.
     * @return RedirectResponse Respons pengalihan ke daftar arus kas dengan notifikasi flash.
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

        return to_route('cashflows.index');
    }

    /**
     * Menampilkan halaman formulir penyuntingan transaksi arus kas.
     *
     * @param  Cashflow  $cashflow  Model data transaksi arus kas yang akan disunting.
     * @return Response Komponen halaman Inertia untuk penyuntingan transaksi arus kas.
     */
    public function edit(Cashflow $cashflow): Response
    {
        Gate::authorize('cashflow.edit');

        return Inertia::render('cashflows/edit', [
            'cashflow' => $cashflow->load(['category', 'creator:id,name', 'updater:id,name', 'createdBy:id,name', 'updatedBy:id,name']),
            'categories' => Category::forDropdown()->get(),
        ]);
    }

    /**
     * Memperbarui data transaksi arus kas yang ditentukan di dalam basis data.
     *
     * @param  UpdateCashflowRequest  $request  Objek request pembaruan yang tervalidasi.
     * @param  Cashflow  $cashflow  Model data transaksi arus kas yang akan diperbarui.
     * @return RedirectResponse Respons pengalihan ke daftar arus kas dengan notifikasi flash.
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

        return to_route('cashflows.index');
    }

    /**
     * Menghapus transaksi arus kas yang ditentukan dari basis data (Soft Delete).
     *
     * @param  Cashflow  $cashflow  Model data transaksi arus kas yang akan dihapus.
     * @return RedirectResponse Respons pengalihan kembali dengan notifikasi flash.
     */
    public function destroy(Cashflow $cashflow): RedirectResponse
    {
        Gate::authorize('cashflow.delete');

        DB::transaction(function () use ($cashflow) {
            if (auth()->check()) {
                $cashflow->updated_by = (int) auth()->id();
                $cashflow->save();
            }

            $cashflow->delete();
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Transaksi berhasil dihapus.'),
        ]);

        return back();
    }
}
