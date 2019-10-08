<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;

abstract class Event
{
    /** @var Id */
    private $id;

    /** @var EventType */
    private $type;

    /** @var Id */
    private $caseId;

    /** @var DateTimeInterface */
    private $time;

    /** @var Id */
    private $publisherId;

    /** @var Id */
    private $zoneId;

    /** @var Id */
    private $advertiserId;

    /** @var Id */
    private $campaignId;

    /** @var Id */
    private $bannerId;

    /** @var Id */
    private $trackingId;

    /** @var Id */
    private $userId;

    /** @var float */
    private $humanScore;

    /** @var array */
    private $userData;

    /** @var PaymentStatus */
    private $paymentStatus;

    public function __construct(Id $id, EventType $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getType(): EventType
    {
        return $this->type;
    }
}
