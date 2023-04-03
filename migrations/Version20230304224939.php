<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230304224939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payments ADD payMethod_id INT UNSIGNED DEFAULT NULL, DROP payMethod');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32B9E90F5B FOREIGN KEY (payMethod_id) REFERENCES paymethods (id)');
        $this->addSql('CREATE INDEX IDX_65D29B32B9E90F5B ON payments (payMethod_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32B9E90F5B');
        $this->addSql('DROP INDEX IDX_65D29B32B9E90F5B ON payments');
        $this->addSql('ALTER TABLE payments ADD payMethod VARCHAR(255) DEFAULT NULL, DROP payMethod_id');
    }
}
