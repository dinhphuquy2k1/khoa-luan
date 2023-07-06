<template>
    <div class="main-view content-user_profile flex flex-column">
        <div class="container">
            <div class="content-page pt-0 position-relative w-full">
                <div class="content-wrapper col-lg-12 col-md-12 col-sm-12 center d-flex flex-column mobile-text-14">
                    <div class="change-height text-center">
                        <span class="font-weight-bold page-title h-75 text-header font-size-20 mobile-text-18">
                            Thông tin cá nhân
                        </span>
                        <br>
                        <span class=" h-75 font-size-16 " style="color: #212529;">Quản lý thông tin cá nhân</span>
                    </div>
                    <div class="d-flex">
                        <div class="row flex-row flex1">
                            <div class="row-content flex-column">
                                <div class="row-title flex-row">
                                    <div class="flex1 text-title">Thông tin cơ bản</div>
                                    <div class="row-link text-link" @click="setIsPersonal()">Thay đổi</div>
                                </div>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="ms-td row-hide-boder-top">Họ và tên</td>
                                            <td class="ms-td text-left row-hide-boder-top">{{ auth.user.username }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Giới tính</td>
                                            <td class="ms-td text-left ">Khác</td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Ngày sinh</td>
                                            <td class="ms-td text-left "></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="personal-info" class="d-flex mt-30">
                        <div class="row flex-row flex1">
                            <div class="row-content flex-column">
                                <div class="row-title flex-row">
                                    <div class="flex1 text-title">Thông tin liên hệ</div>
                                    <div class="row-link text-link">Thay đổi</div>
                                </div>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="ms-td row-hide-boder-top">Số điện thoại</td>
                                            <td class="ms-td text-left row-hide-boder-top"></td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Email</td>
                                            <td class="ms-td text-left font-weight-bold">{{ auth.user.email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Địa chỉ liên hệ</td>
                                            <td class="ms-td text-left "></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="personal-info" class="d-flex mt-30 mb-30">
                        <div class="row flex-row flex1">
                            <div class="row-content flex-column">
                                <div class="row-title flex-row">
                                    <div class="flex1 text-title">CMND/CCCD</div>
                                    <div class="row-link text-link">Thay đổi</div>
                                </div>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="ms-td row-hide-boder-top">Số CMND/CCCD</td>
                                            <td class="ms-td text-left row-hide-boder-top"></td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Ngày cấp</td>
                                            <td class="ms-td text-left "></td>
                                        </tr>
                                        <tr>
                                            <td class="ms-td ">Nơi cấp</td>
                                            <td class="ms-td text-left "></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <Personal v-if="isPersonal" @setIsPersonal="setIsPersonal" />
            </div>
        </div>
    </div>
</template>

<script>
import Personal from '../components/user-profile/Personal.vue'
export default {
    components: {
        Personal,
    },

    data() {
        return {
            isPersonal: false,
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

        startLoading() {
            this.isLoading = true;
        },

        endLoading() {
            setTimeout(() => {
                this.isLoading = false;
            }, 750);
        }
    },

    created() {
    }

}
</script>

<style scoped>
@import url('../../../public/css/components/user-profile.css');
</style>
