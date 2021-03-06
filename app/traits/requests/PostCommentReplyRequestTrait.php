<?php

namespace App\traits\requests;

use Illuminate\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait PostCommentReplyRequestTrait
{
    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function () {
            if ($this->post && $this->comment && !$this->post->is($this->comment->post)) {
                throw new NotFoundHttpException('Unable to find comment data!');
            }

            if ($this->comment && $this->reply && !$this->comment->is($this->reply->parent)) {
                throw new NotFoundHttpException('Unable to find reply data!');
            }
        });
    }
}
