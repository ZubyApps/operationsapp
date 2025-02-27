<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315130728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D92619BE04EA9');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D92619BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D92619BE04EA9');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D92619BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
