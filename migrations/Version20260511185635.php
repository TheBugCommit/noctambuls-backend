<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511185635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_participants DROP FOREIGN KEY `FK_SESSION_PARTICIPANT_PROFILE`');
        $this->addSql('DROP INDEX FK_SESSION_PARTICIPANT_PROFILE ON session_participants');
        $this->addSql('CREATE UNIQUE INDEX uq_census_annotator ON sessions (census_id, annotator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uq_census_annotator ON sessions');
        $this->addSql('ALTER TABLE session_participants ADD CONSTRAINT `FK_SESSION_PARTICIPANT_PROFILE` FOREIGN KEY (ornithologist_code) REFERENCES profiles (ornithologist_code)');
        $this->addSql('CREATE INDEX FK_SESSION_PARTICIPANT_PROFILE ON session_participants (ornithologist_code)');
    }
}
