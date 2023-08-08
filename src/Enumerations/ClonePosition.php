<?php

namespace Shishima\TranslateSpreadsheet\Enumerations;

enum ClonePosition
{
    case AppendCurrentSheet;
    case PrependCurrentSheet;
    case AppendLastSheet;
    case PrependFirstSheet;
}
