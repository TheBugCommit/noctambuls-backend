<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527202440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted_at to sessions for 30-day expiry purge';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sessions ADD deleted_at DATETIME DEFAULT NULL AFTER is_deleted');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sessions DROP COLUMN deleted_at');
    }
}
