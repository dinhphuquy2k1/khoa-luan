<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SheetName extends Enum
{
    const IdStudent =   0;      //Số báo danh
    const KeyStudent = 1;       //Mã sinh viên
    const StudentName = 2;      //tên sinh viên
    const Other = 3;            //Khác
}
