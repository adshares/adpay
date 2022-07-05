<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Exception\DomainRepositoryException;
use App\Domain\Exception\InvalidDataException;
use App\Domain\Model\Event;
use App\Domain\Model\EventCollection;
use App\Domain\Repository\EventRepository;
use App\Domain\ValueObject\EventType;
use App\Infrastructure\Mapper\ClickEventMapper;
use App\Infrastructure\Mapper\ConversionEventMapper;
use App\Infrastructure\Mapper\EventMapper;
use App\Infrastructure\Mapper\ViewEventMapper;
use DateTimeInterface;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\Types;

final class DoctrineEventRepository extends DoctrineModelUpdater implements EventRepository
{
    public function saveAll(
        EventCollection $events
    ): int {
        return $this->upsertEvents($events, self::getMapper($events->getType()));
    }

    public function deleteByTime(
        EventType $type,
        ?DateTimeInterface $timeStart = null,
        ?DateTimeInterface $timeEnd = null
    ): int {
        /*  @var $mapper EventMapper */
        $mapper = self::getMapper($type);
        try {
            return $this->clearInterval($mapper::table(), $timeStart, $timeEnd);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }

    public function fetchByTime(
        ?DateTimeInterface $timeStart = null,
        ?DateTimeInterface $timeEnd = null
    ): iterable {
        $params = [];
        $types = [];
        $query = 'SELECT * FROM %s WHERE 1=1';
        $query .= self::timeCondition($timeStart, $timeEnd, $params, $types);

        try {
            $result = $this->db->executeQuery(sprintf($query, ViewEventMapper::table()), $params, $types);
            while ($row = $result->fetchAssociative()) {
                yield ViewEventMapper::fillRaw($row);
            }
            $result = $this->db->executeQuery(sprintf($query, ClickEventMapper::table()), $params, $types);
            while ($row = $result->fetchAssociative()) {
                yield ClickEventMapper::fillRaw($row);
            }
            $result = $this->db->executeQuery(sprintf($query, ConversionEventMapper::table()), $params, $types);
            while ($row = $result->fetchAssociative()) {
                yield ConversionEventMapper::fillRaw($row);
            }
        } catch (DBALException | DBALDriverException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return null;
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

    /**
     * @param string $table
     * @param DateTimeInterface|null $timeStart
     * @param DateTimeInterface|null $timeEnd
     *
     * @return int
     * @throws DBALException
     */
    private function clearInterval(
        string $table,
        ?DateTimeInterface $timeStart = null,
        ?DateTimeInterface $timeEnd = null
    ): int {
        $params = [];
        $types = [];
        $query = sprintf('DELETE FROM %s WHERE 1=1', $table);
        $query .= self::timeCondition($timeStart, $timeEnd, $params, $types);

        return $this->db->executeStatement($query, $params, $types);
    }

    private static function timeCondition(
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd,
        array &$params,
        array &$types
    ): string {
        $query = '';

        if ($timeStart !== null) {
            $query .= ' AND time >= ?';
            $params[] = $timeStart;
            $types[] = Types::DATETIME_MUTABLE;
        }
        if ($timeEnd !== null) {
            $query .= ' AND time <= ?';
            $params[] = $timeEnd;
            $types[] = Types::DATETIME_MUTABLE;
        }

        return $query;
    }
}
