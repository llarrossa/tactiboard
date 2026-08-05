<?php

namespace App\Http\Requests;

use App\Enums\BoardCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBoardRequest extends FormRequest
{
    /**
     * A propriedade é verificada pela BoardPolicy, aplicada na rota via
     * `can:update,board`. Aqui só resta validar a entrada.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::enum(BoardCategory::class)],
        ];
    }
}
