<?php

namespace Lambda\Support;

use Illuminate\Support\Facades\DB;

/**
 * Connection-aware database helpers.
 *
 * NOTE: the class is called Driver, not Db — PHP names are case-insensitive,
 * so a class named Db would collide with `use Illuminate\Support\Facades\DB`.
 *
 * Never use env('DB_CONNECTION') at runtime: once `php artisan config:cache`
 * has run, Laravel does not load the .env file at all and env() returns null,
 * which silently sends every driver down the MySQL code path.
 */
class Driver
{
    /** Driver name of the given (or default) connection: pgsql|mysql|sqlsrv|sqlite|mongodb */
    public static function driver(?string $connection = null): string
    {
        return (string) DB::connection($connection)->getDriverName();
    }

    public static function is(string $driver, ?string $connection = null): bool
    {
        return static::driver($connection) === $driver;
    }

    public static function isPgsql(?string $connection = null): bool
    {
        return static::is('pgsql', $connection);
    }

    public static function database(?string $connection = null): string
    {
        return (string) DB::connection($connection)->getDatabaseName();
    }

    /**
     * Default schema for the connection.
     * PostgreSQL: first entry of the configured search_path (defaults to "public").
     * SQL Server: "dbo". Other drivers have no schema concept here.
     */
    public static function schema(?string $connection = null): string
    {
        $override = config('lambda.schema');

        if (is_string($override) && $override !== '') {
            return $override;
        }

        $driver = static::driver($connection);

        if ($driver === 'pgsql') {
            $name = $connection ?: config('database.default');
            $searchPath = config("database.connections.{$name}.search_path")
                ?? config("database.connections.{$name}.schema")
                ?? 'public';

            if (is_array($searchPath)) {
                $first = reset($searchPath);
            } else {
                $first = explode(',', (string) $searchPath)[0];
            }

            $first = trim((string) $first, " \t\"'");

            return $first !== '' ? $first : 'public';
        }

        if ($driver === 'sqlsrv') {
            return 'dbo';
        }

        return '';
    }

    /**
     * Qualify a Lambda platform table with its schema where the driver needs it.
     * Replaces the repeated `pgsql ? 'public.api_config' : 'api_config'` branches.
     */
    public static function qualify(string $table, ?string $connection = null): string
    {
        if (str_contains($table, '.')) {
            return $table;
        }

        $schema = static::schema($connection);

        return $schema !== '' ? $schema . '.' . $table : $table;
    }

    /** Split "schema.table" into [schema, table], falling back to the connection default. */
    public static function splitTable(string $table, ?string $connection = null): array
    {
        $parts = explode('.', $table);

        if (count($parts) >= 2) {
            $name = array_pop($parts);

            return [implode('.', $parts), $name];
        }

        return [static::schema($connection), $parts[0]];
    }
}
