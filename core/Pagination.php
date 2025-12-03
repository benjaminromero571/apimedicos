<?php

declare(strict_types=1);

namespace Core;

class Pagination
{
    public static function build(?int $limit, int $offset, int $total): array
    {
        $effectiveLimit = ($limit !== null && $limit > 0) ? $limit : $total;
        $effectiveLimit = $effectiveLimit > 0 ? $effectiveLimit : 1;
        $page = (int)floor($offset / $effectiveLimit) + 1;
        $totalPages = (int)ceil($total / $effectiveLimit);

        return [
            'limit' => $effectiveLimit,
            'offset' => $offset,
            'total' => $total,
            'page' => $page,
            'total_pages' => $totalPages,
        ];
    }
}
