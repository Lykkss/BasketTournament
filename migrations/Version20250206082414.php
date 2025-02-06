<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206082414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe_user (equipe_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_84DA47B76D861B89 (equipe_id), INDEX IDX_84DA47B7A76ED395 (user_id), PRIMARY KEY(equipe_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, equipe_a_id INT DEFAULT NULL, equipe_b_id INT DEFAULT NULL, vainqueur_id INT DEFAULT NULL, score_equipe_a INT DEFAULT NULL, score_equipe_b INT DEFAULT NULL, INDEX IDX_232B318C3297C2A6 (equipe_a_id), INDEX IDX_232B318C20226D48 (equipe_b_id), INDEX IDX_232B318C773C35EE (vainqueur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournoi (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, date_debut DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_fin DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, nb_max_equipes INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournoi_user (user_id INT NOT NULL, tournoi_id INT NOT NULL, INDEX IDX_D0703ACDA76ED395 (user_id), INDEX IDX_D0703ACDF607770A (tournoi_id), PRIMARY KEY(user_id, tournoi_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B76D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C3297C2A6 FOREIGN KEY (equipe_a_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C20226D48 FOREIGN KEY (equipe_b_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C773C35EE FOREIGN KEY (vainqueur_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE tournoi_user ADD CONSTRAINT FK_D0703ACDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournoi_user ADD CONSTRAINT FK_D0703ACDF607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B76D861B89');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B7A76ED395');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C3297C2A6');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C20226D48');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C773C35EE');
        $this->addSql('ALTER TABLE tournoi_user DROP FOREIGN KEY FK_D0703ACDA76ED395');
        $this->addSql('ALTER TABLE tournoi_user DROP FOREIGN KEY FK_D0703ACDF607770A');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE equipe_user');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE tournoi');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE tournoi_user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
