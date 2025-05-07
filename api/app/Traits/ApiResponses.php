<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ApiResponses
{
    protected function ok()
    {
        return response()->noContent(Response::HTTP_OK);
    }

    protected function created()
    {
        return response()->noContent(Response::HTTP_CREATED);
    }

    protected function accepted()
    {
        return response()->noContent(Response::HTTP_ACCEPTED);
    }

    protected function noContent()
    {
        return response()->noContent();
    }

    protected function unprocessableEntity($message)
    {
        return abort(Response::HTTP_UNPROCESSABLE_ENTITY, $message);
    }

    protected function unauthorized($message)
    {
        return abort(Response::HTTP_UNAUTHORIZED, $message);
    }

    protected function forbidden($message)
    {
        return abort(Response::HTTP_FORBIDDEN, $message);
    }

    protected function success($data = [], $code = 200)
    {
        return response()->json([
            'data' => $data,
        ], $code);
    }
}
