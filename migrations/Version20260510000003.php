<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create observations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE observations (
            id VARCHAR(36) NOT NULL,
            session_id VARCHAR(36) NOT NULL,
            species_id VARCHAR(36) NOT NULL,
            contact_type TINYINT NOT NULL,
            quantity INT NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NOT NULL,
            notes LONGTEXT NULL,
            latitude DOUBLE PRECISION NULL,
            longitude DOUBLE PRECISION NULL,
            accuracy DOUBLE PRECISION NULL,
            altitude DOUBLE PRECISION NULL,
            altitude_accuracy DOUBLE PRECISION NULL,
            location_source TINYINT NULL,
            bearing DOUBLE PRECISION NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            CONSTRAINT fk_observations_session FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE INDEX idx_observations_session_id ON observations (session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE observations');
    }
}
