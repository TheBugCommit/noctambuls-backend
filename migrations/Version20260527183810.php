<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527183810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add restored_at to observations for analytics double-decrement fix';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations ADD restored_at DATETIME DEFAULT NULL AFTER deleted_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations DROP COLUMN restored_at');
    }
}
