<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class BidStrategyDeleteDTO extends BasicDeleteDTO
{
    public function __construct(array $input)
    {
        parent::__construct('bid_strategies', $input);
    }
}
