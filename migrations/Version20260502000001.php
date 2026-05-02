<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260502000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ornithologist_code to profiles table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profiles ADD ornithologist_code VARCHAR(15) DEFAULT NULL');

        $this->addSql(
            "UPDATE profiles p
             JOIN (
                 SELECT id,
                        CONCAT('#OR-X-', LPAD(ROW_NUMBER() OVER (ORDER BY created_at ASC), 4, '0')) AS code
                 FROM profiles
             ) ranked ON p.id = ranked.id
             SET p.ornithologist_code = ranked.code"
        );

        $this->addSql(
            'ALTER TABLE profiles
             MODIFY ornithologist_code VARCHAR(15) NOT NULL,
             ADD UNIQUE INDEX uniq_ornithologist_code (ornithologist_code)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profiles DROP INDEX uniq_ornithologist_code, DROP COLUMN ornithologist_code');
    }
}
