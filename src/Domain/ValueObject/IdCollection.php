<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Doctrine\Common\Collections\ArrayCollection;

final class IdCollection extends ArrayCollection
{
    public function __construct(Id ...$ids)
    {
        parent::__construct($ids);
    }

    public function shouldBeAdded(Id $id): bool
    {
        return !$this->exists(static function ($key, $element) use ($id) {
            /* @var $element Id */
            return $id->equals($element);
        });
    }
}
