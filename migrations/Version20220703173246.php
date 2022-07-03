<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220703173246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE auction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bid_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE auction ADD reserve_quantity VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE auction ALTER end_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE auction ALTER end_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN auction.end_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bid ADD end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN bid.end_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE asset_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE auction_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bid_id_seq CASCADE');
        $this->addSql('ALTER TABLE bid DROP end_at');
        $this->addSql('ALTER TABLE auction DROP reserve_quantity');
        $this->addSql('ALTER TABLE auction ALTER end_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE auction ALTER end_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN auction.end_at IS NULL');
    }
}
