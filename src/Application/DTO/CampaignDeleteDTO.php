<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use TypeError;

final class CampaignDeleteDTO
{
    private $ids;

    public function __construct(array $ids)
    {
        $idCollection = new IdCollection();

        try {
            foreach ($ids as $item) {
                $idCollection->add(new Id($item));
            }
        } catch (InvalidArgumentException|TypeError $exception) {
            throw new ValidationDtoException($exception->getMessage());
        }

        $this->ids = $idCollection;
    }

    public function getIds(): IdCollection
    {
        return $this->ids;
    }
}
