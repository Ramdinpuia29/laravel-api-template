<?php

namespace App\Services;

use Illuminate\Http\Request;

class PaginationService
{
  public function getPaginationData(Request $request): array
  {
    $page = 1;
    $perPage = 15;
    $orderBy = 'id';
    $descending = 'true';

    $page = $request->get('page', $page);
    $perPage = $request->get('perPage', $perPage);
    $orderBy = $request->get('orderBy', $orderBy);
    $order = $request->input('descending') === 'true' ? 'desc' : 'asc';

    $paginationData = [
      'page' => $page,
      'perPage' => $perPage,
      'orderBy' => $orderBy,
      'order' => $order,
    ];

    return $paginationData;
  }
}
