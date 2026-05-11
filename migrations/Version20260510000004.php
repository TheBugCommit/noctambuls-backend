<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create session_participants table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE session_participants (
            id INT NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(36) NOT NULL,
            ornithologist_code VARCHAR(15) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_session_participant (session_id, ornithologist_code),
            CONSTRAINT fk_session_participants_session FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE session_participants');
    }
}
