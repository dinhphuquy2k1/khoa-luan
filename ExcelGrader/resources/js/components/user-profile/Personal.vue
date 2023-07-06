<template>
    <div class="personal-info">
        <div class="content-page position-relative w-full">
            <div class="content-wrapper col-lg-12 col-md-12 col-sm-12 center d-flex flex-column mobile-text-14">
                <div class="change-height flex-row">
                    <div class="icon24 back flex-center mr-10 h-100 pointer" @click="$emit('setIsPersonal')"></div>
                    <span class="font-weight-bold page-title h-75 text-header font-size-20 mobile-text-18">
                        Thông tin cá nhân
                    </span>
                    <br>
                </div>
                <div class="d-flex">
                    <div class="row flex-row flex1">
                        <div class="row-content flex-column">
                            <div class="row-title flex-row">
                                <div class="flex1 text-title">Cập nhật thông tin cơ bản</div>
                            </div>
                            <div class="form-group flex-row">
                                <div class="flex1 mr-10">
                                    <div class="form-group-label d-flex label-form">
                                        Họ và tên
                                    </div>
                                    <div class="ms-input ms-editor w-100">
                                        <InputText v-model="user.username" type="text" class="ms-input-item flex1"
                                            placeholder="Họ và tên" />
                                    </div>
                                    <div class="flex1">
                                        <div class="ms-input ms-editor w-100"></div>
                                    </div>
                                </div>
                                <div class="flex1 mr-10">
                                    <div class="form-group-label d-flex label-form">
                                        Giới tính
                                    </div>
                                    <div class="ms-input ms-editor w-100">
                                        <InputText type="text" class="ms-input-item flex1" placeholder="Giới tính" />
                                    </div>
                                    <div class="flex1">
                                        <div class="ms-input ms-editor w-100"></div>
                                    </div>
                                </div>
                                <div class="flex1 mr-10">
                                    <div class="form-group-label d-flex label-form">
                                        Ngày sinh
                                    </div>
                                    <div class="ms-input ms-editor w-100">
                                        <InputText type="text" class="ms-input-item flex1" placeholder="dd/mm/yyyy" />
                                    </div>
                                    <div class="flex1">
                                        <div class="ms-input ms-editor w-100"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import InputText from 'primevue/inputtext';
export default {
    emits: ["setIsPersonal"],
    components: {
        InputText
    },

    data() {
        return {
            user: {
                username: this.auth.user.username,
                usercode: null,
            }
        }
    },

    methods: [

    ]
}
</script>

<style scoped>
@import url('../../../../public/css/components/user-profile.css');
</style>
