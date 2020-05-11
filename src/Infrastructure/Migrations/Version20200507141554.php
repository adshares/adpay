<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200507141554 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add bid strategies';
    }

    public function up(Schema $schema) : void
    {
        $uuid = random_bytes(16);

//        $this->addSql(<<<SQL
//CREATE TABLE bid_strategy
//(
//    id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//    name VARCHAR(255) NOT NULL
//);
//SQL
//        );
//
//        $this->addSql(<<<SQL
//CREATE TABLE bid_strategy_details
//(
//    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//    bid_strategy_id BIGINT UNSIGNED NOT NULL,
//    category        varchar(267)    NOT NULL,
//    `rank`          decimal(3, 2)   NOT NULL,
//    CONSTRAINT bid_strategy_details_bid_strategy_id_foreign
//        FOREIGN KEY (bid_strategy_id) REFERENCES bid_strategy (id)
//            ON DELETE CASCADE
//);
//SQL
//        );

        $this->addSql(<<<SQL
CREATE TABLE bid_strategies
(
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bid_strategy_id VARBINARY(16) NOT NULL,
    category        varchar(267)    NOT NULL,
    `rank`          decimal(3, 2)   NOT NULL,
    INDEX bid_strategy_id_index (bid_strategy_id)
);
SQL
        );

        $this->addSql('ALTER TABLE campaigns ADD COLUMN bid_strategy_id VARBINARY(16) NOT NULL AFTER max_cpc');
        $this->addSql('UPDATE campaigns SET bid_strategy_id = ?', [$uuid]);
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE campaigns DROP COLUMN bid_strategy_id');
        $this->addSql('DROP TABLE bid_strategy');
    }
}
