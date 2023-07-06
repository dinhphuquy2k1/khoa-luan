
const DEFAULT_RANGE = 'A1:A1';
const A1_COORDINATE_REGEX = /^(?<col>\$?[A-Z]{1,3})(?<row>\$?\d{1,7})$/i;
/**
 *  Checks if a Cell Address represents a range of cells.
 * @param {*} cellAddress cellAddress eg: 'A1' or 'A1:A2' or 'A1:A2,C1:C2'
 * @returns bool Whether the coordinate represents a range of cells
 */
export function coordinateIsRange(cellAddress) {
    return (cellAddress.indexOf(':') !== -1 || (cellAddress.indexOf(',') !== -1));
}

/**
 * Split range into coordinate strings.
 * @param {*} range $range e.g. 'B4:D9' or 'B4:D9,H2:O11' or 'B4'
 * @returns Array containing one or more arrays containing one or two coordinate strings
 * e.g. ['B4','D9'] or [['B4','D9'], ['H2','O11']]  or ['B4']
 */
export function splitRange(range) {
    // Ensure $pRange is a valid range
    if (Array.isArray(range) && !range.length) {
        range = DEFAULT_RANGE;
    }
    var exploded = range.split(',');
    for (let index = 0; index < exploded.length; ++index) {
        // @phpstan-ignore-next-line
        exploded[index] = exploded[index].split(':');
    }

    return exploded;
}
/**
 * Column index from string.
 * @param {*} columnAddress columnAddress eg 'A'
 * @returns
 */
export function columnIndexFromString(columnAddress) {
    return columnAddress.toUpperCase().split('').reduce((acc, cur) => acc * 26 + cur.charCodeAt(0) - 64, 0);
}

/**
 * Convert string coordinate to [0 => true : tách được| false: không tách được  ,1 => int column index, 2 => int row index].
 * @param {*} cellAddress
 * @returns
 */
export function coordinateFromStringT(cellAddress) {
    var matches = cellAddress.match(A1_COORDINATE_REGEX);
    try {
        return [matches.groups['col'], matches.groups['row']];
    } catch (error) {
    }

}




