<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Confirma que a senha informada pertence ao usuario autenticado.
     *
     * @throws ValidationException
     */
    public function confirm(): void
    {
        $credentials = [
            'email' => $this->user()->email,
            'password' => $this->string('password')->toString(),
        ];

        if (! Auth::guard('web')->validate($credentials)) {
            throw ValidationException::withMessages([
                'password' => trans('auth.password'),
            ]);
        }
    }
}
