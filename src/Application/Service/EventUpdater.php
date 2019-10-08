<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Service;

use Adshares\AdPay\Domain\Model\ClickEventCollection;
use Adshares\AdPay\Domain\Model\ConversionEventCollection;
use Adshares\AdPay\Domain\Model\ViewEventCollection;
use DateTimeInterface;

interface EventUpdater
{
    /**
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     * @param ViewEventCollection $views
     *
     * @return int
     */
    public function updateViews(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        ViewEventCollection $views
    ): int;

    /**
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     * @param ClickEventCollection $click
     *
     * @return int
     */
    public function updateClicks(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        ClickEventCollection $click
    ): int;

    /**
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     * @param ConversionEventCollection $conversions
     *
     * @return int
     */
    public function updateConversions(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        ConversionEventCollection $conversions
    ): int;
}
