<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\DTO;

use Adshares\AdPay\Application\DTO\EventUpdateDTO;
use Adshares\AdPay\Application\DTO\ViewEventUpdateDTO;

final class ViewEventUpdateDTOTest extends EventUpdateDTOTest
{
    protected function createDTO(array $data): EventUpdateDTO
    {
        return new ViewEventUpdateDTO($data);
    }
}
