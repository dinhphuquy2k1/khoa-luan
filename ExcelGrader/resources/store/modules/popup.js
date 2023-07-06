import FormMode from "../../js/base/base-enum/form-mode";
const state = {
    isShowModal: false,
    modeModal: FormMode.None,
    selectedData: {},
}

const mutations = {
    /**
     * Ẩn/hiện popup
     * @param {*} state
     */
    IS_SHOW_MODAL: (state, modeModal = FormMode.None) => {
        state.isShowModal = !state.isShowModal;
        state.modeModal = modeModal;
    },

    SELECTED_DATA: (state, selectedData) => {
        state.selectedData = selectedData;
    }
}

const actions = {
    toggleModal({ commit }, modeModal) {
        commit('IS_SHOW_MODAL', modeModal);
    },

    setSelectedData({ commit }, selectedData) {
        commit('SELECTED_DATA', selectedData);
    }
}
export default {
    namespaced: true,
    state,
    mutations,
    actions
}
