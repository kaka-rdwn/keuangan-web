<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $role_id
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Role|null $role
 */
#[Fillable(['name', 'email', 'password', 'role_id', 'email_verified_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Scope untuk menyaring data pengguna berdasarkan kriteria pencarian (nama/email) dan nama role.
     *
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<User>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function (Builder $q, string $roleName) {
                $q->whereHas('role', function (Builder $sub) use ($roleName) {
                    $sub->where('name', $roleName);
                });
            });
    }

    /**
     * Scope untuk mengurutkan data pengguna dengan validasi kolom dan arah pengurutan.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeSortBy(Builder $query, ?string $sort = null, ?string $direction = null): Builder
    {
        $allowedSorts = ['name', 'email', 'role_id', 'email_verified_at', 'created_at'];
        $sortColumn = (is_string($sort) && in_array($sort, $allowedSorts, true)) ? $sort : 'created_at';

        $allowedDirections = ['asc', 'desc'];
        $sortDirection = (is_string($direction) && in_array($direction, $allowedDirections, true)) ? $direction : 'desc';

        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Menyinkronkan permission default pengguna berdasarkan peran (role) yang diberikan.
     */
    public function syncRolePermissions(Role $role): void
    {
        if ($role->name === 'Admin') {
            $this->permissions()->sync(Permission::pluck('id'));
        } else {
            $defaultPermissions = Permission::whereIn('name', [
                'cashflow.view',
                'cashflow.create',
                'cashflow.edit',
                'cashflow.delete',
                'category.view',
            ])->pluck('id');

            $this->permissions()->sync($defaultPermissions);
        }
    }

    /**
     * Relasi ke Role (Belongs-to)
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relasi ke Permissions (Many-to-Many via permission_user)
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Relasi ke Kategori yang dibuat user
     *
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    /**
     * Relasi ke Cashflow yang dibuat user
     *
     * @return HasMany<Cashflow, $this>
     */
    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class, 'created_by');
    }

    /**
     * Periksa apakah pengguna memiliki izin (permission) tertentu.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }
}
