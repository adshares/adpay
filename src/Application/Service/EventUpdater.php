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
    public function updateViews(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $views
    ): int;

    /**
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     * @param EventCollection $click
     *
     * @return int
     */
    public function updateClicks(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $click
    ): int;

    /**
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     * @param EventCollection $conversions
     *
     * @return int
     */
    public function updateConversions(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $conversions
    ): int;
}
