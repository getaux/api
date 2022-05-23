<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220523085347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bid_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE asset (id INT NOT NULL, internal_id VARCHAR(255) NOT NULL, token_address VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN asset.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN asset.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE auction (id INT NOT NULL, asset_id INT NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, transfer_id BIGINT NOT NULL, quantity BIGINT NOT NULL, decimals INT NOT NULL, token_type VARCHAR(255) NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DEE4F5935DA1941 ON auction (asset_id)');
        $this->addSql('COMMENT ON COLUMN auction.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN auction.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE bid (id INT NOT NULL, auction_id INT NOT NULL, status VARCHAR(255) NOT NULL, transfer_id BIGINT NOT NULL, quantity BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4AF2B3F357B8F0DE ON bid (auction_id)');
        $this->addSql('COMMENT ON COLUMN bid.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bid.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE auction ADD CONSTRAINT FK_DEE4F5935DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bid ADD CONSTRAINT FK_4AF2B3F357B8F0DE FOREIGN KEY (auction_id) REFERENCES auction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE auction DROP CONSTRAINT FK_DEE4F5935DA1941');
        $this->addSql('ALTER TABLE bid DROP CONSTRAINT FK_4AF2B3F357B8F0DE');
        $this->addSql('DROP SEQUENCE bid_id_seq CASCADE');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE auction');
        $this->addSql('DROP TABLE bid');
    }
}
