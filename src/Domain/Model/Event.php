<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use DateTimeInterface;

abstract class Event
{
    /** @var Id */
    private $id;

    /** @var EventType */
    private $type;

    /** @var DateTimeInterface */
    private $time;

    /** @var ImpressionCase */
    private $case;

    /** @var PaymentStatus */
    private $paymentStatus;

    public function __construct(
        Id $id,
        EventType $type,
        DateTimeInterface $time,
        ImpressionCase $case,
        PaymentStatus $paymentStatus = null
    ) {
        if ($paymentStatus === null) {
            $paymentStatus = new PaymentStatus();
        }
        $this->id = $id;
        $this->type = $type;
        $this->time = $time;
        $this->case = $case;
        $this->paymentStatus = $paymentStatus;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getType(): EventType
    {
        return $this->type;
    }

    public function getTime(): DateTimeInterface
    {
        return $this->time;
    }

    public function getCase(): ImpressionCase
    {
        return $this->case;
    }

    public function getCaseId(): Id
    {
        return $this->case->getId();
    }

    public function getPublisherId(): Id
    {
        return $this->case->getPublisherId();
    }

    public function getZoneId(): Id
    {
        return $this->case->getZoneId();
    }

    public function getAdvertiserId(): Id
    {
        return $this->case->getAdvertiserId();
    }

    public function getCampaignId(): Id
    {
        return $this->case->getCampaignId();
    }

    public function getBannerId(): Id
    {
        return $this->case->getBannerId();
    }

    public function getImpression(): Impression
    {
        return $this->case->getImpression();
    }

    public function getImpressionId(): Id
    {
        return $this->case->getImpressionId();
    }

    public function getTrackingId(): Id
    {
        return $this->case->getTrackingId();
    }

    public function getUserId(): Id
    {
        return $this->case->getUserId();
    }

    public function getContext(): Context
    {
        return $this->case->getContext();
    }

    public function getContextData(): array
    {
        return $this->case->getContextData();
    }

    public function getKeywords(): array
    {
        return $this->case->getKeywords();
    }

    public function getHumanScore(): float
    {
        return $this->case->getHumanScore();
    }

    public function getPaymentStatus(): PaymentStatus
    {
        return $this->paymentStatus;
    }
}
