<?php

namespace App\Http\Controllers\Api\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait AppliesQueryOptions
{
    /**
     * Apply pagination, sorting, search, and filtering to a query.
     */
    protected function applyQueryOptions(
        Builder $query,
        Request $request,
        array $searchableFields = [],
        array $allowedFilters = []
    ): LengthAwarePaginator {
        // 1. Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field) {
                    if (str_contains($field, '.')) {
                        [$relation, $relField] = explode('.', $field);
                        $q->orWhereHas($relation, function (Builder $relQ) use ($relField, $search) {
                            $relQ->where($relField, 'like', '%'.$search.'%');
                        });
                    } else {
                        $q->orWhere($field, 'like', '%'.$search.'%');
                    }
                }
            });
        }

        // 2. Filters
        if ($request->has('filter') && is_array($request->input('filter'))) {
            $filters = $request->input('filter');
            foreach ($filters as $field => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                if ($field === 'class_id' && in_array('class_id', $allowedFilters)) {
                    $query->whereHas('classes', function (Builder $q) use ($value) {
                        $q->where('classes.id', $value);
                    });

                    continue;
                }

                if (in_array($field, $allowedFilters)) {
                    $query->where($field, $value);
                }
            }
        }

        // 3. Sorting
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');

        if (! in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        if (preg_match('/^[a-zA-Z0-9_]+$/', $sort)) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 4. Pagination
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 15;
        }

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Format paginated resource response to match API documentation.
     */
    protected function respondWithPagination(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $message
    ): JsonResponse {
        $resourceCollection = $resourceClass::collection($paginator->items());
        $data = $resourceCollection->response()->getData(true)['data'] ?? [];

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'sort' => [
                    'field' => request()->input('sort', 'created_at'),
                    'order' => request()->input('order', 'desc'),
                ],
                'filters' => request()->input('filter', (object) []),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
