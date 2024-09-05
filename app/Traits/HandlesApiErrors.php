<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HandlesApiErrors
{
    protected function handleError(\Exception $e, string $context = ''): JsonResponse
    {
        return response()->json([
            'error' => true,
            'message' => $context . ': ' . $e->getMessage()
        ], 500);
    }
}
