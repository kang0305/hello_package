<?php

namespace Kang\SystemLogPackage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemLogDB
{
    public static string $database;
    public static string $table;
    public static Model $model;
    public static function insert(array $data): void
    {
        if (DB::connection(self::$database)->getPdo() === null) {
            throw new \Exception("無法連接到資料庫：".self::$database);
        }

        if (!Schema::connection(self::$database)->hasTable(self::$table)) {
            throw new \Exception("資料表不存在：".self::$table);
        }

        self::$model::create($data);
    }
}
