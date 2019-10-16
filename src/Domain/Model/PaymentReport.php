<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTimeInterface;

final class PaymentReport
{
    public const INTERVAL_START = 0;

    public const INTERVAL_END = 3599;

    /** @var int */
    private $id;

    /** @var PaymentReportStatus */
    private $status;

    /** @var array<int> */
    private $intervals;

    public function __construct(
        int $id,
        PaymentReportStatus $status,
        array $intervals = []
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->intervals = [
            EventType::VIEW => [],
            EventType::CLICK => [],
            EventType::CONVERSION => [],
        ];

        $this->addIntervals(EventType::createView(), $intervals[EventType::VIEW] ?? []);
        $this->addIntervals(EventType::createClick(), $intervals[EventType::CLICK] ?? []);
        $this->addIntervals(EventType::createConversion(), $intervals[EventType::CONVERSION] ?? []);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTimeStart(): DateTimeInterface
    {
        return DateTimeHelper::fromTimestamp($this->id + PaymentReport::INTERVAL_START);
    }

    public function getTimeEnd(): DateTimeInterface
    {
        return DateTimeHelper::fromTimestamp($this->id + PaymentReport::INTERVAL_END);
    }

    public function getStatus(): PaymentReportStatus
    {
        return $this->status;
    }

    public function isCalculated(): bool
    {
        return $this->status->isCalculated();
    }

    public function isComplete(): bool
    {
        return $this->status->isComplete();
    }

    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function getTypedIntervals(EventType $type): array
    {
        return $this->intervals[$type->toString()] ?? [];
    }

    public function addIntervals(EventType $type, array $intervals): void
    {
        foreach ($intervals as $interval) {
            if (!is_array($interval) || count($interval) !== 2) {
                throw new InvalidArgumentException('Interval must be a 2-element array.');
            }
            $this->addInterval($type, ...$interval);
        }
    }

    public function addInterval(EventType $type, int $start, int $end): void
    {
        if ($start < self::INTERVAL_START) {
            throw InvalidArgumentException::fromArgument(
                'start',
                (string)$start,
                sprintf('The value must be greater than or equal to %d.', self::INTERVAL_START)
            );
        }
        if ($end > self::INTERVAL_END) {
            throw InvalidArgumentException::fromArgument(
                'end',
                (string)$end,
                sprintf('The value must be less than %d.', self::INTERVAL_END)
            );
        }
        if ($end < $start) {
            throw new InvalidArgumentException('Interval end must be greater than or equal to start.');
        }

        $list = $this->intervals[$type->toString()];
        $list[] = [$start, $end];

        usort(
            $list,
            function ($a, $b) {
                return $a[0] <=> $b[0];
            }
        );

        $merged = [];
        $maxEnd = self::INTERVAL_START;
        foreach ($list as [$itemStart, $itemEnd]) {
            if (empty($merged) || $itemStart > $maxEnd + 1) {
                $merged[] = [$itemStart, $itemEnd];
                $maxEnd = $itemEnd;
                continue;
            }
            [$lastStart, $lastEnd] = array_pop($merged);
            $maxEnd = max($lastEnd, $itemEnd);
            $merged[] = [$lastStart, $maxEnd];
        }

        $this->intervals[$type->toString()] = $merged;

        $this->checkCompleteness();
    }

    private function checkCompleteness(): void
    {
        if ($this->status->isComplete()) {
            return;
        }

        $complete = [[self::INTERVAL_START, self::INTERVAL_END]];

        if ($complete === $this->intervals[EventType::VIEW]
            && $complete === $this->intervals[EventType::CLICK]
            && $complete === $this->intervals[EventType::CONVERSION]) {
            $this->status = PaymentReportStatus::createComplete();
        }
    }

    public static function timestampToId(int $timestamp): int
    {
        return (int)floor($timestamp / (self::INTERVAL_END + 1)) * (self::INTERVAL_END + 1);
    }
}
