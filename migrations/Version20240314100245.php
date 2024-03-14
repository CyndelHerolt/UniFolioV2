<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314100245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE criteres (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, valeur INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE criteres_validation (criteres_id INT NOT NULL, validation_id INT NOT NULL, INDEX IDX_E3BAAA4FA6EB9800 (criteres_id), INDEX IDX_E3BAAA4FA2274850 (validation_id), PRIMARY KEY(criteres_id, validation_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE criteres_validation ADD CONSTRAINT FK_E3BAAA4FA6EB9800 FOREIGN KEY (criteres_id) REFERENCES criteres (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE criteres_validation ADD CONSTRAINT FK_E3BAAA4FA2274850 FOREIGN KEY (validation_id) REFERENCES validation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE validation ADD pourcentage_global DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE criteres_validation DROP FOREIGN KEY FK_E3BAAA4FA6EB9800');
        $this->addSql('ALTER TABLE criteres_validation DROP FOREIGN KEY FK_E3BAAA4FA2274850');
        $this->addSql('DROP TABLE criteres');
        $this->addSql('DROP TABLE criteres_validation');
        $this->addSql('ALTER TABLE validation DROP pourcentage_global');
    }
}
