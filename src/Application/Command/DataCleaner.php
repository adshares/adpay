<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use DateTime;

interface DataCleaner
{
    public function cleanEvents(DateTime $dateTo): int;

    public function cleanReports(DateTime $dateTo): int;
}
