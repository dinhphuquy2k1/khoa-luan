<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Font extends Enum
{
    const Cambria = 0;
    const Calibri = 1;
    const Arial =  2;
    const TimesNewRoman = 3;
    const CalibriLight = 4;
    const Tahoma = 5;
}
