<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create bird_catalog tables: birds, bird_translations, bird_catalog_version';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE birds (
            id VARCHAR(36) NOT NULL,
            scientific_name VARCHAR(255) NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            audio_path VARCHAR(500) NOT NULL,
            information_url VARCHAR(500) NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        $this->addSql('CREATE TABLE bird_translations (
            id VARCHAR(36) NOT NULL,
            bird_id VARCHAR(36) NOT NULL,
            language_code VARCHAR(10) NOT NULL,
            common_name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            PRIMARY KEY (id, bird_id),
            UNIQUE INDEX uq_bird_translation_language (bird_id, language_code),
            INDEX idx_bird_translation_bird (bird_id),
            CONSTRAINT fk_bird_translation_bird FOREIGN KEY (bird_id) REFERENCES birds (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        $this->addSql('CREATE TABLE bird_catalog_version (
            id INT NOT NULL,
            version INT NOT NULL,
            last_updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE bird_translations');
        $this->addSql('DROP TABLE birds');
        $this->addSql('DROP TABLE bird_catalog_version');
    }
}
