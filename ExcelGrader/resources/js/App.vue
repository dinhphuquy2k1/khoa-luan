<template>
    <div class="main-container" :class="{ 'collapse': isCollapse }" v-if="loggedUser">
        <router-view name="header" @setLoading="setLoading"
            @setUseBackgroundLoading="setUseBackgroundLoading"></router-view>
        <TheContent />
        <router-view name="navbar" @toggleTitle="toggleTitle" :isCollapse="isCollapse"></router-view>
    </div>
    <Toast />
    <UserGuide v-if="isUserGuide" />
    <TheLoadingProgress v-if="isLoading" :useBackground="useBackgroundLoading" />
    <Login v-if="!loggedUser" />
</template>
<script>
import Auth from '../api/utils/auth';
import TheContent from './components/layout/TheContent.vue';
import TheHeader from './components/layout/TheHeader.vue';
import TheNavbar from './components/layout/TheNavbar.vue';
import Login from './views/Login.vue';
import Toast from 'primevue/toast';
import TheLoadingProgress from './components/TheLoadingProgress.vue';
import UserGuide from './components/documents/UserGuide.vue';
export default {
    components: {
        TheContent,
        TheHeader,
        TheNavbar,
        Login,
        UserGuide,
        Toast,
        TheLoadingProgress
    },
    data() {
        return {
            isCollapse: false,
            isLoading: false,
            loggedUser: Auth.check(),
            isUserGuide: true,
            useBackgroundLoading: false,
        }
    },

    methods: {
        toggleTitle() {
            this.isCollapse = !this.isCollapse;
        },

        setLoading() {
            this.isLoading = !this.isLoading;
        },

        setUseBackgroundLoading() {
            this.useBackgroundLoading = !this.useBackgroundLoading;
        }

    },

    mounted() {
        if (window.localStorage.getItem('UserGuide') != null) {
            this.isUserGuide = false;
        }
    },
    computed: {

    }

}
</script>
