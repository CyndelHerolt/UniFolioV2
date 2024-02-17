<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217140206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, date_creation DATE NOT NULL, date_modification DATE DEFAULT NULL, contenu LONGTEXT NOT NULL, visibilite TINYINT(1) NOT NULL, parent INT DEFAULT NULL, enfant INT DEFAULT NULL, portfolio_id INT DEFAULT NULL, trace_id INT DEFAULT NULL, enseignant_id INT NOT NULL, INDEX IDX_67F068BCB96B5643 (portfolio_id), INDEX IDX_67F068BCBE0D4B70 (trace_id), INDEX IDX_67F068BCE455FCC0 (enseignant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCB96B5643 FOREIGN KEY (portfolio_id) REFERENCES portfolio_univ (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCBE0D4B70 FOREIGN KEY (trace_id) REFERENCES trace (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCE455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCB96B5643');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCBE0D4B70');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCE455FCC0');
        $this->addSql('DROP TABLE commentaire');
    }
}
