<template>
    <div class="m-navbar ms-navbar_profile">
        <div class="container">
            <div class="left-container mt-10">
                <div class="menu-container">
                    <div class="menu-item-container">
                        <router-link to="/user-info" class="router-link">
                            <div class="menu-item-admin profile mb-0">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Thông tin cá nhân</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Thông tin cá nhân
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <div class="sub-menu list-group">
                            <ul class="sub-menu-ul">
                                <li class="list-group-item">
                                    <div class="text-dark-admin">Thông tin cơ bản</div>
                                </li>
                                <li class="list-group-item">
                                    <div class="text-dark-admin">Thông tin liên hệ</div>
                                </li>
                                <li class="list-group-item">
                                    <div class="text-dark-admin">CMND/CCCD</div>
                                </li>
                            </ul>
                        </div>
                        <router-link to="/user-protect" class="router-link">
                            <div class="menu-item-admin protect">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Bảo vệ tài khoản</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Bảo vệ tài khoản
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <div class="sub-menu list-group">
                            <ul class="sub-menu-ul">
                                <li class="list-group-item">
                                    <div class="text-dark-admin">Thông tin đăng nhập</div>
                                </li>
                                <li class="list-group-item">
                                    <div class="text-dark-admin">Nhật kí đăng nhập</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toggle-button pointer show" :class="{ 'hiden': isCollapse }" @click="toggleTitle">
                <span class="toggle-title" v-if="!isCollapse">Thu gọn</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isCollapse: {
            type: Boolean,
            default: false,
        }
    },
    data() {
        return {
            isActiveTitle: false,
        }
    },
    methods: {
        toggleTitle() {
            this.$emit("toggleTitle");
        }
    }
}
</script>
