<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajusta is_deleted per fer coincidir el tipus exacte que espera Doctrine (elimina DEFAULT)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations CHANGE is_deleted is_deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE sessions CHANGE is_deleted is_deleted TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations CHANGE is_deleted is_deleted TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE sessions CHANGE is_deleted is_deleted TINYINT(1) NOT NULL DEFAULT 0');
    }
}
