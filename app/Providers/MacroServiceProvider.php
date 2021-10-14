<?php

namespace App\Providers;

use App\Enums\StatusCodeEnum;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('jsonSuccess', function ($data, string $message = '', int $statusCode = StatusCodeEnum::OK) {
            return response()->json([
                'data' => $data,
                'message' => $message
            ], $statusCode);
        });

        Response::macro('jsonError', function ($data, string $message = '', int $statusCode = StatusCodeEnum::UNPROCESSABLE_ENTITY) {
            return response()->json([
                'errors' => $data,
                'message' => $message
            ], $statusCode);
        });
    }
}
