<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Size;

final class Banner
{
    /** @var Id */
    private $campaignId;
    /** @var Id */
    private $bannerId;
    /** @var Size */
    private $size;
    /** @var string */
    private $type;

    public function __construct(Id $campaignId, Id $bannerId, Size $size, string $type)
    {
        $this->campaignId = $campaignId;
        $this->bannerId = $bannerId;
        $this->size = $size;
        $this->type = $type;
    }

    public function getBannerId(): string
    {
        return $this->bannerId->toString();
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
