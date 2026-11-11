<?php

namespace Lambda\Puzzle;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lambda\Support\Driver;

trait DBSchema
{
    /**
     * List tables and views for the current connection.
     */
    public static function tables()
    {
        $ignore_tables = [];

        $tables_ = [];
        $views_ = [];

        $driver = Driver::driver();

        if ($driver === 'sqlsrv') {
            $tables = DB::select('SELECT TABLE_NAME, TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES ORDER BY TABLE_NAME');

            foreach ($tables as $t) {
                $tableName = $t->TABLE_NAME;

                if (in_array($tableName, $ignore_tables, true)) {
                    continue;
                }

                if ($t->TABLE_TYPE === 'VIEW') {
                    $views_[] = $tableName;
                } else {
                    $tables_[] = $tableName;
                }
            }
        } elseif ($driver === 'pgsql') {
            // Cross-database references are not supported by PostgreSQL, so the
            // catalog is always read from the CURRENT database without a prefix.
            $tables = DB::select(
                'SELECT table_schema, table_name, table_type
                   FROM information_schema.tables
                  WHERE table_schema NOT IN (?, ?)
                    AND table_schema NOT LIKE ?
                  ORDER BY table_schema, table_name',
                ['information_schema', 'pg_catalog', 'pg_toast%']
            );

            $defaultSchema = Driver::schema();

            foreach ($tables as $t) {
                // Tables in the default schema keep their bare name so that
                // existing Krud/Datagrid schemas built on MySQL keep matching.
                $tableName = $t->table_schema === $defaultSchema
                    ? $t->table_name
                    : $t->table_schema . '.' . $t->table_name;

                if (in_array($tableName, $ignore_tables, true)) {
                    continue;
                }

                if ($t->table_type === 'VIEW') {
                    $views_[] = $tableName;
                } else {
                    $tables_[] = $tableName;
                }
            }
        } elseif ($driver === 'mongodb') {
            $dbName = DB::connection('mongodb')->getMongoDB()->getDatabaseName();
            $cursors = DB::connection('mongodb')->getMongoClient()->{$dbName}->listCollections();

            foreach ($cursors as $collection) {
                $tables_[] = $collection->getName();
            }
        } else {
            $tables = DB::select('SHOW FULL TABLES');
            $databaseName = Driver::database();

            foreach ($tables as $t) {
                $key = "Tables_in_$databaseName";
                $tableName = $t->$key;

                if (in_array($tableName, $ignore_tables, true)) {
                    continue;
                }

                if ($t->Table_type === 'VIEW') {
                    $views_[] = $tableName;
                } else {
                    $tables_[] = $tableName;
                }
            }
        }

        return [
            'tables' => $tables_,
            'views' => $views_,
        ];
    }

    /**
     * Column metadata for one table, normalised to the MySQL-shaped array the
     * Krud / Datagrid / Dataform builders expect:
     *   ['model', 'title', 'dbType', 'table', 'key', 'extra']
     */
    public static function tableMeta($table)
    {
        $data = [];

        // Guard against injection via the table name: a bare identifier or
        // one schema/database-qualified identifier (`schema.table`), nothing else.
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', (string) $table)) {
            return $data;
        }

