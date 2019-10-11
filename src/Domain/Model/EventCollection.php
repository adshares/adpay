<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\EventType;
use Doctrine\Common\Collections\ArrayCollection;

final class EventCollection extends ArrayCollection
{
    /** @var EventType */
    private $type;

    public function __construct(EventType $type, Event ...$views)
    {
        parent::__construct($views);
        $this->type = $type;
    }

    /**
     * @return EventType
     */
    public function getType(): EventType
    {
        return $this->type;
    }
}
