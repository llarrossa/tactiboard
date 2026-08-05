<?php

namespace App\Http\Requests;

use App\Enums\BoardCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBoardRequest extends FormRequest
{
    /**
     * Qualquer usuário autenticado pode criar a própria prancheta. A restrição
     * de propriedade só existe sobre pranchetas já criadas (BoardPolicy).
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
