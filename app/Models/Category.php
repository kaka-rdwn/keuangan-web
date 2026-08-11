<?php

namespace App\Models;

use App\Enums\CashflowType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property CashflowType $type
 * @property string|null $description
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
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
            'type' => CashflowType::class,
        ];
    }

    /**
     * Scope untuk menyaring data kategori berdasarkan kriteria filter (search, type).
     *
     * @param  Builder<Category>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Category>
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
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type));
    }

    /**
     * Scope untuk mengurutkan data kategori dengan validasi kolom dan arah pengurutan.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeSortBy(Builder $query, ?string $sort = null, ?string $direction = null): Builder
    {
        $allowedSorts = ['name', 'type', 'created_at'];
        $sortColumn = (is_string($sort) && in_array($sort, $allowedSorts, true)) ? $sort : 'created_at';

        $allowedDirections = ['asc', 'desc'];
        $sortDirection = (is_string($direction) && in_array($direction, $allowedDirections, true)) ? $direction : 'desc';

        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Scope untuk mengambil kolom ringkas kategori untuk kebutuhan dropdown pilihan.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeForDropdown(Builder $query): Builder
    {
        return $query->select(['id', 'name', 'type'])->orderBy('name');
    }

    /**
     * Relasi ke User pembuat kategori
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User pengubah kategori
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi ke Cashflows (One-to-Many)
     *
     * @return HasMany<Cashflow, $this>
     */
    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class);
    }
}
