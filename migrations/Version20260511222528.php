<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511222528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Afegeix la clau forana manual entre les observacions i el catàleg d\'ocells (Cross-Aggregate DDD)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE observations ADD CONSTRAINT FK_OBSERVATIONS_SPECIES_ID FOREIGN KEY (species_id) REFERENCES birds (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE observations DROP CONSTRAINT FK_OBSERVATIONS_SPECIES_ID');
    }
}
