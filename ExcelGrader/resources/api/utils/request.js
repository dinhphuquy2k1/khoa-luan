import axios from 'axios'
import store from '../../store'
const service = axios.create({
    baseURL: process.env.MIX_BASE_URL, // uri = baseURL + apiFunction truyền tới
    timeout: 5000,
    headers: { // Request Headers
        project: 'rd-test',
        apikey: 'r6fArtu86fTvdertojf5Hferdcbhmr3U',
        'content-type': 'application/json',
        'Accept': 'application/json'
    }
})

service.interceptors.request.use(
    config => {
        if (store.getters.token) {
            config.headers['X-Token'] = getToken()
        }
        return config
    },
    error => {
        console.log('error', error)
    }
)

service.interceptors.response.use(
    response => {
        const res = response.data
        return res
    },
    error => {
        if (error.response.status == 500) {
            // alert(1);
            store.dispatch('toast/handleInternalServerError');
        }
        return Promise.reject(error)
    }
)

export default service
