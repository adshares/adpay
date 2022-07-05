<?php

declare(strict_types=1);

namespace App\Domain\Repository;

interface PaymentRepository
{
    public function fetchByReportId(int $reportId, ?int $limit = null, ?int $offset = null): iterable;

    public function saveAllRaw(int $reportId, array $payments): int;

    public function deleteByReportId(int $reportId): int;
}
