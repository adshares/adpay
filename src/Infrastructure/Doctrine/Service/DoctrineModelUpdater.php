<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Domain\ValueObject\Id;
use Doctrine\DBAL\Connection;
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
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function upsert(string $table, Id $id, array $data, array $types = []): void
    {
        if ($this->isModelExists($table, $id)) {
            $this->db->update($table, $data, ['id' => $id->toBin()], $types);
        } else {
            $this->db->insert($table, $data, $types);
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function isModelExists(string $table, Id $id): bool
    {
        $isset = $this->db->fetchColumn(
            sprintf('SELECT id FROM %s WHERE id = ?', $table),
            [$id->toBin()],
            0,
            [Type::BINARY]
        );

        return $isset !== false;
    }
}
