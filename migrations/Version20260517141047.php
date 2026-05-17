<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517141047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_templates (title VARCHAR(255) NOT NULL, body VARCHAR(1000) NOT NULL, event VARCHAR(50) NOT NULL, PRIMARY KEY (event)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO notification_templates (event, title, body) VALUES ('census.published', 'Nou cens disponible', 'S\'ha publicat un nou cens. Consulta l\'app per als detalls.')");
        $this->addSql("INSERT INTO notification_templates (event, title, body) VALUES ('census.date_changed', 'Cens modificat', 'S\'ha modificat la data d\'un cens. Revisa els detalls actualitzats.')");
        $this->addSql("INSERT INTO notification_templates (event, title, body) VALUES ('census.cancelled', 'Cens cancel·lat', 'Un cens ha estat cancel·lat.')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE notification_templates');
    }
}
