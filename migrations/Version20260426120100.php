<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align bird_catalog schema with Doctrine expectations (LONGTEXT, FK names, datetime comment)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bird_catalog_version CHANGE last_updated_at last_updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bird_translations DROP FOREIGN KEY fk_bird_translation_bird');
        $this->addSql('ALTER TABLE bird_translations CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE bird_translations ADD CONSTRAINT FK_223F208EE813F9 FOREIGN KEY (bird_id) REFERENCES birds (id)');
        $this->addSql('ALTER TABLE bird_translations RENAME INDEX idx_bird_translation_bird TO IDX_223F208EE813F9');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bird_catalog_version CHANGE last_updated_at last_updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bird_translations DROP FOREIGN KEY FK_223F208EE813F9');
        $this->addSql('ALTER TABLE bird_translations CHANGE description description TEXT NOT NULL');
        $this->addSql('ALTER TABLE bird_translations ADD CONSTRAINT fk_bird_translation_bird FOREIGN KEY (bird_id) REFERENCES birds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bird_translations RENAME INDEX IDX_223F208EE813F9 TO idx_bird_translation_bird');
    }
}
