<?php

namespace App\Traits;

trait ApiResponses
{
    protected function ok($message)
    {
        return $this->success([], $message, 200);
    }

    protected function created($message)
    {
        return $this->success([], $message, 201);
    }

    protected function accepted($message)
    {
        return $this->success([], $message, 202);
    }

    protected function noContent()
    {
        return $this->success([], '', 204);
    }

    protected function unauthorized($message)
    {
        return $this->error($message, 401);
    }

    protected function forbidden($message)
    {
        return $this->error($message, 403);
    }

    protected function success($data = [], $message = '', $code = 200)
    {
        return response()->json([
            'message' => $message,
            'success' => true,
            'status' => $code,
            'data' => $data,
        ], $code);
    }

    protected function error($message, $code)
    {
        return response()->json([
            'message' => $message,
            'success' => false,
            'status' => $code,
            'data' => [],
        ], $code);
    }
}
