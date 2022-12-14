<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum Medium: string
{
    case Web = 'web';
    case Metaverse = 'metaverse';
}
