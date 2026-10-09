<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'     => ['required', Rule::in(['IN', 'OUT', 'ADJUST'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type     = $this->input('type');
            $quantity = (int) $this->input('quantity', 0);
            $product  = $this->route('product');

            if (!$product || $validator->errors()->has('quantity')) {
                return;
            }

            if ($type === 'OUT' && $product->stock_quantity < $quantity) {
                $validator->errors()->add(
                    'quantity',
                    "在庫不足で出庫できません。現在庫数: {$product->stock_quantity}"
                );
            }

            if ($type === 'ADJUST' && $product->stock_quantity < $quantity) {
                $validator->errors()->add(
                    'quantity',
                    "調整数量が現在庫数（{$product->stock_quantity}）を超えています。"
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'type.required'     => '操作種別を選択してください。',
            'quantity.required' => '数量を入力してください。',
            'quantity.integer'  => '数量は整数で入力してください。',
            'quantity.min'      => '数量は1以上で入力してください。',
        ];
    }
}
