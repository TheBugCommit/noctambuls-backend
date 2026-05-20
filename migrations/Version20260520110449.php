<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520110449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key from observations to birds catalog';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE observations ADD CONSTRAINT FK_OBS_SPECIES FOREIGN KEY (species_id) REFERENCES birds (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE observations DROP FOREIGN KEY FK_OBS_SPECIES');
    }
}
