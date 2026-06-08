<?php

namespace App\Enum;

enum ContentType: string
{
    case MOVIE = 'film';
    case SERIES = 'serie';
    case DOCUMENTARY = 'documentaire';
    case THEATER = 'piece_de_theatre';
}
