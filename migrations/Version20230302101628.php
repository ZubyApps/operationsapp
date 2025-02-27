<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302101628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs ADD paystatus_id INT UNSIGNED DEFAULT NULL, ADD jobType_id INT UNSIGNED DEFAULT NULL, DROP jobtype, DROP paystatus');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5A3C67F0D FOREIGN KEY (jobType_id) REFERENCES jobtypes (id)');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC54967B422 FOREIGN KEY (paystatus_id) REFERENCES paystatus (id)');
        $this->addSql('CREATE INDEX IDX_A8936DC5A3C67F0D ON jobs (jobType_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8936DC54967B422 ON jobs (paystatus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC5A3C67F0D');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC54967B422');
        $this->addSql('DROP INDEX IDX_A8936DC5A3C67F0D ON jobs');
        $this->addSql('DROP INDEX UNIQ_A8936DC54967B422 ON jobs');
        $this->addSql('ALTER TABLE jobs ADD jobtype VARCHAR(255) DEFAULT NULL, ADD paystatus VARCHAR(255) DEFAULT NULL, DROP paystatus_id, DROP jobType_id');
    }
}
