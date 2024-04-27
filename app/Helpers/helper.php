<?php

if (!function_exists('sendSuccess')) {
    function sendSuccess(array $data, string $message = '', int $status = 200)
    {
        return response()->json([
            'success'  => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }
}

if (!function_exists('sendError')) {
    function sendError(string $message, array $data = [], int $status = 400)
    {
        return response()->json([
            'success'  => false,
            'data' => $data,
            'message' => $message,
        ], $status);
    }
}
