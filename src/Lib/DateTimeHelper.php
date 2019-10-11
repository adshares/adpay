<?php declare(strict_types = 1);

namespace Adshares\AdPay\Lib;

use Adshares\AdPay\Lib\Exception\DateTimeException;
use DateTimeImmutable;
use Exception;
use Throwable;

final class DateTimeHelper
{
    public static function createFromTimestamp(int $timestamp): DateTimeImmutable
    {
        try {
            if ($timestamp === 0) {
                throw new Exception('Timestamp equals 0');
            }
            return new DateTimeImmutable('@'.$timestamp);
        } catch (Throwable $exception) {
            throw new DateTimeException($exception->getMessage());
        }
    }

    public static function createFromString(string $date): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($date);
        } catch (Throwable $exception) {
            throw new DateTimeException($exception->getMessage());
        }
    }
}