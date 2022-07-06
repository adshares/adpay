<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\Exception\ValidationException;
use App\Domain\Exception\InvalidArgumentException;
use App\Domain\Model\BidStrategy;
use App\Domain\Model\BidStrategyCollection;
use App\Domain\ValueObject\Id;
use TypeError;

final class BidStrategyUpdateDTO
{
    private BidStrategyCollection $bidStrategies;

    public function __construct(array $input)
    {
        $this->validate($input);
        $this->fill($input);
    }

    public function getBidStrategies(): BidStrategyCollection
    {
        return $this->bidStrategies;
    }

    private function validate(array $input): void
    {
        if (!isset($input['bid_strategies'])) {
            throw new ValidationException('Field `bid_strategies` is required.');
        }

        if (!is_array($input['bid_strategies'])) {
            throw new ValidationException('Field `bid_strategies` must be an array.');
        }

        foreach ($input['bid_strategies'] as $bidStrategy) {
            $this->validateBidStrategy($bidStrategy);
        }
    }

    private function validateBidStrategy(array $input): void
    {
        if (!isset($input['id'])) {
            throw new ValidationException('Field `id` is required.');
        }

        if (!isset($input['details'])) {
            throw new ValidationException('Field `details` is required.');
        }

        if (!is_array($input['details'])) {
            throw new ValidationException('Field `details` must be an array.');
        }

        $this->validateDetails($input['details']);
    }

    private function validateDetails(array $details): void
    {
        foreach ($details as $input) {
            if (empty($input['category'])) {
                throw new ValidationException('Field `details[][category]` is required.');
            }

            if (!isset($input['rank'])) {
                throw new ValidationException('Field `details[][rank]` is required.');
            }

            if (!is_numeric($input['rank'])) {
                throw new ValidationException('Field `details[][rank]` must be a number.');
            }
        }
    }

    protected function fill(array $input): void
    {
        $collection = new BidStrategyCollection();
        foreach ($input['bid_strategies'] as $bidStrategy) {
            try {
                $id = new Id($bidStrategy['id']);
                foreach ($bidStrategy['details'] as $bidStrategyDetail) {
                    $model = new BidStrategy($id, $bidStrategyDetail['category'], (float)$bidStrategyDetail['rank']);

                    $collection->add($model);
                }
            } catch (InvalidArgumentException | TypeError $exception) {
                throw new ValidationException($exception->getMessage());
            }
        }

        $this->bidStrategies = $collection;
    }
}
