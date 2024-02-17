<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217133240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE portfolio_univ (id INT AUTO_INCREMENT NOT NULL, date_creation DATE NOT NULL, date_modification DATE DEFAULT NULL, libelle VARCHAR(255) NOT NULL, visibilite TINYINT(1) NOT NULL, banniere VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, opt_search TINYINT(1) NOT NULL, etudiant_id INT NOT NULL, annee_id INT NOT NULL, INDEX IDX_2842A85DDEAB1A3 (etudiant_id), INDEX IDX_2842A85543EC5F0 (annee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE portfolio_univ ADD CONSTRAINT FK_2842A85DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE portfolio_univ ADD CONSTRAINT FK_2842A85543EC5F0 FOREIGN KEY (annee_id) REFERENCES annee (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portfolio_univ DROP FOREIGN KEY FK_2842A85DDEAB1A3');
        $this->addSql('ALTER TABLE portfolio_univ DROP FOREIGN KEY FK_2842A85543EC5F0');
        $this->addSql('DROP TABLE portfolio_univ');
    }
}
