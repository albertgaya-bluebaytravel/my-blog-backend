<?php

namespace App\Http\Requests\Posts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\traits\requests\PostCommentRequestTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostCommentReplyUpdateRequest extends FormRequest
{
    use PostCommentRequestTrait {
        withValidator as parentWithValidator;
    }

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

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $this->parentWithValidator($validator);

        $validator->after(function () {
            if (!$this->comment->is($this->reply->parent)) {
                throw new NotFoundHttpException('Unable to find comment data!');
            }
        });
    }
}
