<?php

declare(strict_types=1);

namespace Adshares\AdPay\Application\DTO;

final class CampaignDeleteDTO extends BasicDeleteDTO
{
    public function __construct(array $input)
    {
        parent::__construct('campaigns', $input);
    }
}
