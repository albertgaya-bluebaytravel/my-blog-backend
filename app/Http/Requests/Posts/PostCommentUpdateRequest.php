<?php

namespace App\Http\Requests\Posts;

use App\traits\requests\PostCommentReplyRequestTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class PostCommentUpdateRequest extends FormRequest
{
    use PostCommentReplyRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && $this->comment->user->is(Auth::user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'body' => ['string']
        ];
    }
}
