<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CashflowType;
use Database\Factories\CashflowFactory;
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
        ];
    }

    /**
     * Relasi ke Category (Belongs-to)
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
     * Relasi ke User pengubah transaksi
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
