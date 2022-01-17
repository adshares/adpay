<?php

declare(strict_types=1);

namespace Adshares\AdPay\Application\DTO;

final class BidStrategyDeleteDTO extends BasicDeleteDTO
{
    public function __construct(array $input)
    {
        parent::__construct('bid_strategies', $input);
    }
}
