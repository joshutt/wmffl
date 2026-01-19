<?php

namespace App\Enum;

enum TransactionMethodEnum: string
{
    case Cut = 'Cut';
    case Sign = 'Sign';
    case Trade = 'Trade';
    case Fire = 'Fire';
    case Hire = 'Hire';
    case ToIR = 'To IR';
    case FromIR = 'From IR';
    case ToCOVID = 'To COVID';
    case FromCOVID = 'From COVID';
}
