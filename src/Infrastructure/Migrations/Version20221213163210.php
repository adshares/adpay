<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221213163210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add medium and vendor to campaign';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaigns ADD COLUMN medium VARCHAR(16) NOT NULL DEFAULT "web"');
        $this->addSql('ALTER TABLE campaigns ADD COLUMN vendor VARCHAR(32) NULL DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaigns DROP COLUMN medium');
        $this->addSql('ALTER TABLE campaigns DROP COLUMN vendor');
    }
}
