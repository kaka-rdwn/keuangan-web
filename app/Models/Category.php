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
