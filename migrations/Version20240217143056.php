<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217143056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE portfolio_perso (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, visibilite TINYINT(1) NOT NULL, etudiant_id INT NOT NULL, INDEX IDX_E03AD064DDEAB1A3 (etudiant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE portfolio_perso ADD CONSTRAINT FK_E03AD064DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portfolio_perso DROP FOREIGN KEY FK_E03AD064DDEAB1A3');
        $this->addSql('DROP TABLE portfolio_perso');
    }
}
