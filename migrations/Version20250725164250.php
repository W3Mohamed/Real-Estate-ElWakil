<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725164250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE clients ADD wilaya_id INT NOT NULL, ADD commune_id INT NOT NULL, DROP wilaya, DROP commune
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients ADD CONSTRAINT FK_C82E74DC89F5B6 FOREIGN KEY (wilaya_id) REFERENCES wilaya (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients ADD CONSTRAINT FK_C82E74131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C82E74DC89F5B6 ON clients (wilaya_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C82E74131A4F72 ON clients (commune_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE clients DROP FOREIGN KEY FK_C82E74DC89F5B6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients DROP FOREIGN KEY FK_C82E74131A4F72
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C82E74DC89F5B6 ON clients
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C82E74131A4F72 ON clients
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients ADD wilaya VARCHAR(255) NOT NULL, ADD commune VARCHAR(255) NOT NULL, DROP wilaya_id, DROP commune_id
        SQL);
    }
}
