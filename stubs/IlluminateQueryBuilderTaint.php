<?php

// Supplements Illuminate's query builder (a real vendor class) with
// taint-sink annotations on its raw-SQL-fragment parameters, so Psalm's
// taint analysis can trace untrusted input flowing into these methods
// the way whereRaw()/selectRaw()/etc. are used throughout this package.

namespace Illuminate\Database\Query;

class Builder
{
    /** @psalm-taint-sink sql $sql */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
    }

    /** @psalm-taint-sink sql $sql */
    public function orWhereRaw($sql, $bindings = [])
    {
    }

    /** @psalm-taint-sink sql $expression */
    public function selectRaw($expression, $bindings = [])
    {
    }

    /** @psalm-taint-sink sql $sql */
    public function havingRaw($sql, $bindings = [], $boolean = 'and')
    {
    }

    /** @psalm-taint-sink sql $sql */
    public function orHavingRaw($sql, $bindings = [])
    {
    }
}
