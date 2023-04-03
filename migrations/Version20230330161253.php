<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330161253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expenses ADD sponsor_id INT UNSIGNED DEFAULT NULL after category_id');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B12F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id)');
        $this->addSql('CREATE INDEX IDX_2496F35B12F7FB51 ON expenses (sponsor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expenses DROP FOREIGN KEY FK_2496F35B12F7FB51');
        $this->addSql('DROP INDEX IDX_2496F35B12F7FB51 ON expenses');
        $this->addSql('ALTER TABLE expenses DROP sponsor_id');
    }
}
