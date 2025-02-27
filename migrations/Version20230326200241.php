<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230326200241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departments DROP FOREIGN KEY FK_16AEB8D4F41A619E');
        $this->addSql('DROP INDEX IDX_16AEB8D4F41A619E ON departments');
        $this->addSql('ALTER TABLE departments ADD head VARCHAR(255) DEFAULT NULL, DROP head_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departments ADD head_id INT UNSIGNED DEFAULT NULL, DROP head');
        $this->addSql('ALTER TABLE departments ADD CONSTRAINT FK_16AEB8D4F41A619E FOREIGN KEY (head_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_16AEB8D4F41A619E ON departments (head_id)');
    }
}
