<?php

declare(strict_types=1);

namespace App\UI\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

trait PaginationAwareTrait
{
    private function validatePaginationRequest(Request $request, $maxLimit): void
    {
        $limit = $request->get('limit');
        $offset = $request->get('offset');

        if ($limit !== null) {
            if (!preg_match('/^\d+$/', $limit)) {
                throw new UnprocessableEntityHttpException('Limit must be numeric');
            }
            if ((int)$limit <= 0) {
                throw new UnprocessableEntityHttpException('Limit must be greater than 0');
            }
            if ((int)$limit > $maxLimit) {
                throw new UnprocessableEntityHttpException(sprintf('Limit must be lower than %d', $maxLimit));
            }
        }

        if ($offset !== null) {
            if (!preg_match('/^\d+$/', $offset)) {
                throw new UnprocessableEntityHttpException('Offset must be numeric');
            }
        }
    }
}
