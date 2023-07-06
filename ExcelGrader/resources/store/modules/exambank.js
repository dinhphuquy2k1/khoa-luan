import FormMode from "../../js/base/base-enum/form-mode";
import { getExamBank, configureExam, getExamBankSetting } from "../../api/exambank";
const state = {
    examBankData: [],
    examBankSetting: [],
    isShowModal: false,
    modeModal: FormMode.None,
    configureExamData: [],
    isconfigureExam: false,

}

const mutations = {
    SET_EXAMBANK_DATA: (state, examBankData) => {
        state.examBankData = examBankData
    },

    SET_EXAMBANK_SETTING: (state, examBankSetting) => {
        state.examBankSetting = examBankSetting
    },

    /**
     * Dữ liệu thiết lập đề
     * @param {*} state
     * @param {*} configureExamData
     */
    SET_CONFIGURE_EXAM_DATA: (state, configureExamData) => {
        state.configureExamData = configureExamData
    },

    /**
     * Ẩn/hiện form thiết lập
     * @param {*} state
     */
    SET_IS_CONFIGURE_EXAM: (state) => {
        state.isconfigureExam = !state.isconfigureExam
    },
}

const actions = {
    /**
     * Hàm lấy toàn bộ danh sách đề thi
     * DPQuy - 19/04/2022
     **/
    getExamBank({ commit }) {
        return new Promise((resolve, reject) => {
            getExamBank().then(response => {
                commit('SET_EXAMBANK_DATA', response)
                resolve()
            }).catch(error => {
                reject(error)
            })
        })
    },

    /**
     * Hàm lấy danh sách đề thi đã thiết lập
     * DPQuy - 19/04/2023
     **/
    getExamBankSetting({ commit }) {
        return new Promise((resolve, reject) => {
            getExamBankSetting().then(response => {
                commit('SET_EXAMBANK_SETTING', response)
                resolve()
            }).catch(error => {
                reject(error)
            })
        })
    },

    /**
     * Lấy dữ liệu thiết lập đề
     * @param {*} param0
     * @param {*} id id đề thi
     * Enum,SheetCount
     */
    getConfigureExam({ commit }) {
        return new Promise((resolve, reject) => {
            configureExam(state.examBankData.ExamBankId).then(res => {
                commit('SET_CONFIGURE_EXAM_DATA', res);
                resolve()
            }).catch(error => {
                reject(error)
            })
        })
    },

    /**
     * Ẩn hiện form thiết lập đề
     * @param {*} param0
     */
    toggleConfigureExam({ commit }, data) {
        commit('SET_IS_CONFIGURE_EXAM');
        commit('SET_EXAMBANK_DATA', data);
    }


}
export default {
    namespaced: true,
    state,
    mutations,
    actions
}
