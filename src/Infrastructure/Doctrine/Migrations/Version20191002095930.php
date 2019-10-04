<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191002095930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial structure';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE campaigns (
                id VARBINARY(16) NOT NULL,
                advertiser_id VARBINARY(16) NOT NULL,
                time_start TIMESTAMP NOT NULL,
                time_end TIMESTAMP NOT NULL,
                filters JSON NOT NULL,
                budget INT(11) NOT NULL,
                max_cpm INT(11) NULL DEFAULT NULL,
                max_cpc INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX time_start (time_start),
                INDEX time_end (time_end)
            )
        ');

        $this->addSql('
            CREATE TABLE banners (
                id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                size VARCHAR(32) NOT NULL,
                type VARCHAR(32) NOT NULL,
                PRIMARY KEY (id),
                INDEX campaign_id (campaign_id),
                CONSTRAINT banners_campaigns FOREIGN KEY (campaign_id) REFERENCES campaigns (id) ON DELETE CASCADE
            )
        ');

        $this->addSql('
            CREATE TABLE conversions (
                id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                `limit` BIGINT(20) NULL DEFAULT NULL,
                limit_type VARCHAR(20) NOT NULL,
                cost BIGINT(20) NOT NULL,
                value BIGINT(20) NULL DEFAULT NULL,
                is_value_mutable TINYINT(4) NOT NULL,
                is_repeatable TINYINT(4) NOT NULL,
                PRIMARY KEY (id),
                INDEX campaign_id (campaign_id),
                CONSTRAINT conversions_campaigns FOREIGN KEY (campaign_id) REFERENCES campaigns (id) ON DELETE CASCADE
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE conversions');
        $this->addSql('DROP TABLE banners');
        $this->addSql('DROP TABLE campaigns');
    }
}
