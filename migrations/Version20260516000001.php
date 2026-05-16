<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE system_cron_state (
            job_name          VARCHAR(50) NOT NULL,
            last_processed_at DATETIME    NOT NULL,
            PRIMARY KEY (job_name)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");

        $this->addSql("CREATE TABLE map_bubble_summary (
            species_id VARCHAR(36)   NOT NULL,
            lat        DECIMAL(10,6) NOT NULL,
            lon        DECIMAL(10,6) NOT NULL,
            year       SMALLINT      NOT NULL,
            count      INT           NOT NULL DEFAULT 0,
            updated_at DATETIME      NOT NULL,
            PRIMARY KEY (species_id, lat, lon, year),
            INDEX idx_mbs_updated_at (updated_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE map_bubble_summary');
        $this->addSql('DROP TABLE system_cron_state');
    }
}
