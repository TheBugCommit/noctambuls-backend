<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520110108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key for ornithologist_code in session_participants';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_participants ADD CONSTRAINT FK_SESS_PART_PROFILE FOREIGN KEY (ornithologist_code) REFERENCES profiles (ornithologist_code) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_participants DROP FOREIGN KEY FK_SESS_PART_PROFILE');
    }
}
