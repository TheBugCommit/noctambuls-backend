<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517103446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize is_deleted column type to match Doctrine boolean mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE censuses CHANGE is_deleted is_deleted TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE censuses CHANGE is_deleted is_deleted TINYINT DEFAULT 0 NOT NULL');
    }
}
