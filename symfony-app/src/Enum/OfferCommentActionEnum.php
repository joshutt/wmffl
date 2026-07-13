<?php

namespace App\Enum;

/**
 * What the commenter was doing when they wrote the comment; shown as the
 * label on the trade screen's comment history.
 */
enum OfferCommentActionEnum: string
{
    case Offered = 'offered';
    case Amended = 'amended';
    case Countered = 'countered';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Offered => 'Offered',
            self::Amended => 'Amended',
            self::Countered => 'Countered',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
            self::Voided => 'Voided by league',
        };
    }
}
