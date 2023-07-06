import { handleServerError, handleSuccess } from '../../js/app';
const state = {
}

const mutations = {

}

const actions = {
    handleInternalServerError({ commit }) {
        handleServerError();
    },
    handleInternalServerSuccess({ commit }, message) {
        handleSuccess(message);
    }
}
export default {
    namespaced: true,
    state,
    mutations,
    actions
}
