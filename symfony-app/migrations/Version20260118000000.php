<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration representing the existing database schema.
 *
 * This migration does not execute any SQL because the database tables
 * already exist. It serves as a baseline for future migrations.
 *
 * Tables included in this baseline:
 * - activations
 * - articles
 * - ballot
 * - chat
 * - comments
 * - config
 * - division
 * - draftPickHold
 * - draftclockstop
 * - draftdate
 * - draftpicks
 * - draftvote
 * - expansionLost
 * - expansionpicks
 * - expansionprotections
 * - forum
 * - gameplan
 * - images
 * - injuries
 * - ir
 * - issues
 * - newinjuries
 * - newplayers
 * - nflbyes
 * - nflgames
 * - nflrosters
 * - nflstatus
 * - nflteams
 * - nfltransactions
 * - offer
 * - offeredpicks
 * - offeredplayers
 * - offeredpoints
 * - owners
 * - paid
 * - playeroverride
 * - playerscores
 * - playerteams
 * - positioncost
 * - protectionallocation
 * - protectioncost
 * - protections
 * - rankedvote
 * - revisedactivations
 * - roster
 * - schedule
 * - season_flags
 * - stats
 * - team
 * - teamnames
 * - titles
 * - trade
 * - transactions
 * - transpoints
 * - user
 * - waiveraward
 * - waiverorder
 * - waiverpicks
 * - weekmap
 * - years
 */
final class Version20260118000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial baseline migration - existing database schema';
    }

    public function up(Schema $schema): void
    {
        // This migration is intentionally empty.
        // The database schema already exists and this migration serves
        // as a baseline for tracking future schema changes.
        $this->addSql('-- Baseline migration: all tables already exist');
    }

    public function down(Schema $schema): void
    {
        // Cannot reverse the baseline migration
        $this->throwIrreversibleMigrationException('Cannot reverse the baseline migration.');
    }
}
