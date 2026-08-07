<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<int, int|float|string>
 */
class MoneyCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): int
    {
        return $value !== null ? (int) $value : 0;
    }

    /**
     * Transform the attribute to its underlying model value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value)) {
            if (str_contains($value, ',')) {
                [$integerPart] = explode(',', $value, 2);
                $cleaned = preg_replace('/[^\d-]/', '', $integerPart);
            } else {
                $cleaned = preg_replace('/[^\d-]/', '', $value);
            }

            return ($cleaned !== '' && $cleaned !== null) ? (int) $cleaned : 0;
        }

        return 0;
    }
}
