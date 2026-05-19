<?php

namespace App\Services\Compliance\Debug;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormDebugger
{
    public static function start(string $formCode)
    {
        DB::enableQueryLog();

        Log::info("🔍 Compliance Debug Start", [
            'form' => $formCode,
            'time' => now()->toDateTimeString()
        ]);
    }

    public static function end(string $formCode, $rows)
    {
        $queries = DB::getQueryLog();

        Log::info("📊 Compliance Debug Result", [
            'form' => $formCode,
            'rows_returned' => is_array($rows) ? count($rows) : 0,
            'queries' => $queries
        ]);

        DB::disableQueryLog();
    }
}
