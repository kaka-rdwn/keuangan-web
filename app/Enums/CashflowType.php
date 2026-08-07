<?php

namespace App\Enums;

enum CashflowType: string
{
    case INFLOW = 'inflow';
    case OUTFLOW = 'outflow';

    /**
     * Dapatkan label yang ramah pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::INFLOW => 'Pemasukan',
            self::OUTFLOW => 'Pengeluaran',
        };
    }

    /**
     * Dapatkan warna penanda UI (badge color).
     */
    public function color(): string
    {
        return match ($this) {
            self::INFLOW => 'success',
            self::OUTFLOW => 'danger',
        };
    }
}
