<?php

namespace App\Enum;

/**
 * Which side of a matchup a gameplan pick applied to. Kept alongside
 * App\Entity\Gameplan for the retired feature's historical rows - see
 * that class for why neither is deleted.
 */
enum GameplanSideEnum: string
{
    case Me = 'Me';
    case Them = 'Them';
}
