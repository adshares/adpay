<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\UpdateDataException;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Psr\Log\LoggerInterface;

abstract class DoctrineModelUpdater
{
    /*  @var Connection */
    protected $db;

    /* @var LoggerInterface */
    protected $logger;

    public function __construct(Connection $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * @param string $table
     * @param DateTimeInterface|null $timeStart
     * @param DateTimeInterface|null $timeEnd
     *
     * @return int
     * @throws DBALException
     */
    protected function clearInterval(
        string $table,
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd
    ): int {
        if ($timeStart === null && $timeEnd === null) {
            throw new UpdateDataException('Time start or time end is required');
        }

        $query = sprintf('DELETE FROM %s WHERE 1=1', $table);
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

        return $this->db->executeUpdate($query, $params, $types);
    }

    /**
     * @param string $table
     * @param int|Id $id
     * @param array $data
     * @param array $types
     *
     * @throws DBALException
     */
    protected function upsert(string $table, $id, array $data, array $types = []): void
    {
        $value = $id instanceof Id ? $id->toBin() : $id;

        if ($this->isModelExists($table, $id)) {
            $this->db->update($table, $data, ['id' => $value], $types);
        } else {
            $this->db->insert($table, $data, $types);
        }
    }

    /**
     * @param string $table
     * @param Id $id
     *
     * @return bool
     * @throws DBALException
     */
    protected function isModelExists(string $table, $id): bool
    {
        $value = $id instanceof Id ? $id->toBin() : $id;
        $type = $id instanceof Id ? Type::BINARY : Type::INTEGER;

        $isset = $this->db->fetchColumn(
            sprintf('SELECT id FROM %s WHERE id = ?', $table),
            [$value],
            0,
            [$type]
        );

        return $isset !== false;
    }
}
