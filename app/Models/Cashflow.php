<?php

namespace App\Models;

use Database\Factories\CashflowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cashflow extends Model
{
    /** @use HasFactory<CashflowFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'type',
        'category_id',
        'description',
        'created_by',
        'updated_by',
    ];

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
