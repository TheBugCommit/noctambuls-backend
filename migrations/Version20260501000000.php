<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create profiles table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE profiles (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE profiles');
    }
}
