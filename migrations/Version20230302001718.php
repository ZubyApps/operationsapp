<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302001718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paystatus (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, user_id INT UNSIGNED DEFAULT NULL, totalAmount NUMERIC(13, 3) DEFAULT NULL, job VARCHAR(255) DEFAULT NULL, billstatus VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B9D9261919EB6921 (client_id), INDEX IDX_B9D92619A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D9261919EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D92619A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE completed DROP FOREIGN KEY FK_3AF85C6EA76ED395');
        $this->addSql('DROP TABLE completed');
        $this->addSql('ALTER TABLE admins CHANGE `admin` `admin` VARCHAR(255) DEFAULT NULL, CHANGE addedBy addedBy VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE clients CHANGE phone_number phoneNumber VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE departments ADD head VARCHAR(255) DEFAULT NULL, DROP hod, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE editors CHANGE editors editors VARCHAR(255) DEFAULT NULL, CHANGE addedBy addedBy VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE expenses CHANGE amount amount NUMERIC(13, 3) DEFAULT NULL, CHANGE date date VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jobs DROP FOREIGN KEY FK_A8936DC56C7B4691');
        $this->addSql('DROP INDEX IDX_A8936DC56C7B4691 ON jobs');
        $this->addSql('ALTER TABLE jobs ADD jobtype VARCHAR(255) DEFAULT NULL, DROP jobtype_id, CHANGE details details VARCHAR(255) DEFAULT NULL, CHANGE dueDate dueDate DATETIME DEFAULT NULL, CHANGE amountDue amountDue DOUBLE PRECISION DEFAULT NULL, CHANGE jobStatus jobStatus VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE payments CHANGE payMethod payMethod VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE completed (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, totalAmount NUMERIC(13, 3) NOT NULL, paystatus VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3AF85C6EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE completed ADD CONSTRAINT FK_3AF85C6EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D9261919EB6921');
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D92619A76ED395');
        $this->addSql('DROP TABLE paystatus');
        $this->addSql('ALTER TABLE departments ADD hod VARCHAR(255) NOT NULL, DROP head, CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE Admins CHANGE `admin` `admin` VARCHAR(255) NOT NULL, CHANGE addedBy addedBy VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE jobs ADD jobtype_id INT UNSIGNED DEFAULT NULL, DROP jobtype, CHANGE details details VARCHAR(255) NOT NULL, CHANGE dueDate dueDate DATETIME NOT NULL, CHANGE amountDue amountDue DOUBLE PRECISION NOT NULL, CHANGE jobStatus jobStatus VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC56C7B4691 FOREIGN KEY (jobtype_id) REFERENCES jobtypes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A8936DC56C7B4691 ON jobs (jobtype_id)');
        $this->addSql('ALTER TABLE payments CHANGE payMethod payMethod VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE clients CHANGE phoneNumber phone_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE editors CHANGE editors editors VARCHAR(255) NOT NULL, CHANGE addedBy addedBy VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE expenses CHANGE amount amount NUMERIC(13, 3) NOT NULL, CHANGE date date VARCHAR(255) NOT NULL');
    }
}
