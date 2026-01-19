<?php

namespace App\Enum;

enum NflTransactionActionEnum: string
{
    case Signed = 'Signed';
    case Cut = 'Cut';
    case IR = 'IR';
    case Trade = 'Trade';
    case Draft = 'Draft';
    case Retired = 'Retired';
    case Unknown = 'Unknown';
}
