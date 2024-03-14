<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314132341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE validation_criteres (id INT AUTO_INCREMENT NOT NULL, valeur DOUBLE PRECISION NOT NULL, critere_id INT NOT NULL, validation_id INT NOT NULL, INDEX IDX_EBA0D9F9E5F45AB (critere_id), INDEX IDX_EBA0D9FA2274850 (validation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE validation_criteres ADD CONSTRAINT FK_EBA0D9F9E5F45AB FOREIGN KEY (critere_id) REFERENCES criteres (id)');
        $this->addSql('ALTER TABLE validation_criteres ADD CONSTRAINT FK_EBA0D9FA2274850 FOREIGN KEY (validation_id) REFERENCES validation (id)');
        $this->addSql('ALTER TABLE criteres ADD valeurs JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE validation_criteres DROP FOREIGN KEY FK_EBA0D9F9E5F45AB');
        $this->addSql('ALTER TABLE validation_criteres DROP FOREIGN KEY FK_EBA0D9FA2274850');
        $this->addSql('DROP TABLE validation_criteres');
        $this->addSql('ALTER TABLE criteres DROP valeurs');
    }
}
