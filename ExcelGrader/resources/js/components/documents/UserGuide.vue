<template>
    <div class="ms-document flex1 d-flex">
        <div class="ht-page">
            <article class="ht-container">
                <header class="d-flex">
                    <h1 class="hkb-article__title flex1">Hướng dẫn thực hiện chấm thi</h1>

                    <div class="flex1 align-items-center justify-content-end ">
                        <a href="/" target="_blank" style="text-decoration: none;">
                            <Button label="Đăng nhập" icon="pi pi-arrow-up-right" outlined class="btn font-primeicons" link
                                @click="toggleUserGuide" iconPos="right">
                            </Button>
                        </a>
                    </div>
                </header>
                <div class="hkb-article__content">
                    <p><strong>Các bước thực hiện</strong></p>
                    <ol>
                        <li>
                            Đăng nhập vào hệ thống với thông tin tài khoản
                            <ol>
                                <li>
                                    <strong>Tài khoản: </strong> examgrader@gmail.com
                                </li>
                                <li>
                                    <strong>Mật khẩu: </strong> 123456
                                </li>
                            </ol>
                        </li>
                        <li>
                            Thực hiện chấm thi
                            <br>
                            <ol>
                                <li>Chọn tab <strong>Thực hiện chấm thi</strong>, chọn thông tin <strong>kì thi, ca thi,
                                        phòng thi</strong>
                                    <img src="../../../../public/assets/images/documents/step-setupexamshift.jpg" alt="">
                                </li>
                                <li>
                                    Tải <strong>file danh sách thi, danh sách bài thi</strong>
                                    <ul>
                                        <li>
                                            File danh sách thi. <a
                                                href="https://drive.google.com/drive/folders/14u0fuZadKXE4_IKADNLUJMm1osqbCK0t?usp=share_link"
                                                target="_blank">Tại đây</a>
                                        </li>
                                        <li>
                                            File bài thi. <a
                                                href="https://drive.google.com/drive/folders/14u0fuZadKXE4_IKADNLUJMm1osqbCK0t?usp=share_link"
                                                target="_blank">Tại đây</a>
                                        </li>
                                    </ul>
                                    <img src="../../../../public/assets/images/documents/uploadfile.png" alt="">
                                </li>
                                <li>Chọn <strong>Thực hiện chấm</strong>
                                    <img src="../../../../public/assets/images/documents/excuteevalutor.jpg" alt="">
                                </li>
                            </ol>
                        </li>

                    </ol>
                </div>
            </article>
        </div>
    </div>
</template>

<script>
import Divider from 'primevue/divider';
import Image from 'primevue/image';
import Dialog from 'primevue/dialog';
import Steps from 'primevue/steps';
import Button from 'primevue/button';
export default {
    name: "userguide",
    components: {
        Steps,
        Dialog,
        Image,
        Divider,
        Button
    },
    data() {
        return {
            visible: true,

            formObject: {}
        }
    },

    methods: {
        nextPage(event) {
            for (let field in event.formData) {
                this.formObject[field] = event.formData[field];
            }

            this.$router.push(this.items[event.pageIndex + 1].to);
        },
        prevPage(event) {
            this.$router.push(this.items[event.pageIndex - 1].to);
        },
        complete() {
            // this.$toast.add({ severity: 'success', summary: 'Order submitted', detail: 'Dear, ' + this.formObject.firstname + ' ' + this.formObject.lastname + ' your order completed.' });
        },

        toggleUserGuide() {
            window.localStorage.setItem('UserGuide', true);
        }
    },

}
</script>

<style scoped>
@import url('../../../../public/css/base/document.css');
</style>
