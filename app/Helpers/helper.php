<?php

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

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

/**
 * custom pagination
 */
if (!function_exists('paginate')) {
    function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: Paginator::resolveCurrentPage();
        $items = Collection::make($items);
        $result = $items->slice(($page - 1) * $perPage, $perPage)->values();
        return new LengthAwarePaginator($result, $items->count(), $perPage, $page, $options);
    }
}
