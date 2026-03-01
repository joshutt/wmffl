<?php

namespace App\Enum;

enum InjuryStatusEnum: string
{
    case P = 'P';  // Probable
    case Q = 'Q';  // Questionable
    case D = 'D';  // Doubtful
    case O = 'O';  // Out
    case I = 'I';  // IR
    case S = 'S';  // Suspended
}
