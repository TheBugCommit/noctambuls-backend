<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Afegeix is_deleted a observations i sessions per a soft-delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations ADD is_deleted TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE sessions ADD is_deleted TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations DROP COLUMN is_deleted');
        $this->addSql('ALTER TABLE sessions DROP COLUMN is_deleted');
    }
}
