import request from '../api/utils/request'

/**
 * Thêm mới phòng thi
 * @returns
 */
export function getDashBoardData() {
    return request({
        url: 'api/dash-board',
        method: 'GET',
    })
}
