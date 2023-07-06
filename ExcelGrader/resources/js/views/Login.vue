<template>
    <div class="bg-image position-relative" v-if="!loggedUser"></div>
    <div class="box box-login flex-row" v-if="!loggedUser">
        <div class="notification">
            <div class="toast-wrong-account flex-row" v-if="invalid['error']">
                <div class="text-wrong-account">Tên đăng nhập hoặc mật khẩu không đúng.</div>
            </div>
        </div>
        <div class="box-img-left">
            <img src="../../../public/assets/images/img/welcome.png" alt="">
        </div>
        <div class="box-body flex-column">
            <div class="login-form">
                <div class="bg-title">
                    <img src="../../../public/assets/images/img/logo-it.webp" alt="" class="title">
                </div>
                <form action="" @submit.prevent="doLogin">
                    <div class="content">
                        <div class="form-group validate-input">
                            <label class="top-label d-flex subtitle" style="width:100%;">
                                <span style="font-weight: lighter">Đăng nhập để làm việc với<b
                                        style="margin-left: 5px">Excel
                                        Garder</b>
                                </span>
                            </label>
                            <div>
                                <InputText v-model.trim="user.email" class="form-control input" placeholder="Địa chỉ email"
                                    @keypress="validateSpace" />
                            </div>
                            <div class="error-text" v-if="invalid['email']">
                                <span class="mi-icon24"></span>
                                <span style="padding-left: 2px;">{{ invalid['email'] }}</span>
                            </div>
                        </div>
                        <div class="form-group validate-input">
                            <div class="flex-column">
                                <label style="width:100%; padding-right: 0; position: relative">
                                    <InputText v-model="user.password" :type="isShowPassword ? 'text' : 'password'"
                                        class="form-control input" placeholder="Mật khẩu" />
                                    <div id="togglepassword" class="eye" @click="isShowPassword = !isShowPassword"
                                        :class="{ 'eye-slash': isShowPassword }"></div>
                                </label>
                                <div class=" flex-row align-items-center" v-if="invalid['password']">
                                    <div style="padding-top: 2px;" class="icon-error-text"></div>
                                    <div style="padding-left: 2px; margin-top: 0;" class="error-text">{{ invalid['password']
                                    }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Button type="submit" label="Đăng nhập"
                            class="d-flex align-items-center btn btn-primary btn-login text" @click="doLogin"
                            @keyup.enter="doLogin" />
                    </div>
                </form>
                <div class="footer-login flex-center">
                    <button class="btn btn-link text">Quên mật khẩu</button>
                </div>
            </div>
        </div>
        <div class="copy-right">
            <div class="copy-right-text">Copyright © 2022 - 2023 K69 CNTT</div>
        </div>
    </div>
    <TheLoadingProgress v-if="isLoading" />
    <Toast />
</template>

<script>
import Auth from '../../api/utils/auth';
import { login } from '../../api/user'
import Toast from 'primevue/toast';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import TheLoadingProgress from '../components/TheLoadingProgress.vue';
import { mapState, mapActions } from 'vuex'
export default {
    components: {
        Button,
        InputText,
        TheLoadingProgress,
        Toast
    },
    emits: ["setLoggedUser"],
    data() {
        return {
            isShowPassword: false,

            invalid: [],
            loggedUser: Auth.check(),
            isLoading: false,
            user: {
                email: null,
                password: null,
            }
        }
    },
    methods: {
        ...mapActions('user', ['saveUser']),

        doLogin() {
            this.invalid = [];
            if (this.user.email == null) {
                this.invalid['email'] = 'Email không được để trống';
            }

            if (!this.validateemail()) {
                this.invalid['email'] = 'Email không đúng định dạng';
            }
            if (this.user.password == null || String(this.user.password).trim() == '') {
                this.invalid['password'] = 'Mật khẩu không được để trống';
            }
            //xảy ra lỗi
            if (Object.keys(this.invalid).length > 0) return;

            this.isLoading = true;
            login(this.user).then(res => {
                Auth.login(res.access_token, res.user); //set local storage
                this.$router.push({ path: '/dashboard' });
            }).catch(error => {
                if (error.response.status == 401) {
                    this.invalid['error'] = error.response.data.error;
                }
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            })
        },

        /**
         * Hiển thị toast message
         * @param {*} message
         */
        showToast(message, severity = 'success') {
            this.$toast.add({ severity: severity, summary: 'Thông báo', detail: message, life: 3000 });
        },


        /**
         * Validate email
         */
        validateemail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.user.email) && this.user.email != null && String(this.user.email).trim() != '') {
                return true;
            }
            return false;
        },

        /**
         * Không cho nhập khoảng trắng
         * @param {*} event
         */
        validateSpace(event) {
            if (event.keyCode === 32) {
                event.preventDefault();
            }
        },

        handleKeyDown(event) {
            if (event.keyCode === 13) {
                this.doLogin();
            }
        }
    },

    created() {
        document.addEventListener('keydown', this.handleKeyDown);
    },

    beforeDestroy() {
        document.removeEventListener('keydown', this.handleKeyDown);
    },
    beforeCreate() {

    }

}
</script>

<style scoped>
@import url('../../../public/css/layout/Login.css');
</style>
