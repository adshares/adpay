<?php

declare(strict_types=1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationException;
use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use TypeError;

abstract class BasicDeleteDTO
{
    protected IdCollection $ids;

    public function __construct(string $fieldName, array $input)
    {
        if (!isset($input[$fieldName])) {
            throw new ValidationException(sprintf('Field `%s` is required.', $fieldName));
        }
        if (!is_array($input[$fieldName])) {
            throw new ValidationException(sprintf('Field `%s` must be an array.', $fieldName));
        }

        $collection = new IdCollection();
        try {
            foreach ($input[$fieldName] as $id) {
                $collection->add(new Id($id));
            }
        } catch (InvalidArgumentException | TypeError $exception) {
            throw new ValidationException($exception->getMessage());
        }
        $this->ids = $collection;
    }

    public function getIds(): IdCollection
    {
        return $this->ids;
    }
}
