<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516000002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations
            ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER is_deleted,
            ADD INDEX idx_obs_deleted_at (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations DROP INDEX idx_obs_deleted_at, DROP COLUMN deleted_at');
    }
}
