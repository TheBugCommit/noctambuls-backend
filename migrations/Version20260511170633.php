<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511170633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_participants ADD CONSTRAINT FK_BFF4CD13613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BFF4CD13613FECDF ON session_participants (session_id)');
        $this->addSql('
            ALTER TABLE session_participants
            ADD CONSTRAINT FK_SESSION_PARTICIPANT_PROFILE
            FOREIGN KEY (ornithologist_code)
            REFERENCES profiles (ornithologist_code)
            ON DELETE RESTRICT
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_participants DROP FOREIGN KEY FK_BFF4CD13613FECDF');
        $this->addSql('DROP INDEX IDX_BFF4CD13613FECDF ON session_participants');

        $this->addSql('
            ALTER TABLE session_participants
            DROP FOREIGN KEY FK_SESSION_PARTICIPANT_PROFILE
        ');
    }
}
