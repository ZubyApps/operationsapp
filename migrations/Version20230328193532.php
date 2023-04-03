<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230328193532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE expenses DROP FOREIGN KEY FK_2496F35BBE04EA9');
        //$this->addSql('DROP INDEX IDX_2496F35BBE04EA9 ON expenses');
        $this->addSql('ALTER TABLE expenses DROP job');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expenses ADD job_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35BBE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2496F35BBE04EA9 ON expenses (job_id)');
    }
}
