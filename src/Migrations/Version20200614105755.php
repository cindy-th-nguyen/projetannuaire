<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200614105755 extends AbstractMigration
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
        $this->addSql('DROP TABLE batiment');
        $this->addSql('DROP TABLE office');
        $this->addSql('ALTER TABLE tutelle CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE responsable DROP FOREIGN KEY responsable_ibfk_1');
        $this->addSql('DROP INDEX activite ON responsable');
        $this->addSql('ALTER TABLE responsable DROP activite');
        $this->addSql('ALTER TABLE personne DROP INDEX IDX_FCEC9EFCFF65260, ADD UNIQUE INDEX UNIQ_FCEC9EFCFF65260 (compte)');
        $this->addSql('ALTER TABLE personne DROP FOREIGN KEY personne_ibfk_1');
        $this->addSql('ALTER TABLE personne DROP workphone, CHANGE birthdate birthdate DATETIME NOT NULL');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT FK_FCEC9EF6B537532 FOREIGN KEY (tutelle) REFERENCES tutelle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE personne RENAME INDEX tutelle TO IDX_FCEC9EF6B537532');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE activitethem (id INT AUTO_INCREMENT NOT NULL, activite INT DEFAULT NULL, thematique INT DEFAULT NULL, INDEX IDX_32160DB33A8ED5A8 (thematique), INDEX IDX_32160DB3B8755515 (activite), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE batiment (id INT NOT NULL, label VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE office (id INT NOT NULL, name INT NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activitethem ADD CONSTRAINT FK_32160DB33A8ED5A8 FOREIGN KEY (thematique) REFERENCES thematique (id)');
        $this->addSql('ALTER TABLE activitethem ADD CONSTRAINT FK_32160DB3B8755515 FOREIGN KEY (activite) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE personne DROP INDEX UNIQ_FCEC9EFCFF65260, ADD INDEX IDX_FCEC9EFCFF65260 (compte)');
        $this->addSql('ALTER TABLE personne DROP FOREIGN KEY FK_FCEC9EF6B537532');
        $this->addSql('ALTER TABLE personne ADD workphone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE birthdate birthdate DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT personne_ibfk_1 FOREIGN KEY (tutelle) REFERENCES tutelle (id)');
        $this->addSql('ALTER TABLE personne RENAME INDEX idx_fcec9ef6b537532 TO tutelle');
        $this->addSql('ALTER TABLE responsable ADD activite INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responsable ADD CONSTRAINT responsable_ibfk_1 FOREIGN KEY (activite) REFERENCES activite (id)');
        $this->addSql('CREATE INDEX activite ON responsable (activite)');
        $this->addSql('ALTER TABLE tutelle CHANGE id id INT NOT NULL');
    }
}
