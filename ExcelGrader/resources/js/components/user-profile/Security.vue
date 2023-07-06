<template>
    <div class="main-view flex content-user_profile flex-column position-relative">
        <div class="container">
            <div class="content-page pt-0 position-relative w-full">
                <div class="content-wrapper col-lg-12 col-md-12 col-sm-12 mb-30 center d-flex flex-column mobile-text-14">
                    <div class="change-height text-center">
                        <span class="font-weight-bold page-title h-75 text-header font-size-20 mobile-text-18">
                            Bảo vệ tài khoản
                        </span>
                        <br>
                        <span class=" h-75 font-size-16 " style="color: #212529;">Chức năng hỗ trợ bảo vệ tài khoản</span>
                    </div>
                    <div class="d-flex mb-30">
                        <div class="row flex-row flex1">
                            <div class="row-content flex-column">
                                <div class="row-title flex-row">
                                    <div class="flex1 text-title">Thông tin đăng nhập</div>
                                </div>
                                <table class="table">
                                    <tbody>
                                        <tr class="row-hide-boder-top">
                                            <td class="ms-td">Số điện thoại</td>
                                            <td class="ms-td text-left"></td>
                                            <td class="ms-td text-left">
                                                <div class="row-link text-link">Thay đổi</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Email</td>
                                            <td class="ms-td text-left ">{{ auth.user.email }}</td>
                                            <td class="ms-td text-left">
                                                <div class="row-link text-link" @click="dialogPassword = true">Thay đổi
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Mật khẩu</td>
                                            <td class="ms-td text-left ">*******</td>
                                            <td class="ms-td text-left" @click="setIsChangePassword()">
                                                <div class="row-link text-link">Thay đổi</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mb-30">
                        <div class="row flex-row flex1">
                            <div class="row-content flex-column">
                                <div class="row-title flex-row">
                                    <div class="flex1 text-title">Nhật kí hoạt động</div>
                                </div>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="ms-td row-hide-boder-top" style="width: 35%;">
                                                <div class="flex-column">
                                                    <span class="font-weight-medium font-size-16"
                                                        style="    color: #020a1c;"> Đăng nhập thành
                                                        công</span>
                                                    <span class="mt-10">13:50, 17 Tháng 4 2023</span>
                                                </div>
                                            </td>
                                            <td class="ms-td text-left row-hide-boder-top text-grey " style="width: 40%;">
                                                Windows 10 • Desktop
                                            </td>
                                            <td class="ms-td text-left row-hide-boder-top text-grey">IP: 210.245.2.226</td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td " style="width: 35%;">
                                                <div class="flex-column">
                                                    <span class="font-weight-medium font-size-16"
                                                        style="    color: #020a1c;"> Đăng nhập thành
                                                        công</span>
                                                    <span class="mt-10">13:50, 17 Tháng 4 2023</span>
                                                </div>
                                            </td>
                                            <td class="ms-td text-left text-grey" style="width: 40%;">
                                                Windows 10 • Desktop
                                            </td>
                                            <td class="ms-td text-left text-grey">IP: 210.245.2.226</td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td " style="width: 35%;">
                                                <div class="flex-column">
                                                    <span class="font-weight-medium font-size-16"
                                                        style="    color: #020a1c;"> Đăng nhập thành
                                                        công</span>
                                                    <span class="mt-10">13:50, 17 Tháng 4 2023</span>
                                                </div>
                                            </td>
                                            <td class="ms-td text-left text-grey" style="width: 40%;">
                                                Windows 10 • Desktop
                                            </td>
                                            <td class="ms-td text-left  text-grey">IP: 210.245.2.226</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ChangePassword v-if="isChangePassword" @setIsChangePassword="setIsChangePassword" />
        <ChangeEmail v-if="isChangeEmail" @setIsChangeEmail="setIsChangeEmail" />
        <Dialog v-model:visible="dialogPassword" @keydown.enter.prevent="btnContinue()" modal :style="{ width: '400px' }"
            :draggable="true" :closable="false" class="p-dialog-header-hidden" style="border-radius: 12px !important;">
            <TheLoadingProgress v-if="isLoading" />
            <div>
                <div class="flex-column mt-10">
                    <div class="rounded-circle img-ava d-flex align-items-center justify-content-center mb-3">
                        <img src="../../../../public/assets/icon/getavatar.png" alt="" class="img-detail">
                    </div>
                    <div class="user-name font-size-20 text-center mt-10" style="color: #000;">
                        {{ auth.user.username }}
                    </div>
                    <div class="text-center mt-10">{{ auth.user.email }}</div>
                    <div class="text-center font-size-16 mt-10">Nhập mật khẩu để tiếp tục</div>
                    <div class="form-item mt-10">
                        <div class="form-group validate-input">
                            <div class="flex-column">
                                <label style="width:100%; padding-right: 0; position: relative">
                                    <InputText v-model="txtPassword" :type="isShowPassword ? 'text' : 'password'"
                                        :class="{ 'error': invalidData['confirmPassword'] }" class="form-control"
                                        placeholder="Mật khẩu" @keypress="validateSpace" />
                                    <div id="togglepassword" class="eye" @click="isShowPassword = !isShowPassword"
                                        :class="{ 'eye-slash': isShowPassword }" style="top: 16px;"></div>
                                </label>
                                <div class=" flex-row align-items-center" v-if="invalidData['confirmPassword']">
                                    <div style="padding-top: 2px;" class="icon-error-text"></div>
                                    <div style="padding-left: 2px; margin-top: 0;" class="error-text">
                                        {{ invalidData['confirmPassword'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Đóng" class="ms-button btn detail-button secondary" @click="dialogPassword = false" />
                <Button class="ms-button btn primary has-tooltip" @click="btnContinue">
                    <span class="px-3 text">Tiếp tục</span>
                </Button>
            </template>
        </Dialog>
    </div>
</template>

<script>
import TheLoadingProgress from '../TheLoadingProgress.vue';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import ChangePassword from './ChangePassword.vue';
import ChangeEmail from './ChangeEmail.vue'
import { checkPassword } from '../../../api/user'
export default {
    components: {
        ChangePassword,
        Dialog,
        InputText,
        ChangeEmail,
        TheLoadingProgress
    },

    data() {
        return {
            isPersonal: false,
            isChangePassword: false,
            dialogPassword: false,
            isShowPassword: false,
            isChangeEmail: false,
            txtPassword: null,
            invalidData: [],
            isLoading: false,
        }
    },

    methods: {

        /**
         * Ẩn hiển form thông tin cơ bản
         */
        setIsPersonal() {
            this.isPersonal = !this.isPersonal;
        },

        /**
         * Ẩn/hiện form thay đổi password
         */
        setIsChangePassword() {
            this.isChangePassword = !this.isChangePassword;
        },

        /**
         * Ẩn/hiện form thay đổi email
         */
        setIsChangeEmail() {
            this.isChangeEmail = !this.isChangeEmail;
        },

        /**
         * Click nút tiếp tục form xác nhận mật khẩu
         */
        btnContinue() {
            this.isLoading = true;
            this.invalidData = [];
            checkPassword({ email: this.auth.user.email, passwordCheck: this.txtPassword }).then(res => {
                this.dialogPassword = false;
                this.isChangeEmail = true;
            }).catch(error => {
                this.invalidData['confirmPassword'] = 'Mật khẩu không chính xác';
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            });
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
    }
}
</script>

<style scoped>
@import url('../../../../public/css/components/user-profile.css');
</style>
