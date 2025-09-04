<?php

namespace Kang\HelloPackage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemLogDB
{
    public static string $database;
    public static string $table;
    public function insert(string $database, string $table, array $data): void
    {
        if (DB::connection($database)->getPdo() === null) {
            throw new \Exception("無法連接到資料庫：{$database}");
        }

        if (!Schema::connection($database)->hasTable($table)) {
            throw new \Exception("資料表不存在：{$table}");
        }

        DB::connection($database)
            ->table($table)
            ->insert($data);
    }
}
