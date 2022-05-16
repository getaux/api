<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220422110416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asset (id INT NOT NULL, internal_id VARCHAR(255) NOT NULL, token_address VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN asset.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE auction (id INT NOT NULL, asset_id INT NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, transfer_id BIGINT NOT NULL, quantity BIGINT NOT NULL, decimals INT NOT NULL, token_type VARCHAR(255) NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DEE4F5935DA1941 ON auction (asset_id)');
        $this->addSql('COMMENT ON COLUMN auction.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE auction ADD CONSTRAINT FK_DEE4F5935DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE auction DROP CONSTRAINT FK_DEE4F5935DA1941');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE auction');
    }
}