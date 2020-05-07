<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203094056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add case time';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE view_events ADD COLUMN case_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER case_id'
        );
        $this->addSql(
            'ALTER TABLE click_events ADD COLUMN case_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER case_id'
        );
        $this->addSql(
            'ALTER TABLE conversion_events ADD COLUMN case_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER case_id'
        );

        $this->addSql('UPDATE view_events SET case_time = time');
        $this->addSql('UPDATE click_events SET case_time = time');
        $this->addSql('UPDATE conversion_events SET case_time = time');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE view_events DROP COLUMN case_time');
        $this->addSql('ALTER TABLE click_events DROP COLUMN case_time');
        $this->addSql('ALTER TABLE conversion_events DROP COLUMN case_time');
    }
}
