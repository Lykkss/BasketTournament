<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206085637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD tournoi_id INT NOT NULL, CHANGE equipe_a_id equipe_a_id INT NOT NULL, CHANGE equipe_b_id equipe_b_id INT NOT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CF607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id)');
        $this->addSql('CREATE INDEX IDX_232B318CF607770A ON game (tournoi_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CF607770A');
        $this->addSql('DROP INDEX IDX_232B318CF607770A ON game');
        $this->addSql('ALTER TABLE game DROP tournoi_id, CHANGE equipe_a_id equipe_a_id INT DEFAULT NULL, CHANGE equipe_b_id equipe_b_id INT DEFAULT NULL');
    }
}
