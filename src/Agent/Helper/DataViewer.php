<?php

namespace Lambda\Agent\Helper;

trait DataViewer
{
    public function scopeSearchPaginateAndOrder($query)
    {
        $search = request()->get('search');
        if ($search !== null && $search !== '') {
            $searchableColumns = array_filter(static::$columns, function ($column) {
                return $column !== 'id' && !str_ends_with($column, '_at');
            });

            $query->where(function ($q) use ($searchableColumns, $search) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $sort = request()->get('sort');
        if ($sort && in_array($sort, static::$columns, true)) {
            $query->orderBy($sort, request()->get('direction', 'desc'));
        } else {
            $query->orderByDesc($this->getKeyName());
        }

        return $query->paginate((int)request()->get('paginate', 15));
    }
}
