<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422113200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_users CHANGE role role VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE refresh_tokens CHANGE refresh_token refresh_token VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_users CHANGE role role VARCHAR(50) DEFAULT \'ROLE_USER\' NOT NULL');
        $this->addSql('ALTER TABLE refresh_tokens CHANGE refresh_token refresh_token VARCHAR(128) NOT NULL');
    }
}
