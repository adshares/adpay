<?php

declare(strict_types=1);

namespace App\Application\Command;

use DateTime;

interface DataCleaner
{
    public function cleanEvents(DateTime $dateTo): int;

    public function cleanReports(DateTime $dateTo): int;
}
