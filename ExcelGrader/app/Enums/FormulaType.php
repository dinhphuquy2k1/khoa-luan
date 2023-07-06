<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FormulaType extends Enum
{
    const CellReference = 'Cell Reference'; // địa chỉ
    const StructuredReference = 'Structured Reference'; //vị trí tham chiếu nằm trong bảng
    const COUNTIF = 13;
    const SUM = 14;
    const MAX = 15;
    const MIN = 16;
    const AVERAGE = 17;
    const SUMIF = 18;
    const RANK = 19;
    const IF = 20;
    const EXPRESSION = 21;
}
