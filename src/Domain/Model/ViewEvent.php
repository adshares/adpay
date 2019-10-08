<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Id;

final class ViewEvent extends Event
{
    public function __construct(Id $id)
    {
        parent::__construct($id, EventType::createView());
    }
}
