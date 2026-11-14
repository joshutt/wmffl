<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login & security upscale (Phase 18 item 1,
 * specs/2026-11-14-login-upscale/):
 *
 * - Widens user.Password to varchar(255) so it can hold bcrypt/argon2
 *   hashes; existing MD5 hashes are upgraded silently at each user's next
 *   login.
 * - Blanks the password of every inactive user. A blank password can
 *   never authenticate; reactivating a user means sending them a
 *   set-password link from /admin/users. down() CANNOT restore these
 *   hashes — restore the pre-deploy `user` backup if they're needed.
 * - Moves email addresses into a new user_email table (several per user,
 *   exactly one flagged primary) and drops user.Email. down() re-adds the
 *   column and refills it from each user's primary address.
 * - Adds password_reset_token (SHA-256 of the emailed token only).
 *
 * Remember-me uses Symfony's signature cookies, so no token table.
 */
final class Version20261114000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Login upscale: widen user.Password, blank inactive passwords (irreversible), user_email replaces user.Email, password_reset_token';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user MODIFY Password varchar(255) NOT NULL DEFAULT ''");
        $this->addSql("UPDATE user SET Password = '' WHERE active = 'N'");

        $this->addSql(<<<'SQL'
            CREATE TABLE user_email (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) NOT NULL,
                email varchar(255) NOT NULL,
                is_primary tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_user_email_user_address (user_id, email),
                CONSTRAINT fk_user_email_user FOREIGN KEY (user_id) REFERENCES user (UserID) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO user_email (user_id, email, is_primary, created_at)
            SELECT UserID, TRIM(Email), 1, NOW() FROM user WHERE TRIM(Email) <> ''
            SQL);
        $this->addSql('ALTER TABLE user DROP COLUMN Email');

        $this->addSql(<<<'SQL'
            CREATE TABLE password_reset_token (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) NOT NULL,
                token_hash char(64) NOT NULL,
                expires_at datetime NOT NULL,
                used_at datetime DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_password_reset_token_hash (token_hash),
                KEY idx_password_reset_token_user (user_id),
                CONSTRAINT fk_password_reset_token_user FOREIGN KEY (user_id) REFERENCES user (UserID) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE password_reset_token');

        $this->addSql("ALTER TABLE user ADD Email varchar(75) NOT NULL DEFAULT '' AFTER Name");
        $this->addSql(<<<'SQL'
            UPDATE user u JOIN user_email e ON e.user_id = u.UserID AND e.is_primary = 1
            SET u.Email = LEFT(e.email, 75)
            SQL);
        $this->addSql('DROP TABLE user_email');

        // Modern hashes would be truncated by varchar(50); only narrow the
        // column back if every stored value still fits.
        $this->addSql(<<<'SQL'
            SET @fits = (SELECT COUNT(*) = 0 FROM user WHERE LENGTH(Password) > 50)
            SQL);
        $this->addSql(<<<'SQL'
            SET @sql = IF(@fits, "ALTER TABLE user MODIFY Password varchar(50) NOT NULL DEFAULT ''", 'DO 0')
            SQL);
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }
}
