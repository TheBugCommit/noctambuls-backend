<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426120200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove DC2Type comment from bird_catalog_version.last_updated_at (not needed in MariaDB)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bird_catalog_version CHANGE last_updated_at last_updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bird_catalog_version CHANGE last_updated_at last_updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
