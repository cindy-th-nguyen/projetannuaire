<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200613193251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE activitethem');
        $this->addSql('DROP TABLE office');
        $this->addSql('ALTER TABLE tutelle CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE responsable DROP FOREIGN KEY responsable_ibfk_1');
        $this->addSql('DROP INDEX activite ON responsable');
        $this->addSql('ALTER TABLE responsable DROP activite');
        $this->addSql('ALTER TABLE personne DROP INDEX IDX_FCEC9EFCFF65260, ADD UNIQUE INDEX UNIQ_FCEC9EFCFF65260 (compte)');
        $this->addSql('ALTER TABLE personne DROP FOREIGN KEY personne_ibfk_1');
        $this->addSql('DROP INDEX tutelle ON personne');
        $this->addSql('ALTER TABLE personne DROP workphone, CHANGE tutelle tutelle VARCHAR(255) DEFAULT NULL, CHANGE birthdate birthdate DATETIME NOT NULL');
        $this->addSql('ALTER TABLE compte DROP FOREIGN KEY FK_CFF65260570C0D4');
        $this->addSql('DROP INDEX IDX_CFF65260570C0D4 ON compte');
        $this->addSql('ALTER TABLE compte DROP groupinfo');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE activitethem (id INT AUTO_INCREMENT NOT NULL, activite INT DEFAULT NULL, thematique INT DEFAULT NULL, INDEX IDX_32160DB33A8ED5A8 (thematique), INDEX IDX_32160DB3B8755515 (activite), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE office (id INT NOT NULL, name INT NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activitethem ADD CONSTRAINT FK_32160DB33A8ED5A8 FOREIGN KEY (thematique) REFERENCES thematique (id)');
        $this->addSql('ALTER TABLE activitethem ADD CONSTRAINT FK_32160DB3B8755515 FOREIGN KEY (activite) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE compte ADD groupinfo INT DEFAULT NULL');
        $this->addSql('ALTER TABLE compte ADD CONSTRAINT FK_CFF65260570C0D4 FOREIGN KEY (groupinfo) REFERENCES groupinfo (id)');
        $this->addSql('CREATE INDEX IDX_CFF65260570C0D4 ON compte (groupinfo)');
        $this->addSql('ALTER TABLE personne DROP INDEX UNIQ_FCEC9EFCFF65260, ADD INDEX IDX_FCEC9EFCFF65260 (compte)');
        $this->addSql('ALTER TABLE personne ADD workphone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE birthdate birthdate DATETIME DEFAULT NULL, CHANGE tutelle tutelle INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT personne_ibfk_1 FOREIGN KEY (tutelle) REFERENCES tutelle (id)');
        $this->addSql('CREATE INDEX tutelle ON personne (tutelle)');
        $this->addSql('ALTER TABLE responsable ADD activite INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responsable ADD CONSTRAINT responsable_ibfk_1 FOREIGN KEY (activite) REFERENCES activite (id)');
        $this->addSql('CREATE INDEX activite ON responsable (activite)');
        $this->addSql('ALTER TABLE tutelle CHANGE id id INT NOT NULL, CHANGE name name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`');
    }
}
