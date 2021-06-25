<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191202110335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove conversions limit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversions DROP COLUMN `limit`');
        $this->addSql('ALTER TABLE conversions DROP COLUMN cost');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversions ADD COLUMN `limit` BIGINT(20) NULL DEFAULT NULL AFTER campaign_id');
        $this->addSql('ALTER TABLE conversions ADD COLUMN cost BIGINT(20) NOT NULL AFTER limit_type');
    }
}
