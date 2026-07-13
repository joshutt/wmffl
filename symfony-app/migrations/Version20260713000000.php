<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 8 trades: persist trade-flow comments and link the offer
 * amendment chain. Both changes are additive — no legacy code reads
 * either one.
 *
 *  - offercomments: one row per non-empty comment submitted anywhere in
 *    the trade flow (offer/amend/counter/accept/reject/withdraw/void).
 *  - offer.PrevOfferID: an amend/counter creates a new offer row; this
 *    nullable column points at the row it replaced so the comment
 *    history follows the negotiation. Legacy rows stay NULL.
 */
final class Version20260713000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add offercomments table and offer.PrevOfferID chain link';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE offercomments (
              CommentID int(11) NOT NULL AUTO_INCREMENT,
              OfferID int(11) NOT NULL,
              TeamID int(11) NOT NULL,
              Action enum('offered','amended','countered','accepted','rejected','withdrawn','voided') NOT NULL,
              Date datetime NOT NULL,
              Comment text NOT NULL,
              PRIMARY KEY (CommentID),
              KEY OfferID_idx (OfferID)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);
        $this->addSql('ALTER TABLE offer ADD PrevOfferID int(11) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offer DROP COLUMN PrevOfferID');
        $this->addSql('DROP TABLE offercomments');
    }
}
