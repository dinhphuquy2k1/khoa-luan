<template>
    <div class="m-header">
        <div id="top_header" class="nav-container flex-row">
            <router-link to="/" id="top_header-left" class="flex-row">
                <div class="logo-content">
                    <div class="nav-logo">
                    </div>
                    <div class="logo-title">Excel Grader</div>
                </div>
            </router-link>
            <div class="flex1"></div>
            <div id="top_header-right" class="flex-row" v-click-outside="() => isShowActionInfo = false">
                <div class="right-btt-action right-action notify has-tooltip" v-tooltip.bottom="'Thông báo'"></div>
                <div class="right-btt-action right-action help has-tooltip" v-tooltip.bottom="'Hướng dẫn'"></div>
                <div class="right-btt-action right-action setting has-tooltip" v-tooltip.bottom="'Thiết lập hệ thống'">
                </div>
                <div class=" right-action user" @click="isShowActionInfo = !isShowActionInfo">
                    <div class="user-avatar pointer">
                        <img src="../../../../public/assets/icon/getavatar.png" alt="" class="avatar">
                    </div>
                </div>
                <ul class="user-account-menu" v-show="isShowActionInfo" @focusout="close" tabindex="0">
                    <i class="icon-align"></i>
                    <li class="apui-dropdown-item user-details" @click="isShowActionInfo = false, $router.push('/')">
                        <div class="license-hover">
                            <div class="apui-menu-user-avatar">
                                <div class="apui-user-avatar">
                                    <img src="../../../../public/assets/icon/getavatar.png" alt="" class="img-detail">
                                </div>
                            </div>
                            <div class="apui-user-info">
                                <div class="menu-fullname ellipsis">{{ auth.user.username }}</div>
                                <div class="menu-accountname">{{ auth.user.email }}</div>
                            </div>
                        </div>
                        <div class="menu-company d-flex">
                            <div class="action-icon"></div>
                            <div class="ellipsis text-center font-size-16">Excel Grader</div>
                        </div>
                    </li>
                    <li class="apui-menu-account-item apui-menu-item extend-item has-icon w-full">
                        <router-link to="user-info" class="flex-column" @click="isShowActionInfo = false"
                            style="width: calc(100% - 32px); border-radius: 6px;">
                            <button class="ms-button btn custom-button flex1">
                                <div class="icon24 icon left ic-change-password"></div>
                                <div class="text pl-0">Đổi mật khẩu</div>
                            </button>
                        </router-link>
                    </li>
                    <li class="apui-menu-account-item apui-menu-item extend-item has-icon">
                        <button class="ms-button btn custom-button">
                            <div class="icon24 icon left ic-setting-profile"></div>
                            <div class="text pl-0">Thiết lập tài khoản</div>
                        </button>
                    </li>
                    <li action="logout" class="apui-menu-item-logout has-icon">
                        <button class="ms-button btn button-logout secondary" @click="logout">
                            <div class="icon24 icon left logout"></div>
                            <div class="text pl-0">Đăng xuất</div>
                        </button>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</template>

<script>
import vClickOutside from 'click-outside-vue3'
import { logout } from '../../../api/user'
import Auth from '../../../api/utils/auth'
export default {
    emits: ["setLoading", "setUseBackgroundLoading"],
    directives: {
        clickOutside: vClickOutside.directive
    },
    data() {
        return {
            isShowActionInfo: false,
        }
    },
    methods: {
        logout() {
            this.isShowActionInfo = false;
            this.$emit("setUseBackgroundLoading");
            this.$emit("setLoading");
            Auth.logout(); //reset local storage
            setTimeout(() => {
                this.$emit("setUseBackgroundLoading");
                this.$emit("setLoading");
                this.$router.push('/');
            }, 900);
        }
    },
    mounted() {
    },
}
</script>
<style scoped>
@import url('../../../../public/css/layout/TheHeader.css');
</style>
