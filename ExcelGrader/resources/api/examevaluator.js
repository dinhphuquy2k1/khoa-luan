import request from '../api/utils/request'

/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function getDetailExamManager() {
    return request({
        url: 'api/exam-evaluator',
        method: 'GET',
    })
}


/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function excuteEvaluator(data) {
    return request({
        url: 'api/exam-grader',
        method: 'POST',
        data: data,
        timeout: 5000 * 2
    })
}


/**
 * Lấy kết quả chấm
 * @returns
 */
export function getExamResult(data) {
    return request({
        url: 'api/exam-evaluator',
        method: 'POST',
        data: data
    })
}


/**
 * Lấy kết quả chấm
 * @returns
 */
export function getExamResultDetail(studentCode) {
    return request({
        url: `api/exam-evaluator/${studentCode}`,
        method: 'GET',
    })
}

/**
 * Lấy danh sách phân chia đề thi
 * @returns
 */
export function exportExamList(data) {
    return request({
        url: 'api/exam-evaluator/exportExamList',
        method: 'POST',
        data: data,
        responseType: 'arraybuffer',
        headers: {
            'Accept': 'application/octet-stream',
        },
    })
}

/**
 * Lấy kết quả chấm
 * @returns
 */
export function exportExamResult(data) {
    return request({
        url: 'api/exam-evaluator/export',
        method: 'POST',
        data: data,
        responseType: 'arraybuffer',
        headers: {
            'Accept': 'application/octet-stream',
        },
    })
}


