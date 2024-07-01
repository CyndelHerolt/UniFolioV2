<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701084816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portfolio_univ ADD annee_univ_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE portfolio_univ ADD CONSTRAINT FK_2842A857D34CA3C FOREIGN KEY (annee_univ_id) REFERENCES annee_universitaire (id)');
        $this->addSql('CREATE INDEX IDX_2842A857D34CA3C ON portfolio_univ (annee_univ_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portfolio_univ DROP FOREIGN KEY FK_2842A857D34CA3C');
        $this->addSql('DROP INDEX IDX_2842A857D34CA3C ON portfolio_univ');
        $this->addSql('ALTER TABLE portfolio_univ DROP annee_univ_id');
    }
}
