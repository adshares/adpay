<?php

declare(strict_types=1);

namespace App\Lib;

use App\Lib\Exception\DateTimeException;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class DateTimeHelper
{
    public static function fromTimestamp(int $timestamp): DateTimeImmutable
    {
        try {
            if ($timestamp === 0) {
                throw new RuntimeException('Timestamp equals 0');
            }

            return new DateTimeImmutable('@' . $timestamp);
        } catch (Throwable $exception) {
            throw new DateTimeException(
                str_replace('DateTimeImmutable::__construct(): ', '', $exception->getMessage())
            );
        }
    }

    public static function fromString(string $date): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($date);
        } catch (Throwable $exception) {
            throw new DateTimeException(
                str_replace('DateTimeImmutable::__construct(): ', '', $exception->getMessage())
            );
        }
    }
}
