<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416100438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE band (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_48DFA2EB7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE band_user (band_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D6A5361249ABEB17 (band_id), INDEX IDX_D6A53612A76ED395 (user_id), PRIMARY KEY(band_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE band ADD CONSTRAINT FK_48DFA2EB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE band_user ADD CONSTRAINT FK_D6A5361249ABEB17 FOREIGN KEY (band_id) REFERENCES band (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE band_user ADD CONSTRAINT FK_D6A53612A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE band DROP FOREIGN KEY FK_48DFA2EB7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE band_user DROP FOREIGN KEY FK_D6A5361249ABEB17
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE band_user DROP FOREIGN KEY FK_D6A53612A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE band
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE band_user
        SQL);
    }
}
