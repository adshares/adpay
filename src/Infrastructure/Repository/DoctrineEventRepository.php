<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\InvalidDataException;
use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Infrastructure\Mapper\ClickEventMapper;
use Adshares\AdPay\Infrastructure\Mapper\ConversionEventMapper;
use Adshares\AdPay\Infrastructure\Mapper\EventMapper;
use Adshares\AdPay\Infrastructure\Mapper\ViewEventMapper;
use DateTimeInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\Type;

final class DoctrineEventRepository extends DoctrineModelUpdater implements EventRepository
{
    public function saveAll(
        EventCollection $events
    ): int {
        return $this->upsertEvents($events, self::getMapper($events->getType()));
    }

    public function deleteByTime(
        EventType $type,
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd
    ): void {
        /*  @var $mapper EventMapper */
        $mapper = self::getMapper($type);
        try {
            $this->clearInterval($mapper::table(), $timeStart, $timeEnd);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }

    private static function getMapper(EventType $type): string
    {
        if ($type->isClick()) {
            $mapper = ClickEventMapper::class;
        } elseif ($type->isConversion()) {
            $mapper = ConversionEventMapper::class;
        } else {
            $mapper = ViewEventMapper::class;
        }

        return $mapper;
    }

    private function upsertEvents(
        EventCollection $events,
        string $mapper
    ): int {
        /*  @var $mapper EventMapper */
        $count = 0;
        try {
            $this->clearInterval($mapper::table(), $events->getTimeStart(), $events->getTimeEnd());
            foreach ($events as $event) {
                /*  @var $event Event */
                try {
                    $this->db->insert(
                        $mapper::table(),
                        $mapper::map($event),
                        $mapper::types()
                    );
                } catch (UniqueConstraintViolationException $exception) {
                    throw new InvalidDataException(sprintf('Duplicate event id [%s]', $event->getId()));
                }
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $count;
    }

    public function fetchByTime(
        EventType $type,
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd
    ): iterable {
        /*  @var $mapper EventMapper */
        $mapper = self::getMapper($type);

        if ($timeStart === null && $timeEnd === null) {
            throw new DomainRepositoryException('Time start or time end is required');
        }

        $query = sprintf('SELECT * FROM %s WHERE 1=1', $mapper::table());
        $params = [];
        $types = [];

        if ($timeStart !== null) {
            $query .= ' AND time >= ?';
            $params[] = $timeStart;
            $types[] = Type::DATETIME;
        }
        if ($timeEnd !== null) {
            $query .= ' AND time <= ?';
            $params[] = $timeEnd;
            $types[] = Type::DATETIME;
        }

        try {
            $result = $this->db->executeQuery($query, $params, $types);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        while ($row = $result->fetch()) {
            yield $mapper::fillRaw($row);
        }

        return null;
    }
}
