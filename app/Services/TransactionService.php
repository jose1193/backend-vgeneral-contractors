<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    public function handleTransaction(callable $callback, string $context = '')
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();
            return $result; // Solo devuelve el resultado
        } catch (Exception $ex) {
            DB::rollBack();
            $this->handleException($ex, $context);
            throw $ex; // Re-lanza la excepción para que el controlador la maneje
        }
    }

    private function handleException(Exception $e, string $context): void
    {
        Log::error("Error occurred while {$context}: " . $e->getMessage(), [
            'exception' => $e,
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'context' => $context
        ]);
    }
}
