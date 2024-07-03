<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701135230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE critere_apprentissage_critique (id INT AUTO_INCREMENT NOT NULL, valeur INT NOT NULL, critere_id INT DEFAULT NULL, apprentissage_critique_id INT DEFAULT NULL, page_id INT DEFAULT NULL, INDEX IDX_27378C109E5F45AB (critere_id), INDEX IDX_27378C1062399251 (apprentissage_critique_id), INDEX IDX_27378C10C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE critere_niveau (id INT AUTO_INCREMENT NOT NULL, valeur INT NOT NULL, critere_id INT DEFAULT NULL, apc_niveau_id INT DEFAULT NULL, page_id INT DEFAULT NULL, INDEX IDX_5EBF2C3C9E5F45AB (critere_id), INDEX IDX_5EBF2C3C9445617E (apc_niveau_id), INDEX IDX_5EBF2C3CC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE critere_apprentissage_critique ADD CONSTRAINT FK_27378C109E5F45AB FOREIGN KEY (critere_id) REFERENCES criteres (id)');
        $this->addSql('ALTER TABLE critere_apprentissage_critique ADD CONSTRAINT FK_27378C1062399251 FOREIGN KEY (apprentissage_critique_id) REFERENCES apc_apprentissage_critique (id)');
        $this->addSql('ALTER TABLE critere_apprentissage_critique ADD CONSTRAINT FK_27378C10C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE critere_niveau ADD CONSTRAINT FK_5EBF2C3C9E5F45AB FOREIGN KEY (critere_id) REFERENCES criteres (id)');
        $this->addSql('ALTER TABLE critere_niveau ADD CONSTRAINT FK_5EBF2C3C9445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id)');
        $this->addSql('ALTER TABLE critere_niveau ADD CONSTRAINT FK_5EBF2C3CC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE critere_apprentissage_critique DROP FOREIGN KEY FK_27378C109E5F45AB');
        $this->addSql('ALTER TABLE critere_apprentissage_critique DROP FOREIGN KEY FK_27378C1062399251');
        $this->addSql('ALTER TABLE critere_apprentissage_critique DROP FOREIGN KEY FK_27378C10C4663E4');
        $this->addSql('ALTER TABLE critere_niveau DROP FOREIGN KEY FK_5EBF2C3C9E5F45AB');
        $this->addSql('ALTER TABLE critere_niveau DROP FOREIGN KEY FK_5EBF2C3C9445617E');
        $this->addSql('ALTER TABLE critere_niveau DROP FOREIGN KEY FK_5EBF2C3CC4663E4');
        $this->addSql('DROP TABLE critere_apprentissage_critique');
        $this->addSql('DROP TABLE critere_niveau');
    }
}
