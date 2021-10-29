<?php

namespace App\Http\Requests\Users;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && $this->user->is(Auth::user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'email' => ['max:255', 'email', Rule::unique('users')->ignore($this->user->id)],
            'password_change' => ['boolean'],
            'password' => ['required', 'max:255', 'confirmed'],
        ];
    }
}
