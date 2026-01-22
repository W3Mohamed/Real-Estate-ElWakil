<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121200230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE promoteur (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, commune_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(15) NOT NULL, superficie_min INT NOT NULL, superficie_max INT NOT NULL, INDEX IDX_6C2B2221C54C8C93 (type_id), INDEX IDX_6C2B2221131A4F72 (commune_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promoteur_wilaya (promoteur_id INT NOT NULL, wilaya_id INT NOT NULL, INDEX IDX_D216D40354C056EB (promoteur_id), INDEX IDX_D216D403DC89F5B6 (wilaya_id), PRIMARY KEY(promoteur_id, wilaya_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur ADD CONSTRAINT FK_6C2B2221C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur ADD CONSTRAINT FK_6C2B2221131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur_wilaya ADD CONSTRAINT FK_D216D40354C056EB FOREIGN KEY (promoteur_id) REFERENCES promoteur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur_wilaya ADD CONSTRAINT FK_D216D403DC89F5B6 FOREIGN KEY (wilaya_id) REFERENCES wilaya (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur DROP FOREIGN KEY FK_6C2B2221C54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur DROP FOREIGN KEY FK_6C2B2221131A4F72
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur_wilaya DROP FOREIGN KEY FK_D216D40354C056EB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promoteur_wilaya DROP FOREIGN KEY FK_D216D403DC89F5B6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE promoteur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE promoteur_wilaya
        SQL);
    }
}
