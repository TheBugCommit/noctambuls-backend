<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Afegeix updated_at a auth_users, inicialitzat amb created_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_users ADD COLUMN updated_at DATETIME NULL');
        $this->addSql('UPDATE auth_users SET updated_at = created_at');
        $this->addSql('ALTER TABLE auth_users MODIFY COLUMN updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_users DROP COLUMN updated_at');
    }
}
