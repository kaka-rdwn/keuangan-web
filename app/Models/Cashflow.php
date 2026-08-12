<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CashflowType;
use Database\Factories\CashflowFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $amount
 * @property CashflowType $type
 * @property int|null $category_id
 * @property Carbon|string|null $transaction_date
 * @property string|null $description
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read User|null $creator
 * @property-read User|null $updater
 */
class Cashflow extends Model
{
    /** @use HasFactory<CashflowFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'type',
        'category_id',
        'transaction_date',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'amount' => MoneyCast::class,
            'type' => CashflowType::class,
            'transaction_date' => 'date:Y-m-d',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope untuk menyaring data arus kas berdasarkan kriteria filter (search, type, category_id, date_from, date_to).
     *
     * @param  Builder<Cashflow>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Cashflow>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filters['category_id'] ?? null, fn (Builder $q, mixed $catId) => $q->where('category_id', $catId))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('transaction_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('transaction_date', '<=', $to));
    }

    /**
     * Scope untuk mengurutkan data arus kas dengan validasi kolom dan arah pengurutan.
     *
     * @param  Builder<Cashflow>  $query
     * @return Builder<Cashflow>
     */
    public function scopeSortBy(Builder $query, ?string $sort = null, ?string $direction = null): Builder
    {
        $allowedSorts = ['name', 'amount', 'type', 'transaction_date', 'created_at'];
        $sortColumn = (is_string($sort) && in_array($sort, $allowedSorts, true)) ? $sort : 'transaction_date';

        $allowedDirections = ['asc', 'desc'];
        $sortDirection = (is_string($direction) && in_array($direction, $allowedDirections, true)) ? $direction : 'desc';

        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Relasi ke Category (Belongs-to)
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * Relasi ke User pembuat transaksi
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias relasi ke User pembuat transaksi
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User pengubah transaksi
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Alias relasi ke User pengubah transaksi
     *
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
