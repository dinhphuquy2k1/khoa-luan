<template>
    <div class="personal-info content-user_profile">
        <div class="content-page position-relative w-full">
            <div
                class="content-wrapper position-relative col-lg-12 col-md-12 col-sm-12 center d-flex flex-column mobile-text-14">
                <div class="change-height flex-row">
                    <div class="icon24 back flex-center mr-10 h-100 pointer" @click="$emit('setIsChangeEmail')"></div>
                    <span class="font-weight-bold page-title h-75 text-header font-size-20 mobile-text-18">
                        Email
                    </span>
                    <br>
                </div>
                <div class="d-flex">
                    <div class="row flex-row flex1">
                        <div class="row-content flex-column">
                            <div class="row-title flex-column text-center" style="line-height: 1.5;">
                                <div class="flex1 font-24">Cập nhật email</div>
                                <span style="color: #000; font-size: 16px;" class="mt-10">
                                    Nhập email bạn muốn sử dụng
                                </span>
                            </div>
                            <div class="form-item">
                                <div class="form-group validate-input">
                                    <div class="flex-column">
                                        <label style="width:100%; padding-right: 0; position: relative" class="flex1">
                                            <InputText v-model="email" class="form-control"
                                                :class="{ 'error': invalidData['email'] }" placeholder="Email" />
                                        </label>
                                        <div class="flex-row align-items-center" v-if="invalidData['email']">
                                            <div style="padding-top: 2px;" class="icon-error-text"></div>
                                            <div style="padding-left: 2px; margin-top: 0;" class="error-text">
                                                {{ invalidData['email'] }}
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
                                            <Button class="ms-button btn primary has-tooltip" @click="btnContinue">
                                                <span class="px-3 text">Tiếp tục</span>
                                            </Button>
                                            <Button class="ms-button secondary btn has-tooltip mr-10"
                                                @click="$emit('setIsChangeEmail')">
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
import TheLoadingProgress from '../TheLoadingProgress.vue';
import { checkEmailExits, sendEmailVerify, updateUser } from '../../../api/user'
export default {
    emits: ["setIsChangeEmail"],
    components: {
        InputText,
        Button,
        TheLoadingProgress
    },

    data() {
        return {
            email: null,
            isLoading: false,
            invalidData: [],
        }
    },

    methods: {
        /**
         * Validate email
         */
        validateEmail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.email) && this.email != null && String(this.email).trim() != '') {
                return true;
            }
            return false;
        },

        btnContinue() {
            this.invalidData = [];
            if (this.email == null) {
                this.invalidData['email'] = 'Email không được để trống';
                return;
            }

            if (!this.validateEmail()) {
                this.invalidData['email'] = 'Email không đúng định dạng';
            }
            //xảy ra lỗi
            if (Object.keys(this.invalidData).length > 0) return;

            if (this.validateEmail()) {
                this.isLoading = true;
                var data = { ...this.auth.user };
                data.email = this.email;
                updateUser(data).then(res => {
                    this.auth.user.email = this.email;
                    this.$emit('setIsChangeEmail');
                    this.$store.dispatch('toast/handleInternalServerSuccess', 'Cập nhật thành công');

                }).catch(error => {
                    if (error.response.status == 401) {
                    }
                    if (error.response.status == 422) {
                        for (var itemError in error.response.data.errors) {
                            this.invalidData[itemError] = error.response.data.errors[itemError][0];
                        }
                    }
                }).finally(error => {
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 750);
                });
                // checkEmailExits({ email: this.email }).then(res => {
                //     console.log(res);
                // }).catch(error => {
                //     if (error.response.status == 401) {
                //         this.invalidData['email'] = 'Email đã tồn tại';
                //     }
                // }).finally(() => {
                //     setTimeout(() => {
                //         this.isLoading = false;
                //     }, 750);
                // });
            }
        },

        /**
         * bắt sự kiện nhấn phím
         * @param {*} event
         */
        handleKeyDown(event) {
            if (event.keyCode === 13) {
                this.btnContinue();
            }
        }
    },

    created() {
        document.addEventListener('keydown', this.handleKeyDown);
    },

    beforeDestroy() {
        document.removeEventListener('keydown', this.handleKeyDown);
    },
}
</script>

<style scoped>
@import url('../../../../public/css/components/user-profile.css');
</style>
