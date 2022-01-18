<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220107172649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add automatic max cpm history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
CREATE TABLE campaign_costs
(
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    report_id        BIGINT(20)      NOT NULL,
    campaign_id      VARBINARY(16)   NOT NULL,
    score            FLOAT           DEFAULT NULL,
    max_cpm          BIGINT UNSIGNED NOT NULL,
    cpm_factor       FLOAT           NOT NULL,
    views            INT UNSIGNED    NOT NULL,
    views_cost       BIGINT UNSIGNED NOT NULL,
    clicks           INT UNSIGNED    NOT NULL,
    clicks_cost      BIGINT UNSIGNED NOT NULL,
    conversions      INT UNSIGNED    NOT NULL,
    conversions_cost BIGINT UNSIGNED NOT NULL,
    INDEX campaign_costs_updated_at (updated_at),
    CONSTRAINT campaign_costs_report_id_campaign_id_index
        UNIQUE (report_id, campaign_id)
);
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE campaign_costs');
    }
}
