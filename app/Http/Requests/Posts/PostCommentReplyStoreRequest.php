<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use App\traits\requests\PostCommentReplyRequestTrait;

class PostCommentReplyStoreRequest extends FormRequest
{
    use PostCommentReplyRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
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
