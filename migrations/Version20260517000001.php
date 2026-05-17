<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE monthly_observation_summary (
            species_id VARCHAR(36) NOT NULL,
            year       SMALLINT    NOT NULL,
            month      TINYINT     NOT NULL,
            count      INT         NOT NULL DEFAULT 0,
            updated_at DATETIME    NOT NULL,
            PRIMARY KEY (species_id, year, month),
            INDEX idx_mos_year (year)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE monthly_observation_summary');
    }
}
