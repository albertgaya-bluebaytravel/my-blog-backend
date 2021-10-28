<?php

namespace App\Http\Requests\Posts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\traits\requests\PostCommentReplyRequestTrait;

class PostCommentReplyUpdateRequest extends FormRequest
{
    use PostCommentReplyRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && $this->reply->user->is(Auth::user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string']
        ];
    }
}
