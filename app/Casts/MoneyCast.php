<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<int|float, int|float|string>
 */
class MoneyCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model value (sen in DB -> Rupiah float/int).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): int|float
    {
        if ($value === null) {
            return 0;
        }

        $result = (float) $value / 100;

        return fmod($result, 1) === 0.0 ? (int) $result : $result;
    }

    /**
     * Transform the attribute to its underlying model value (Rupiah -> sen in DB).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if (is_int($value) || is_float($value)) {
            return (int) round($value * 100);
        }

        if (is_string($value)) {
            $str = trim($value);

            if (str_contains($str, ',')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                if (substr_count($str, '.') > 1 || preg_match('/^\s*(?:Rp\.?\s*)?[\d]{1,3}(?:\.[\d]{3})+$/i', $str) || preg_match('/\.\d{3}$/', $str)) {
                    $str = str_replace('.', '', $str);
                }
            }

            $cleaned = preg_replace('/[^\d.-]/', '', $str);

            if ($cleaned !== '' && $cleaned !== null && is_numeric($cleaned)) {
                return (int) round((float) $cleaned * 100);
            }
        }

        return 0;
    }
}
