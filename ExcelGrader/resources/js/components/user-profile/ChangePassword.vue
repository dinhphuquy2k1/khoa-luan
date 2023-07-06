<template>
    <div class="personal-info content-user_profile">
        <div class="content-page position-relative w-full">
            <div class="content-wrapper col-lg-12 col-md-12 col-sm-12 center d-flex flex-column mobile-text-14">
                <div class="change-height flex-row">
                    <div class="icon24 back flex-center mr-10 h-100 pointer" @click="$emit('setIsChangePassword')"></div>
                    <span class="font-weight-bold page-title h-75 text-header font-size-20 mobile-text-18">
                        Mật khẩu
                    </span>
                    <br>
                </div>
                <div class="d-flex">
                    <div class="row flex-row flex1">
                        <div class="row-content flex-column">
                            <div class="row-title flex-column text-center" style="line-height: 1.5;">
                                <div class="flex1 font-24">Thay đổi mật khẩu</div>
                                <span style="color: #000; font-size: 16px;" class="mt-10">Nhập mật khẩu có
                                    tối
                                    thiểu 8 ký tự
                                    bao gồm số, chữ
                                    <br> hoa, chữ
                                    thường.</span>
                            </div>
                            <div class="form-item">
                                <div class="form-group validate-input">
                                    <div class="form-group-label d-flex">
                                        Mật khẩu hiện tại
                                    </div>
                                    <div class="flex-column">
                                        <label style="width:100%; padding-right: 0; position: relative" class="flex1">
                                            <InputText v-model="currentPassword" @keypress="validateSpace"
                                                :type="isShowPassword ? 'text' : 'password'"
                                                :class="{ 'error': invalidData['currentPassword'] }"
                                                placeholder="Mật khẩu hiện tại" />
                                            <div id="togglepassword" class="eye" :class="{ 'eye-slash': isShowPassword }"
                                                style="top: 14px; right: 12px;"></div>
                                        </label>
                                        <div class="flex-row align-items-center" v-if="invalidData['currentPassword']">
                                            <div style="padding-top: 2px;" class="icon-error-text"></div>
                                            <div style="padding-left: 2px; margin-top: 0;" class="error-text">
                                                {{ invalidData['currentPassword'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-item">
                                <div class="form-group validate-input">
                                    <div class="form-group-label d-flex">
                                        Mật khẩu mới
                                    </div>
                                    <div class="flex-column">
                                        <label style="width:100%; padding-right: 0; position: relative;"
                                            class="flex-column">
                                            <Password v-model="newPassword" toggleMask class="flex1"
                                                :class="{ 'error': invalidData['newPassword'] }" @keypress="validateSpace"
                                                placeholder="Mật khẩu mới" />
                                        </label>
                                        <div v-if="invalidData['newPassword']">
                                            <div class=" flex-row align-items-center"
                                                v-for="item in invalidData['newPassword']">
                                                <div style="padding-top: 2px;" class="icon-error-text"></div>
                                                <div style="padding-left: 2px; margin-top: 0;" class="error-text">
                                                    {{ item }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-item">
                                <div class="form-group validate-input">
                                    <div class="form-group-label d-flex">
                                        Xác nhận mật khẩu mới
                                    </div>
                                    <div class="flex-column">
                                        <label style="width:100%; padding-right: 0; position: relative" class="flex1">
                                            <InputText v-model="confirmPassword" @keypress="validateSpace"
                                                :type="isShowPassword ? 'text' : 'password'"
                                                :class="{ 'error': invalidData['confirmPassword'] }"
                                                placeholder="Xác nhận mật khẩu mới" />
                                            <div id="togglepassword" class="eye" :class="{ 'eye-slash': isShowPassword }"
                                                style="top: 14px; right: 12px;"></div>
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
                            <div class="d-flex justify-content-center">
                                <div class="popup-footer row-hide-boder-top mb-30 pr-0"
                                    style="max-width: 392px; width: 392px;">
                                    <div class="flex-row">
                                        <div class="flex1"></div>
                                        <div class="flex-rtl">
                                            <Button class="ms-button btn primary has-tooltip" @click="btnChangePassword">
                                                <span class="px-3 text">Đổi mật khẩu</span>
                                            </Button>
                                            <Button class="ms-button secondary btn has-tooltip mr-10">
                                                <span class="px-3 text">Hủy</span>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <TheLoadingProgress v-if="isLoading" />
            </div>
        </div>
    </div>
</template>

<script>
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { checkPassword } from '../../../api/user'
import TheLoadingProgress from '../TheLoadingProgress.vue';
import Password from 'primevue/password';
export default {
    emits: ["setIsChangePassword"],
    components: {
        InputText,
        Button,
        Password,
        TheLoadingProgress
    },

    data() {
        return {
            currentPassword: null,  //mật khẩu hiện tại
            newPassword: null,
            confirmPassword: null,
            isShowPassword: false,
            invalidData: [],
            isLoading: false,
        }
    },

    methods: {

        /**
         * Click đổi mật khẩu
         */
        btnChangePassword() {
            this.invalidData = [];
            if (this.validateData()) {

            }

            console.log(this.invalidData);
        },

        /**
         * Kiểm tra xem chuỗi có chứa chữ số không
         */
        validateContainsNumber(string) {
            if (/\d+/.test(string)) {
                return true;
            }
            return false;
        },

        /**
         * Kiểm tra chứa chữ hoa
         */
        validateContainsUppercase(string) {
            if (/[A-Z]/.test(string)) {
                return true;
            }
            return false;
        },

        /**
         * Kiểm tra chứa chữ thường
         * @param {*} string
         */
        validateContainsLowecase(string) {
            if (/[a-z]/.test(string)) {
                return true;
            }
            return false;
        },

        /**
         * Validate dữ liệu
         */
        validateData() {
            var valid = true;
            // this.invalidData['newPassword'] = [];
            if (this.currentPassword == null || this.currentPassword == '') {
                this.invalidData['currentPassword'] = 'Nhập mật khẩu hiện tại.';
                valid = false;
            }

            if (this.newPassword == null || this.newPassword == '') {
                this.invalidData['newPassword'] = ['Có ít nhất 8 ký tự', 'Có chữ thường (a-z) và chữ in hoa (A-Z)', 'Có ít nhất một chữ số (0-9)'];
                valid = false;
            }

            if (this.confirmPassword == null || this.confirmPassword == '') {
                this.invalidData['confirmPassword'] = 'Nhập xác nhận mật khẩu';
                valid = false;
            }

            if (valid == false) return valid;

            if (this.newPassword.length < 8) {
                this.invalidData['newPassword'].push('Có ít nhất 8 ký tự');
                valid = false;
            }

            if (!this.validateContainsNumber(this.newPassword)) {
                this.invalidData['newPassword'].push('Có ít nhất một chữ số (0-9)');
                valid = false;
            }

            if (!this.validateContainsUppercase(this.newPassword)) {
                this.invalidData['newPassword'].push('Có chữ thường (a-z) và chữ in hoa (A-Z)');
                valid = false;
            }

            if (!this.validateContainsLowecase(this.newPassword)) {
                this.invalidData['newPassword'].push('Có chữ thường (a-z) và chữ in hoa (A-Z)');
                valid = false;
            }

            if (this.newPassword != this.confirmPassword) {
                this.invalidData['confirmPassword'] = 'Mật khẩu mới và xác nhận mật khẩu không trùng nhau.';
                valid = false;
            }

            return valid;
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
