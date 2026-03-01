<?php

namespace App\Enum;

enum VoteEnum: string
{
    case Accept = 'Accept';
    case Reject = 'Reject';
    case Abstain = 'Abstain';
    case NoVote = 'No Vote';
}
