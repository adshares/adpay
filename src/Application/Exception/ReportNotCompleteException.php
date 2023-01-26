<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Throwable;

class ReportNotCompleteException extends FetchingException
{
    public function __construct(int $reportId, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Report #%d is not complete yet.', $reportId), $code, $previous);
    }
}
