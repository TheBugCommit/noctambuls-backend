<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop legacy auth_refresh_tokens table (replaced by gesdinet refresh_tokens)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS auth_refresh_tokens');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE auth_refresh_tokens (
            id VARCHAR(36) NOT NULL,
            token VARCHAR(128) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            expires_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_7B1E86055F37A13B (token),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');
    }
}
