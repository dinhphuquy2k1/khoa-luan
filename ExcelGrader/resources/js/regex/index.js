export default {
    //range of cells, with or without a sheet reference
    REGEXP_RANGEREF: /^((([^\s,!&%^\/\*\+<>=:`-]*)|(\\'(?:[^\\']|\\'[^!])+?\\')|(\\"(?:[^\\"]|\\"[^!])+?\\"))!)?\$?\b([a-z]{1,3})\$?(\d{1,7})(?![\w.]):((([^\s,!&%^\/\*\+<>=:`-]*)|(\\'(?:[^\\']|\\'[^!])+?\\')|(\\"(?:[^\\"]|\\"[^!])+?\\"))!)?\$?\b([a-z]{1,3})\$?(\d{1,7})(?![\w.])$/miu,
    //cell with or without a sheet reference
    REGEXP_CELLREF: /^((([^\s,!&%^\/\*\+<>=:`-]*)|(\\'(?:[^\\']|\\'[^!])+?\\')|(\\"(?:[^\\"]|\\"[^!])+?\\"))!)?\$?\b([a-z]{1,3})\$?(\d{1,7})(?![\w.])$/miu,
    //cell without a sheet reference
    REGEXP_CELL_NO_REF: /^([^\s,!&%^\/\*\+<>=:`-]*)?\$?\b([a-z]{1,3})\$?(\d{1,7})$/miu,
    //range of cells without a sheet reference
    REGEXP_RANGE_NO_REF: /^([^\s,!&%^\/\*\+<>=:`-]*)?\$?\b([a-z]{1,3})\$?(\d{1,7}):([^\s,!&%^\/\*\+<>=:`-]*)?\$?\b([a-z]{1,3})\$?(\d{1,7})$/miu,
    // CALCULATION_REGEXP_NUMBER: '[-+]?\d*\.?\d+(e[-+]?\d+)?',
    // //    String operand
    // CALCULATION_REGEXP_STRING: '"(?:[^"]|"")*"',
    // //    Opening bracket
    // CALCULATION_REGEXP_OPENBRACE: '\(',
    // //    Function (allow for the old @ symbol that could be used to prefix a function, but we'll ignore it)
    // CALCULATION_REGEXP_FUNCTION: '@?(?:_xlfn\.)?(?:_xlws\.)?([\p{L}][\p{L}\p{N}\.]*)[\s]*\(',
    // //    Cell reference (cell or range of cells, with or without a sheet reference)
    // CALCULATION_REGEXP_CELLREF: '((([^\s,!&%^\/\*\+<>=:`-]*)|(\\\'(?: [^\\\']|\\\'[^ !]) +?\\\')|(\\\"(?:[^\\\"]|\\\"[^!])+?\\\"))!)?\$?\b([a-z]{1,3})\$?(\d{1,7})(?![\w.])',
    // //    Cell reference (with or without a sheet reference) ensuring absolute/relative
    // CALCULATION_REGEXP_CELLREF_RELATIVE: '((([^\s\(,!&%^\/\*\+<>=:`-]*)|(\/\'(?:[^\/\']|\/\'[^!])+?\/\')|(\"(?:[^\"]|\"[^!])+?\"))!)?(\$?\b[a-z]{1,3})(\$?\d{1,7})(?![\w.])',
    // CALCULATION_REGEXP_COLUMN_RANGE: '(((([^\s\(,!&%^\/\*\+<>=:`-]*)|(\/\'(?:[^\/\']|\/\'[^!])+?\/\')|(\".(?:[^\"]|\"[^!])?\"))!)?(\$?[a-z]{1,3})):(?![.*])',
    // CALCULATION_REGEXP_ROW_RANGE: '(((([^\s\(,!&%^\/\*\+<>=:`-]*)|(\/\'(?:[^\/\']|\/\'[^!])+?\/\')|(\"(?:[^\"]|\"[^!])+?\"))!)?(\$?[1-9][0-9]{0,6})):(?![.*])',
    // //    Cell reference (with or without a sheet reference) ensuring absolute/relative
    // //    Cell ranges ensuring absolute/relative
    // CALCULATION_REGEXP_COLUMNRANGE_RELATIVE: '(\$?[a-z]{1,3}):(\$?[a-z]{1,3})',
    // CALCULATION_REGEXP_ROWRANGE_RELATIVE: '(\$?\d{1,7}):(\$?\d{1,7})',
    // //    Defined Names: Named Range of cells, or Named Formulae
    // CALCULATION_REGEXP_DEFINEDNAME: '((([^\s,!&%^\/\*\+<>=-]*)|(\/\'(?:[^\/\']|\/\'[^!])+?\/\')|(\"(?:[^\"]|\"[^!])+?\"))!)?([_\p{L}][_\p{L}\p{N}\.]*)',
    // // Structured Reference (Fully Qualified and Unqualified)
    // CALCULATION_REGEXP_STRUCTURED_REFERENCE: '([\p{L}_\\\\][\p{L}\p{N}\._]+)?(\[(?:[^\d\]+-])?)',
    // //    Error
    // CALCULATION_REGEXP_ERROR: '\#[A-Z][A-Z0_\/]*[!\?]?',

    // /** ants */
    // RETURN_ARRAY_AS_ERROR: 'error',
    // RETURN_ARRAY_AS_VALUE: 'value',
    // RETURN_ARRAY_AS_ARRAY: 'array',

    // FORMULA_OPEN_FUNCTION_BRACE: '(',
    // FORMULA_CLOSE_FUNCTION_BRACE: ')',
    // FORMULA_OPEN_MATRIX_BRACE: '{',
    // FORMULA_CLOSE_MATRIX_BRACE: '}',
    // FORMULA_STRING_QUOTE: '"',

    //    regexpMatchString: /^((?<string>"(?:[^"]|"")*")|(?<function>@?(?:_xlfn\.)?(?:_xlws\.)?([\p{L}][\p{L}\p{N}\.]*)[\s]*\()|(?<cellRef>((([^\s,!&%^\/\*\+<>=:`-]*)|(\'(?:[^\']|\'[^!])+?\')|(\"(?:[^\"]|\"[^!])+?\"))!)?\$?\b([a-z]{1,3})\$?(\d{1,7})(?![\w.]))|(?<colRange>(((([^\s\(,!&%^\/\*\+<>=:`-]*)|(\'(?:[^\']|\'[^!])+?\')|(\".(?:[^\"]|\"[^!])?\"))!)?(\$?[a-z]{1,3})):(?![.*]))|(?<rowRange>(((([^\s\(,!&%^\/\*\+<>=:`-]*)|(\'(?:[^\']|\'[^!])+?\')|(\"(?:[^\"]|\"[^!])+?\"))!)?(\$?[1-9][0-9]{0,6})):(?![.*]))|(?<number>[-+]?\d*\.?\d+(e[-+]?\d+)?)|(?<openBrace>\()|(?<structuredReference>([\p{L}_\\\\][\p{L}\p{N}\._]+)?(\[(?:[^\d\]+-])?))|(?<definedName>((([^\s,!&%^\/\*\+<>=-]*)|(\'(?:[^\']|\'[^!])+?\')|(\"(?:[^\"]|\"[^!])+?\"))!)?([_\p{L}][_\p{L}\p{N}\.]*))|(?<error>\#[A-Z][A-Z0_\/]*[!\?]?))/sui


}
