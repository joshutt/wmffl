<?php

namespace App\Enum;

enum ExpansionProtectionTypeEnum: string
{
    case Protect = 'protect';
    case Pullback = 'pullback';
    case Alternate = 'alternate';
}
