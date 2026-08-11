<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Scope untuk mengambil daftar role ringkas untuk kebutuhan dropdown pilihan.
     *
     * @param  Builder<Role>  $query
     * @return Builder<Role>
     */
    public function scopeForDropdown(Builder $query): Builder
    {
        return $query->select(['id', 'name', 'description'])->orderBy('name');
    }

    /**
     * Relasi ke User (One-to-Many)
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
