const state = {
    user: {},
    isShowUserGuide: true,
}

const mutations = {
    SET_USER: (state, user) => {
        state.user = user;
    },

    SET_USER_GUIDE: (state,) => {
        state.isShowUserGuide = !state.isShowUserGuide;
    }

}

const actions = {
    saveUser({ commit }, user) {
        commit('SET_USER', user);
    },

    toggleUserGuide({ commit }) {
        commit('SET_USER_GUIDE');
    }
}
export default {
    namespaced: true,
    state,
    mutations,
    actions
}
