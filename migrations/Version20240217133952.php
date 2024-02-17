<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217133952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trace (id INT AUTO_INCREMENT NOT NULL, bibliotheque_id INT NOT NULL, INDEX IDX_315BD5A14419DE7D (bibliotheque_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE trace ADD CONSTRAINT FK_315BD5A14419DE7D FOREIGN KEY (bibliotheque_id) REFERENCES bibliotheque (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace DROP FOREIGN KEY FK_315BD5A14419DE7D');
        $this->addSql('DROP TABLE trace');
    }
}
