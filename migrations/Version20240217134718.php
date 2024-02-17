<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217134718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trace_page (id INT AUTO_INCREMENT NOT NULL, ordre INT NOT NULL, trace_id INT NOT NULL, page_id INT NOT NULL, INDEX IDX_B356FF55BE0D4B70 (trace_id), INDEX IDX_B356FF55C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE trace_page ADD CONSTRAINT FK_B356FF55BE0D4B70 FOREIGN KEY (trace_id) REFERENCES trace (id)');
        $this->addSql('ALTER TABLE trace_page ADD CONSTRAINT FK_B356FF55C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace_page DROP FOREIGN KEY FK_B356FF55BE0D4B70');
        $this->addSql('ALTER TABLE trace_page DROP FOREIGN KEY FK_B356FF55C4663E4');
        $this->addSql('DROP TABLE trace_page');
    }
}
