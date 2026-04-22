<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422163757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_tokens ADD user_id BINARY(16) NOT NULL, DROP username');
        $this->addSql('CREATE INDEX idx_refresh_tokens_user_id ON refresh_tokens (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_refresh_tokens_user_id ON refresh_tokens');
        $this->addSql('ALTER TABLE refresh_tokens ADD username VARCHAR(255) NOT NULL, DROP user_id');
    }
}
