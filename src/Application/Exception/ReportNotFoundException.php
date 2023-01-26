<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Throwable;

class ReportNotFoundException extends FetchingException
{
    public function __construct(int $reportId, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot find report #%d.', $reportId), $code, $previous);
    }
}
