<?php

namespace App\Enum;

enum NflStatusEnum: string
{
    case B = 'B';  // Bye
    case P = 'P';  // Playing
    case F = 'F';  // Final
    case L = 'L';  // Late
}
