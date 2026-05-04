<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_blocked and anonymized_at columns to auth_users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_users ADD COLUMN is_blocked TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN anonymized_at DATETIME NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_users DROP COLUMN is_blocked, DROP COLUMN anonymized_at');
    }
}
