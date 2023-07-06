<template>
    <div class="m-navbar">
        <div class="container">
            <div class="left-container">
                <div class="menu-container">
                    <div class="menu-item-container">
                        <router-link to="/dashboard" class="router-link">
                            <div class="menu-item-admin dashboard">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Tổng quan</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Tổng quan
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <router-link to="/exam-list" class="router-link">
                            <div class="menu-item-admin category">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Ngân hàng đề thi</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Ngân hàng đề thi
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <router-link to="/department-list" class="router-link">
                            <div class="menu-item-admin report">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Danh sách phòng thi</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Danh sách phòng thi
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <router-link to="/excel-manager" class="router-link">
                            <div class="menu-item-admin movement">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Danh sách kì thi</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Danh sách kì thi
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <router-link to="/excel-grader" class="router-link">
                            <div class="menu-item-admin decision">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Thực hiện chấm thi</div>
                                    <!-- <div class="arrow-right" :class="{ 'hide': isCollapse }"></div> -->
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Thực hiện chấm thi
                                    </div>
                                </div>
                            </div>
                        </router-link>
                        <router-link to="/user-manager" class="router-link">
                            <div class="menu-item-admin profile">
                                <div class="menu-item-admin-container">
                                    <div class="menu-item-title">Quản lý tài khoản</div>
                                    <div class="menu-item-tooltip" :class="{ 'show': isCollapse }">
                                        Quản lý tài khoản
                                    </div>
                                </div>
                            </div>
                        </router-link>
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

<style scoped>
@import url('../../../../public/css/layout/TheNavbar.css');
</style>
