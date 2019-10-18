<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;

final class ViewEvent extends Event
{
    public function __construct(Id $id, DateTimeInterface $time, ImpressionCase $case)
    {
        parent::__construct($id, EventType::createView(), $time, $case);
    }
}
