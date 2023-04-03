<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314010607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D9261919EB6921');
        $this->addSql('DROP INDEX IDX_B9D9261919EB6921 ON paystatus');
        $this->addSql('ALTER TABLE paystatus DROP client_id, DROP totalAmount, DROP billstatus');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paystatus ADD client_id INT UNSIGNED DEFAULT NULL, ADD totalAmount NUMERIC(13, 3) DEFAULT NULL, ADD billstatus VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D9261919EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_B9D9261919EB6921 ON paystatus (client_id)');
    }
}
