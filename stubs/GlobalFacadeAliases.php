<?php

// This package's own code calls Laravel's bare, globally-aliased facade
// names (DB, Route, ...) rather than importing the Illuminate\Support\Facades
// classes directly. Those aliases only exist at runtime, registered by the
// host Laravel app's AliasLoader, so static analysis needs them declared
// here. The taint-sink annotations mark the raw-SQL-string parameters of
// DB's query-execution methods, so Psalm's taint analysis can trace
// untrusted input (e.g. request()->get(...)) flowing into raw SQL.

class DB extends \Illuminate\Support\Facades\DB
{
    /** @psalm-taint-sink sql $query */
    public static function select($query, $bindings = [], $useReadPdo = true)
    {
        return parent::select($query, $bindings, $useReadPdo);
    }

    /** @psalm-taint-sink sql $query */
    public static function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return parent::selectOne($query, $bindings, $useReadPdo);
    }

    /** @psalm-taint-sink sql $query */
    public static function statement($query, $bindings = [])
    {
        return parent::statement($query, $bindings);
    }

    /** @psalm-taint-sink sql $query */
    public static function unprepared($query)
    {
        return parent::unprepared($query);
    }

    /** @psalm-taint-sink sql $value */
    public static function raw($value)
    {
        return parent::raw($value);
    }
}

class Route extends \Illuminate\Support\Facades\Route
{
}
