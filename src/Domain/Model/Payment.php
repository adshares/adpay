<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;

final class Payment
{
    /** @var Id */
    private $id;

    /** @var int */
    private $reportId;

    /** @var EventType */
    private $eventType;

    /** @var Id */
    private $eventId;

    /** @var PaymentStatus */
    private $status;

    /** @var ?int */
    private $value;

    public function __construct()
    {
    }
}
