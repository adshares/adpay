<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Id;

final class ConversionEvent extends Event
{
    /** @var Id */
    private $conversionId;

    /** @var ?int */
    private $value;

    public function __construct(Id $id, Id $conversionId, ?int $value)
    {
        parent::__construct($id, EventType::createConversion());
        $this->conversionId = $conversionId;
        $this->value = $value;
    }

    public function getConversionId(): Id
    {
        return $this->conversionId;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
