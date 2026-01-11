<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260111113226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE submission (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, submitted_value VARCHAR(255) NOT NULL, success BOOLEAN NOT NULL, submitted_at DATETIME NOT NULL, team_id INTEGER NOT NULL, flag_id INTEGER NOT NULL, CONSTRAINT FK_DB055AF3296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DB055AF3919FE4E5 FOREIGN KEY (flag_id) REFERENCES flag (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_DB055AF3296CD8AE ON submission (team_id)');
        $this->addSql('CREATE INDEX IDX_DB055AF3919FE4E5 ON submission (flag_id)');
        $this->addSql('CREATE INDEX idx_submission_team_flag_success ON submission (team_id, flag_id, success)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE submission');
    }
}
