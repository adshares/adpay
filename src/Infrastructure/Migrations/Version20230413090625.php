<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230413090625 extends AbstractMigration
{
    private const EVENT_TABLES = [
        'view_events',
        'click_events',
        'conversion_events',
    ];

    public function getDescription(): string
    {
        return 'Add `ads_txt` column to events';
    }

    public function up(Schema $schema): void
    {
        foreach (self::EVENT_TABLES as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ADD COLUMN ads_txt TINYINT(1) NULL DEFAULT NULL', $table));
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::EVENT_TABLES as $table) {
            $this->addSql(sprintf('ALTER TABLE %s DROP COLUMN ads_txt', $table));
        }
    }
}
