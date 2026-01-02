<?php

namespace App\Http\Requests;

use App\Enums\StockTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 認証は middleware で制御
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(StockTransactionType::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'type.required' => '操作種別を選択してください',
            'quantity.required' => '数量を入力してください',
            'quantity.min' => '数量は1以上を指定してください',
            'reason.required' => '理由を入力してください',
        ];
    }
}
