import { createStore } from 'vuex'
import popup from './modules/popup'
import exambank from './modules/exambank'
import user from './modules/user'
import toast from './modules/toast'
export default createStore({
    modules: {
        popup,
        exambank,
        user,
        toast
    },
})
