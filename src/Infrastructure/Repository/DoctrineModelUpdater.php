<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\ValueObject\Id;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\ParameterType;
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

    /**
     * @param string $table
     * @param array $ids
     * @param string $field
     *
     * @return int
     * @throws DBALException
     */
    protected function softDelete(string $table, array $ids, string $field = 'id'): int
    {
        return $this->db->executeUpdate(
            sprintf('UPDATE %s SET deleted_at = NOW() WHERE %s IN (?)', $table, $field),
            [$ids],
            [Connection::PARAM_STR_ARRAY]
        );
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $types
     *
     * @return int
     * @throws DBALException
     */
    protected function insertBatch(string $table, array $data, array $types = []): int
    {
        if (empty($data)) {
            return 0;
        }

        $columns = array_keys(reset($data));
        $set = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';

        return $this->db->executeUpdate(
            'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ')' .
            ' VALUES ' . implode(',', array_fill(0, count($data), $set)),
            array_merge(...array_map('array_values', $data)),
            array_merge(...array_fill(0, count($data), $this->extractTypeValues($columns, $types)))
        );
    }

    private function extractTypeValues(array $columnList, array $types): array
    {
        $typeValues = [];

        foreach ($columnList as $columnIndex => $columnName) {
            $typeValues[] = $types[$columnName] ?? ParameterType::STRING;
        }

        return $typeValues;
    }
}
