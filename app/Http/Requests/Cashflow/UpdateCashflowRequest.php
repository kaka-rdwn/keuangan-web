<?php

namespace App\Http\Requests\Cashflow;

use App\Enums\CashflowType;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCashflowRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna memiliki otoritas untuk memperbarui transaksi arus kas.
     */
    public function authorize(): bool
    {
        return Gate::allows('cashflow.edit');
    }

    /**
     * Mendapatkan aturan validasi yang berlaku untuk permintaan pembaruan transaksi arus kas.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CashflowType::class)],
            'category_id' => ['required', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'amount' => ['required', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Memonitor dan menambahkan validasi tambahan untuk mengonfirmasi kesesuaian tipe kategori dengan tipe transaksi.
     *
     * @param  Validator  $validator  Objek validator Laravel.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $categoryId = $this->input('category_id');
            $typeInput = $this->input('type');

            if (! $categoryId || ! $typeInput) {
                return;
            }

            $category = Category::query()->where('id', (int) $categoryId)->first();
            if (! $category) {
                return;
            }

            $cashflowType = CashflowType::tryFrom((string) $typeInput);
            if (! $cashflowType) {
                return;
            }

            if ($category->type !== $cashflowType) {
                $validator->errors()->add(
                    'category_id',
                    __('Tipe kategori tidak sesuai dengan tipe transaksi yang dipilih.')
                );
            }
        });
    }
}
