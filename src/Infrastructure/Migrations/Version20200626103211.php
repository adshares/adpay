<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200626103211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add soft delete to bid strategies';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bid_strategy_details ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bid_strategy_details DROP COLUMN deleted_at');
    }
}
