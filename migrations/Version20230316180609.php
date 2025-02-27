<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230316180609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departments DROP FOREIGN KEY FK_16AEB8D4F41A619E');
        $this->addSql('ALTER TABLE departments ADD CONSTRAINT FK_16AEB8D4F41A619E FOREIGN KEY (head_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paystatus CHANGE percentPaid percentPaid NUMERIC(13, 3) NOT NULL, CHANGE billStatus billStatus VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departments DROP FOREIGN KEY FK_16AEB8D4F41A619E');
        $this->addSql('ALTER TABLE departments ADD CONSTRAINT FK_16AEB8D4F41A619E FOREIGN KEY (head_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE paystatus CHANGE percentPaid percentPaid NUMERIC(13, 3) DEFAULT NULL, CHANGE billStatus billStatus VARCHAR(255) DEFAULT NULL');
    }
}
