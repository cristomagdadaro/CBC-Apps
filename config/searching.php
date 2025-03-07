<?php

return [
    'page' => 'sometimes|integer|min:1',
    'per_page' => 'sometimes|integer|min:1',
    'sort' => 'sometimes|string',
    'order' => 'sometimes|string|in:asc,desc',
    'search' => 'sometimes',
    'filter' => 'sometimes|string',
    'is_exact' => 'sometimes|string|in:true,false',

    'not' => 'sometimes|string',
    'exact' => 'sometimes|string',
    'or' => 'sometimes|string',
    'filter_by_parent_id' => 'sometimes|integer',
    'filter_by_parent_column' => 'sometimes|string',

    'with' => 'sometimes|string',
    'count' => 'sometimes|string',
];
