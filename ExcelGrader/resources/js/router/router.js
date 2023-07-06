import { createRouter, createWebHistory } from 'vue-router'
import Auth from '../../api/utils/auth';
const routes = [
    {
        path: '/',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: {
            title: 'Đăng nhập'
        }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        components: {
            content: () => import('../components/Dashboard.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Tổng quan'
        }
    },
    {
        path: '/exam-list',
        name: 'exam-list',
        components: {
            content: () => import('../views/ExamList.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Quản lý đề thi'
        }
    },
    {
        path: '/userguide',
        name: 'userguide',
        components: {
            UserGuide: () => import('../components/documents/UserGuide.vue'),
        }
    },
    {
        path: '/department-list',
        name: 'department',
        components: {
            content: () => import('../views/DepartmentList.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Quản lý phòng thi'
        }
    },
    {
        path: '/exam-setup',
        name: 'exam-setup',
        components: {
            content: () => import('../components/exam-bank/ExamSetup.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Thiết lập đề thi'
        }
    },
    {
        path: '/user-manager',
        name: 'user-manager',
        components: {
            content: () => import('../views/UserList.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Quản lý người dùng'
        }
    },
    {
        path: '/excel-grader',
        name: 'excel-grader',
        components: {
            content: () => import('../components/excel-grader/ExamEvaluator.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Thực hiện chấm thi'
        }
    },
    {
        path: '/excel-manager',
        name: 'excel-manager',
        components: {
            content: () => import('../views/ExamManager.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/layout/TheNavbar.vue')
        },
        meta: {
            auth: true,
            title: 'Quản lý kì thi'
        }
    },
    {
        path: '/user-info',
        name: 'user-info',
        components: {
            content: () => import('../views/UserProfile.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/user-profile/Navbar.vue')
        },
        meta: {
            auth: true,
            title: 'Thông tin tài khoản'
        }
    },

    {
        path: '/user-protect',
        name: 'user-protect',
        components: {
            content: () => import('../components/user-profile/Security.vue'),
            header: () => import('../components/layout/TheHeader.vue'),
            navbar: () => import('../components/user-profile/Navbar.vue')
        },
        meta: {
            auth: true,
            title: 'Thông tin tài khoản'
        }
    },
];


const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
})

router.beforeEach((to, from, next) => {
    document.title = to.meta.title;
    if (to.matched.some(record => record.meta.auth)) {
        if (Auth.check()) {
            next();
            return;
        } else {
            router.push('/');
        }
    }
    else {
        if (Auth.check()) {
            next('/dashboard');
            return;
        } else {
            next();
        }
    }
});

export default router
