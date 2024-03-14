<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314100626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE criteres ADD departement_id INT NOT NULL');
        $this->addSql('ALTER TABLE criteres ADD CONSTRAINT FK_E913A5C5CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('CREATE INDEX IDX_E913A5C5CCF9E01E ON criteres (departement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE criteres DROP FOREIGN KEY FK_E913A5C5CCF9E01E');
        $this->addSql('DROP INDEX IDX_E913A5C5CCF9E01E ON criteres');
        $this->addSql('ALTER TABLE criteres DROP departement_id');
    }
}
