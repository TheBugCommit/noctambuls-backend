<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517103257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add soft delete fields to censuses table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE censuses ADD is_deleted TINYINT(1) NOT NULL DEFAULT 0, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX idx_obs_deleted_at ON observations');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE censuses DROP is_deleted, DROP deleted_at');
        $this->addSql('CREATE INDEX idx_obs_deleted_at ON observations (deleted_at)');
    }
}
