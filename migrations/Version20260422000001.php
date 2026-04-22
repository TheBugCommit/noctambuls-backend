<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role and password columns to auth_users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE auth_users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'ROLE_USER'");
        $this->addSql('ALTER TABLE auth_users ADD COLUMN password VARCHAR(255) NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_users DROP COLUMN role');
        $this->addSql('ALTER TABLE auth_users DROP COLUMN password');
    }
}
