<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718154748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_b6bd307f57b8f0de');
        $this->addSql('DROP INDEX uniq_b6bd307f4d9866b8');
        $this->addSql('CREATE INDEX IDX_B6BD307F57B8F0DE ON message (auction_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F4D9866B8 ON message (bid_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_B6BD307F57B8F0DE');
        $this->addSql('DROP INDEX IDX_B6BD307F4D9866B8');
        $this->addSql('CREATE UNIQUE INDEX uniq_b6bd307f57b8f0de ON message (auction_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_b6bd307f4d9866b8 ON message (bid_id)');
    }
}
