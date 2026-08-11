<?php

namespace App\Models;

use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Permission extends Model
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Mengambil seluruh data permission dan mengelompokkannya berdasarkan modul fitur.
     *
     * @return Collection<int, array{key: string, name: string, items: Collection<int, mixed>}>
     */
    public static function getGroupedPermissions(): Collection
    {
        $permissions = static::select('id', 'name', 'display_name', 'description')->get();

        return $permissions->groupBy(function (Permission $permission) {
            return explode('.', $permission->name)[0];
        })->map(function (Collection $items, string $groupKey) {
            $groupName = match ($groupKey) {
                'cashflow' => 'Manajemen Cashflow',
                'category' => 'Kategori Keuangan',
                'user' => 'Manajemen Pengguna',
                default => ucfirst($groupKey),
            };

            return [
                'key' => $groupKey,
                'name' => $groupName,
                'items' => $items->values(),
            ];
        })->values();
    }

    /**
     * Relasi Many-to-Many ke User via tabel pivot permission_user
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }
}
