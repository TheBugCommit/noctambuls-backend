<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510105323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix enum columns from TINYINT to INT in sessions and observations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sessions CHANGE session_type session_type INT NOT NULL, CHANGE temperature temperature INT NOT NULL, CHANGE wind wind INT NOT NULL, CHANGE sky sky INT NOT NULL, CHANGE rain rain INT NOT NULL, CHANGE visibility visibility INT NOT NULL, CHANGE moon moon INT NOT NULL, CHANGE location_source location_source INT DEFAULT NULL');
        $this->addSql('ALTER TABLE observations CHANGE contact_type contact_type INT NOT NULL, CHANGE location_source location_source INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sessions CHANGE session_type session_type TINYINT NOT NULL, CHANGE temperature temperature TINYINT NOT NULL, CHANGE wind wind TINYINT NOT NULL, CHANGE sky sky TINYINT NOT NULL, CHANGE rain rain TINYINT NOT NULL, CHANGE visibility visibility TINYINT NOT NULL, CHANGE moon moon TINYINT NOT NULL, CHANGE location_source location_source TINYINT DEFAULT NULL');
        $this->addSql('ALTER TABLE observations CHANGE contact_type contact_type TINYINT NOT NULL, CHANGE location_source location_source TINYINT DEFAULT NULL');
    }
}
