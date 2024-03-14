<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240313110740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, poste VARCHAR(255) NOT NULL, entreprise VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, activites JSON DEFAULT NULL, cv_id INT DEFAULT NULL, INDEX IDX_590C103CFE419E2 (cv_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE formation (id INT AUTO_INCREMENT NOT NULL, diplome VARCHAR(255) DEFAULT NULL, etablissement VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, activites JSON DEFAULT NULL, cv_id INT DEFAULT NULL, INDEX IDX_404021BFCFE419E2 (cv_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103CFE419E2');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFCFE419E2');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE formation');
    }
}
