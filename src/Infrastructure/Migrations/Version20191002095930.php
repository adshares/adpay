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
        $this->addSql(
            'CREATE TABLE campaigns (
                id VARBINARY(16) NOT NULL,
                advertiser_id VARBINARY(16) NOT NULL,
                time_start TIMESTAMP NOT NULL,
                time_end TIMESTAMP NULL DEFAULT NULL,
                filters JSON NOT NULL,
                budget BIGINT(20) NOT NULL,
                max_cpm BIGINT(20) NULL DEFAULT NULL,
                max_cpc BIGINT(20) NULL DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX time_start (time_start),
                INDEX time_end (time_end)
            )'
        );

        $this->addSql(
            'CREATE TABLE banners (
                id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                size VARCHAR(32) NOT NULL,
                type VARCHAR(32) NOT NULL,
                PRIMARY KEY (id),
                INDEX campaign_id (campaign_id),
                CONSTRAINT banners_campaigns FOREIGN KEY (campaign_id) REFERENCES campaigns (id) ON DELETE CASCADE
            )'
        );

        $this->addSql(
            'CREATE TABLE conversions (
                id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                `limit` BIGINT(20) NULL DEFAULT NULL,
                limit_type VARCHAR(20) NOT NULL,
                cost BIGINT(20) NOT NULL,
                value BIGINT(20) NOT NULL,
                is_value_mutable TINYINT(4) NOT NULL,
                is_repeatable TINYINT(4) NOT NULL,
                PRIMARY KEY (id),
                INDEX campaign_id (campaign_id),
                CONSTRAINT conversions_campaigns FOREIGN KEY (campaign_id) REFERENCES campaigns (id) ON DELETE CASCADE
            )'
        );

        $this->addSql(
            'CREATE TABLE view_events (
                id VARBINARY(16) NOT NULL,
                time TIMESTAMP NOT NULL,
                case_id VARBINARY(16) NOT NULL,
                publisher_id VARBINARY(16) NOT NULL,
                zone_id VARBINARY(16) NULL DEFAULT NULL,
                advertiser_id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                banner_id VARBINARY(16) NOT NULL,
                impression_id VARBINARY(16) NOT NULL,
                tracking_id VARBINARY(16) NOT NULL,
                user_id VARBINARY(16) NOT NULL,
                human_score DECIMAL(3,2) NOT NULL,
                keywords JSON NOT NULL,
                context JSON NOT NULL,
                payment_status TINYINT(3) NULL DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX time (time)
            )'
        );

        $this->addSql(
            'CREATE TABLE click_events (
                id VARBINARY(16) NOT NULL,
                time TIMESTAMP NOT NULL,
                case_id VARBINARY(16) NOT NULL,
                publisher_id VARBINARY(16) NOT NULL,
                zone_id VARBINARY(16) NULL DEFAULT NULL,
                advertiser_id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                banner_id VARBINARY(16) NOT NULL,
                impression_id VARBINARY(16) NOT NULL,
                tracking_id VARBINARY(16) NOT NULL,
                user_id VARBINARY(16) NOT NULL,
                human_score DECIMAL(3,2) NOT NULL,
                keywords JSON NOT NULL,
                context JSON NOT NULL,
                payment_status TINYINT(3) NULL DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX time (time)
            )'
        );

        $this->addSql(
            'CREATE TABLE conversion_events (
                id VARBINARY(16) NOT NULL,
                time TIMESTAMP NOT NULL,
                case_id VARBINARY(16) NOT NULL,
                publisher_id VARBINARY(16) NOT NULL,
                zone_id VARBINARY(16) NULL DEFAULT NULL,
                advertiser_id VARBINARY(16) NOT NULL,
                campaign_id VARBINARY(16) NOT NULL,
                banner_id VARBINARY(16) NOT NULL,
                impression_id VARBINARY(16) NOT NULL,
                tracking_id VARBINARY(16) NOT NULL,
                user_id VARBINARY(16) NOT NULL,
                human_score DECIMAL(3,2) NOT NULL,
                keywords JSON NOT NULL,
                context JSON NOT NULL,
                conversion_id VARBINARY(16) NOT NULL,
                conversion_value BIGINT(20) NULL DEFAULT NULL,
                payment_status TINYINT(3) NULL DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX time (time)
            )'
        );

        $this->addSql(
            'CREATE TABLE payment_reports (
                id BIGINT(20) NOT NULL,
                status TINYINT(3) NOT NULL,
                intervals JSON NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
                PRIMARY KEY (id),
                INDEX status (status)
            )'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE conversions');
        $this->addSql('DROP TABLE banners');
        $this->addSql('DROP TABLE campaigns');
//        $this->addSql('DROP TABLE view_events');
//        $this->addSql('DROP TABLE click_events');
//        $this->addSql('DROP TABLE conversion_events');
    }
}
