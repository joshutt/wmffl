<?php

namespace App\Enum;

enum OfferStatusEnum: string
{
    case Accept = 'Accept';
    case Reject = 'Reject';
    case Pending = 'Pending';
    case Withdrawn = 'Withdrawn';
    case Expired = 'Expired';
    case Modified = 'Modified';
}
