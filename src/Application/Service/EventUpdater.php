<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Service;

use Adshares\AdPay\Domain\Model\EventCollection;
use DateTimeInterface;

interface EventUpdater
{
    /**
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     * @param EventCollection $views
     *
     * @return int
     */
    public function update(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $views
    ): int;
}
