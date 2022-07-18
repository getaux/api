<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718131849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ADD auction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD bid_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F57B8F0DE FOREIGN KEY (auction_id) REFERENCES auction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F4D9866B8 FOREIGN KEY (bid_id) REFERENCES bid (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F57B8F0DE ON message (auction_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F4D9866B8 ON message (bid_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F57B8F0DE');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F4D9866B8');
        $this->addSql('DROP INDEX UNIQ_B6BD307F57B8F0DE');
        $this->addSql('DROP INDEX UNIQ_B6BD307F4D9866B8');
        $this->addSql('ALTER TABLE message DROP auction_id');
        $this->addSql('ALTER TABLE message DROP bid_id');
    }
}
