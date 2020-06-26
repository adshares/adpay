<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationException;
use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use TypeError;

final class BidStrategyDeleteDTO
{
    private $ids;

    public function __construct(array $input)
    {
        if (!isset($input['bid_strategies'])) {
            throw new ValidationException('Field `bid_strategies` is required.');
        }
        if (!is_array($input['bid_strategies'])) {
            throw new ValidationException('Field `bid_strategies` must be an array.');
        }

        $collection = new IdCollection();
        try {
            foreach ($input['bid_strategies'] as $id) {
                $collection->add(new Id($id));
            }
        } catch (InvalidArgumentException|TypeError $exception) {
            throw new ValidationException($exception->getMessage());
        }
        $this->ids = $collection;
    }

    public function getIds(): IdCollection
    {
        return $this->ids;
    }
}
