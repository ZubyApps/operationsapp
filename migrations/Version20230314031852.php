<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314031852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC54967B422');
        $this->addSql('DROP INDEX UNIQ_A8936DC54967B422 ON jobs');
        $this->addSql('ALTER TABLE jobs DROP paystatus_id');
        $this->addSql('ALTER TABLE paystatus ADD job_id INT UNSIGNED DEFAULT NULL after id');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D92619BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B9D92619BE04EA9 ON paystatus (job_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs ADD paystatus_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC54967B422 FOREIGN KEY (paystatus_id) REFERENCES paystatus (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8936DC54967B422 ON jobs (paystatus_id)');
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D92619BE04EA9');
        $this->addSql('DROP INDEX UNIQ_B9D92619BE04EA9 ON paystatus');
        $this->addSql('ALTER TABLE paystatus DROP job_id');
    }
}
