<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520100323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key validation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_SESSIONS_CENSUS FOREIGN KEY (census_id) REFERENCES censuses (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_SESSIONS_ANNOTATOR FOREIGN KEY (annotator_id) REFERENCES auth_users (id)');
        $this->addSql('ALTER TABLE profiles ADD CONSTRAINT FK_PROFILES_AUTH_USER FOREIGN KEY (id) REFERENCES auth_users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE map_bubble_summary ADD CONSTRAINT FK_MAP_BUBBLE_SPECIES FOREIGN KEY (species_id) REFERENCES birds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE monthly_observation_summary ADD CONSTRAINT FK_MONTHLY_OBS_SPECIES FOREIGN KEY (species_id) REFERENCES birds (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sessions DROP FOREIGN KEY FK_SESSIONS_CENSUS');
        $this->addSql('ALTER TABLE sessions DROP FOREIGN KEY FK_SESSIONS_ANNOTATOR');
        $this->addSql('ALTER TABLE profiles DROP FOREIGN KEY FK_PROFILES_AUTH_USER');
        $this->addSql('ALTER TABLE monthly_observation_summary DROP FOREIGN KEY FK_MONTHLY_OBS_SPECIES');
        $this->addSql('ALTER TABLE map_bubble_summary DROP FOREIGN KEY FK_MAP_BUBBLE_SPECIES');
    }
}