        try {
            $driver = Driver::driver();

            if ($driver === 'sqlsrv') {
                [$schema, $name] = Driver::splitTable($table);

                $data = DB::select(
                    'SELECT COLUMN_NAME, DATA_TYPE, ORDINAL_POSITION, CHARACTER_MAXIMUM_LENGTH
                       FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                      ORDER BY ORDINAL_POSITION',
                    [$schema, $name]
                );

                if (! $data) {
                    return $data;
                }

                $newData = [];

                foreach ($data as $dcolumn) {
                    $type = '';

                    if ($dcolumn->DATA_TYPE === 'nvarchar') {
                        $type = 'varchar(255)';
                    } elseif ($dcolumn->DATA_TYPE === 'ntext') {
                        $type = 'text';
                    }

                    $newData[] = [
                        'model' => $dcolumn->COLUMN_NAME,
                        'title' => $dcolumn->COLUMN_NAME,
                        'dbType' => $type,
                        'table' => $table,
                        'key' => $dcolumn->ORDINAL_POSITION == 1 ? 'PRI' : '',
                        'extra' => $dcolumn->ORDINAL_POSITION == 1 ? 'auto_increment' : '',
                    ];
                }

                return $newData;
            }

            if ($driver === 'pgsql') {
                [$schema, $name] = Driver::splitTable($table);

                $data = DB::select(
                    "SELECT c.column_name,
                            c.udt_name,
                            c.character_maximum_length,
                            c.numeric_precision,
                            c.numeric_scale,
                            c.is_nullable,
                            c.column_default,
                            c.ordinal_position,
                            c.is_identity,
                            CASE WHEN pk.column_name IS NOT NULL THEN 'PRI' ELSE '' END AS key_type
                       FROM information_schema.columns c
                       LEFT JOIN (
                            SELECT kcu.column_name
                              FROM information_schema.table_constraints tc
                              JOIN information_schema.key_column_usage kcu
                                ON kcu.constraint_name = tc.constraint_name
                               AND kcu.table_schema    = tc.table_schema
                               AND kcu.table_name      = tc.table_name
                             WHERE tc.constraint_type = 'PRIMARY KEY'
                               AND tc.table_schema    = ?
                               AND tc.table_name      = ?
                       ) pk ON pk.column_name = c.column_name
                      WHERE c.table_schema = ? AND c.table_name = ?
                      ORDER BY c.ordinal_position",
                    [$schema, $name, $schema, $name]
                );

                if (! $data) {
                    return $data;
                }

                $newData = [];

                foreach ($data as $dcolumn) {
                    $isSerial = $dcolumn->is_identity === 'YES'
                        || (is_string($dcolumn->column_default) && str_starts_with($dcolumn->column_default, 'nextval('));

                    $newData[] = [
                        'model' => $dcolumn->column_name,
                        'title' => $dcolumn->column_name,
                        'dbType' => static::pgTypeToDbType($dcolumn),
                        'table' => $table,
                        'key' => $dcolumn->key_type,
                        'extra' => $isSerial ? 'auto_increment' : '',
                    ];
                }

                return $newData;
            }

            if ($driver === 'mongodb') {
                $col = DB::collection($table)->first();

                if ($col) {
                    $newData = [];

                    foreach ($col as $key => $val) {
                        $newData[] = [
                            'model' => $key,
                            'title' => $key,
                            'dbType' => 'text',
                            'table' => $table,
                            'key' => '',
                        ];
                    }

                    return $newData;
                }

                return $data;
            }

            // MySQL / MariaDB
            $data = DB::select('SHOW FIELDS FROM ' . static::quoteMysqlTable($table));
        } catch (\Throwable $e) {
            Log::error('Lambda DBSchema::tableMeta failed for table [' . $table . ']: ' . $e->getMessage());

            return [];
        }

        if ($data) {
            $newData = [];

            foreach ($data as $dcolumn) {
                $newData[] = [
                    'model' => $dcolumn->Field,
                    'title' => $dcolumn->Field,
                    'dbType' => $dcolumn->Type,
                    'table' => $table,
                    'key' => $dcolumn->Key,
                    'extra' => $dcolumn->Extra,
                ];
            }

            return $newData;
        }

        return [];
    }

    /**
     * Map a PostgreSQL udt_name onto the MySQL-style type string the builders
     * already know how to interpret. Without this the generated forms pick the
     * wrong widget for every column on PostgreSQL.
     */
    protected static function pgTypeToDbType(object $column): string
    {
        $udt = (string) $column->udt_name;
        $len = $column->character_maximum_length;
        $precision = $column->numeric_precision;
        $scale = $column->numeric_scale;

        switch ($udt) {
            case 'int2':
                return 'smallint(6)';
            case 'int4':
                return 'int(11)';
            case 'int8':
                return 'bigint(20)';
            case 'bool':
                return 'tinyint(1)';
            case 'varchar':
                return 'varchar(' . ($len ?: 255) . ')';
            case 'bpchar':
                return 'char(' . ($len ?: 255) . ')';
            case 'text':
            case 'citext':
                return 'text';
            case 'numeric':
                return $precision !== null
                    ? 'decimal(' . $precision . ',' . ($scale ?: 0) . ')'
                    : 'decimal(10,2)';
            case 'float4':
                return 'float';
            case 'float8':
                return 'double';
            case 'date':
                return 'date';
            case 'time':
            case 'timetz':
                return 'time';
            case 'timestamp':
            case 'timestamptz':
                return 'datetime';
            case 'json':
            case 'jsonb':
                return 'json';
            case 'uuid':
                return 'varchar(36)';
            case 'bytea':
                return 'blob';
            case 'inet':
            case 'cidr':
                return 'varchar(45)';
            default:
                // Arrays arrive as "_int4", enums as the type name.
                return str_starts_with($udt, '_') ? 'text' : 'varchar(255)';
        }
    }

    /**
     * SHOW FIELDS does not accept bindings, so the identifier is escaped instead
     * of interpolated raw.
     */
    protected static function quoteMysqlTable(string $table): string
    {
        return collect(explode('.', $table))
            ->map(fn ($part) => '`' . str_replace('`', '``', $part) . '`')
            ->implode('.');
    }

    public static function getDBSchema()
    {
        $tables = Puzzle::tables();

        $dbSchema = [
            'tableList' => $tables['tables'],
            'viewList' => $tables['views'],
            'tableMeta' => [],
        ];

        foreach ($tables['tables'] as $t) {
            $dbSchema['tableMeta'][$t] = Puzzle::tableMeta($t);
        }

        foreach ($tables['views'] as $t) {
            $dbSchema['tableMeta'][$t] = Puzzle::tableMeta($t);
        }

        return $dbSchema;
    }
}
