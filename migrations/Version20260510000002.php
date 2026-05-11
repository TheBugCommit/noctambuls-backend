<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sessions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE sessions (
            id VARCHAR(36) NOT NULL,
            census_id VARCHAR(36) NULL,
            annotator_id VARCHAR(36) NOT NULL,
            session_type TINYINT NOT NULL,
            start_time DATETIME NULL,
            end_time DATETIME NULL,
            temperature TINYINT NOT NULL,
            wind TINYINT NOT NULL,
            sky TINYINT NOT NULL,
            rain TINYINT NOT NULL,
            visibility TINYINT NOT NULL,
            moon TINYINT NOT NULL,
            listening_point INT NULL,
            latitude DOUBLE PRECISION NULL,
            longitude DOUBLE PRECISION NULL,
            accuracy DOUBLE PRECISION NULL,
            altitude DOUBLE PRECISION NULL,
            altitude_accuracy DOUBLE PRECISION NULL,
            location_source TINYINT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sessions');
    }
}
