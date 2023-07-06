<template>
    <div class="main-view flex1 flex-column position-relative">
        <div class="form-list flex-column flex1">
            <div class="flex-column flex1">
                <div class="flex-row title-box">
                    <div class="list-title flex1">Quản lý tài khoản</div>
                </div>
                <div class="flex-row toolbar-box">
                    <div class="left-toolbar flex-row">
                        <div class="ms-input ms-editor w-100 search-box mr-2">
                            <div class="flex-row border flex1">
                                <input type="text" class="ms-input-item flex1" placeholder="Tìm kiếm...">
                                <div class="icon24 error error-icon" style="display: none;"></div>
                                <div class="icon24 icon search-right search"></div>
                            </div>
                        </div>
                    </div>
                    <div class="right-toolbar flex-row">
                        <button class="ms-button btn primary" @click="selectedUsers = {}, dialogVisible = true">
                            <div class="icon24 icon left add-white"></div>
                            <div class="text pl-0">Thêm tài khoản</div>
                        </button>
                    </div>
                </div>
                <div class="box list-content flex1 flex-row">
                    <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                        :loading="isLoading" :class="{ 'loading': isLoading }" sortField="username" :sortOrder="-1"
                        :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.exam })) : users"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}"
                        paginatorTemplate="FirstPageLink PrevPageLink flex1 CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                        @rowDblclick="onRowSelect($event.data)" tableStyle="min-width: 100%" rowHover>
                        <template #paginatorstart>
                            <Button type="button" icon="pi pi-refresh" text />
                        </template>
                        <template #paginatorend>
                            bản ghi/trang
                        </template>
                        <Column header="STT" style="width: 100px;" class="text-center">
                            <template #body="slotProps">
                                <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="usercode" sortable style="width: 20vw;" header="Mã tài khoản">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="username" sortable style="width: 20vw;" header="Họ và tên">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="email" sortable dataKey="id" header="Email">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="level" sortable dataKey="id" header="Loại tài khoản">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] == 4 ? 'Chấm thi' : data[field] == 3 ? 'Tạo đề' :
                                    data[field] == 2 ? 'Tạo đề và chấm thi' : 'Chấm thi'
                                }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column alignFrozen="right" style="width: 100px; text-align: center;" frozen header="Thao tác">
                            <template #body="slotProps">
                                <div class="row-actions flex-row" v-if="!isLoading">
                                    <div class="item" @click="onRowSelect(slotProps.data)">
                                        <div class="v-popover popover">
                                            <div class="trigger">
                                                <div class="icon24 edit"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item" @click="deleteRowSelect(slotProps.data)">
                                        <div class="v-popover popover">
                                            <div class="icon24 delete"></div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>

                    </DataTable>
                </div>
            </div>
        </div>
        <Dialog v-model:visible="dialogVisible" @keydown.enter.prevent="doSave" modal
            :header="modeModal == FormMode.Insert ? 'Thêm tài khoản' : 'Sửa tài khoản'" :style="{ width: '50vw' }"
            closeOnEscape>
            <TheLoadingProgress v-if="isLoadingComponent" />
            <div class="w-full flex flex-column">
                <div class="form-group flex-row">
                    <div class="flex1 mr-10">
                        <div class="form-group-label d-flex label-form">
                            Họ và tên
                            <span class="required">*</span>
                        </div>
                        <div class="ms-input ms-editor w-100">
                            <InputText v-model="selectedUsers.username" type="text" class="ms-input-item flex1"
                                :class="{ 'error': invalidData['username'] }" placeholder="Nhập họ tên"
                                @input="handlerGenerateCode" />
                            <div class="error-text" v-if="invalidData['username']">
                                {{ invalidData['username'] }}
                            </div>
                        </div>
                        <div class="flex1">
                            <div class="ms-input ms-editor w-100"></div>
                        </div>
                    </div>
                    <div class="flex1 mr-10">
                        <div class="form-group-label d-flex label-form">
                            Mã tài khoản
                            <span class="required">*</span>
                        </div>
                        <div class="ms-input ms-editor w-100">
                            <InputText v-model="selectedUsers.usercode" type="text" class="ms-input-item flex1"
                                :class="{ 'error': invalidData['usercode'] }" placeholder="Nhập mã tài khoản"
                                @keypress="handlerInputCode"
                                @input="modeGenerate = selectedUsers.usercode ? false : true;" />
                            <div class="error-text" v-if="invalidData['usercode']">
                                {{ invalidData['usercode'] }}
                            </div>
                        </div>
                        <div class="flex1">
                            <div class="ms-input ms-editor w-100"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group flex-row">
                    <div class="flex1 mr-10">
                        <div class="form-group-label d-flex label-form">
                            email
                            <span class="required">*</span>
                        </div>
                        <div class="ms-input ms-editor w-100">
                            <InputText v-model="selectedUsers.email" type="text" class="ms-input-item flex1"
                                :class="{ 'error': invalidData['email'] }" placeholder="Nhập email"
                                @keypress="validateSpace" />
                            <div class="error-text" v-if="invalidData['email']">
                                {{ invalidData['email'] }}
                            </div>
                        </div>
                        <div class="flex1">
                            <div class="ms-input ms-editor w-100"></div>
                        </div>
                    </div>
                    <div class="flex1 mr-10">
                        <div class="form-group-label d-flex label-form">
                            Loại tài khoản
                            <span class="required">*</span>
                        </div>
                        <div class="ms-input ms-editor w-100">
                            <div class="flex1">
                                <Dropdown v-model="selectedUsers.level" optionLabel="description" optionValue="value"
                                    :class="{ 'error': invalidData['level'] }" :options="levelOptions" showClear
                                    placeholder="Select a Sheet" />
                            </div>
                            <div class="error-text" v-if="invalidData['level']">
                                {{ invalidData['level'] }}
                            </div>
                        </div>
                        <div class="flex1">
                            <div class="ms-input ms-editor w-100"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group flex-row">
                    <div class="flex1 mr-10">
                        <div class="form-group-label d-flex label-form">
                            Ghi chú
                        </div>
                        <div class="ms-input ms-editor w-100">
                            <Textarea v-model="selectedUsers.note" autoResize rows="5" cols="30"
                                placeholder="Nhập ghi chú" />
                        </div>
                        <div class="flex1">
                            <div class="ms-input ms-editor w-100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="d-flex flex-row">
                    <div class="flex1"></div>
                    <Button label="Đóng" class="ms-button btn detail-button secondary" @click="dialogVisible = false" />
                    <Button label="Lưu" :disabled="isDisable" class="ms-button btn detail-button primary" @click="doSave"
                        @keyup.enter="doSave" />
                </div>

            </template>
        </Dialog>
        <Toast />
        <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
            header="Xóa tài khoản">
            <div class="w-full flex flex-column">
                <span> Bạn có chắc chắn muốn xóa tài khoản <b>{{ selectedUsers.usercode }}</b></span>
            </div>
            <template #footer>
                <Button label="Không" class="ms-button btn detail-button secondary" @click="isPopupDelete = false" />
                <Button label="Xóa tài khoản" class="ms-button btn w-100 danger" @click="handlerDelete" />
            </template>
        </Dialog>
    </div>
</template>

<script>
import { generateCode } from '../common/Functions';
import Toast from 'primevue/toast';
import Textarea from 'primevue/textarea';
import ExamManagerPopup from '../components/exam-manager/ExamManagerPopup.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import TheLoadingProgress from '../components/TheLoadingProgress.vue';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import { getUsers, saveUser, updateUser, deleteUser } from '../../api/user';
export default {
    components: {
        DataTable,
        Column,
        Dialog,
        Textarea,
        Button,
        Calendar,
        InputText,
        Dropdown,
        Toast,
        TheLoadingProgress,
        Skeleton,
        ExamManagerPopup,
    },
    data() {
        return {
            dialogVisible: false,
            columns: [
                { field: 'username', header: 'Họ và tên' },
                { field: 'usercode', header: 'Mã user' },
                { field: 'email', header: 'email' },
                { field: 'level', header: 'Loại tài khoản' },
                { field: 'note', header: 'Ghi chú' },
            ],

            exam: {},
            levelOptions: [
                {
                    description: 'Chấm thi',
                    value: 1,
                },
                {
                    description: 'Tạo đề',
                    value: 2,
                },
                {
                    description: 'Tạo đề và chấm thi',
                    value: 3,
                },
            ],

            isLoadingComponent: false,
            isPopupDelete: false,

            isLoading: true,
            isDisable: false,
            selectedUsers: {
                username: null,
                usercode: null,
                email: null,
                note: null,
                level: null,
            },



            modeModal: this.FormMode.Insert,
            users: [],
            modeGenerate: true, //cho phép sinh mã theo tên

            invalidData: [],

        }
    },

    methods: {

        /**
        * Xử lý hàm sinh mã theo tên
        */
        handlerGenerateCode() {
            if (this.modeGenerate)
                this.selectedUsers.usercode = generateCode(this.selectedUsers.username);
        },

        /**
       * Hiển thị toast message
       * @param {*} message
       */
        showToast(message, severity = 'success') {
            this.$toast.add({ severity: severity, summary: 'Thông báo', detail: message, life: 3000 });
        },

        doSave() {
            try {
                this.isLoadingComponent = true;
                this.isDisable = true;
                if (this.validateData()) {
                    if (this.modeModal === this.FormMode.Insert) {
                        saveUser(this.selectedUsers).then(res => {
                            this.loadUsers();
                            this.showToast("Thêm tài khoản thành công");
                            this.dialogVisible = false;
                            this.modeModal = this.FormMode.Insert;
                            this.selectedUsers = {
                                username: null,
                                usercode: null,
                                email: null,
                                note: null,
                                level: null,
                            };
                        }).catch(error => {
                            this.isDisable = false;
                            if (error.response.status == 422) {
                                for (var itemError in error.response.data.errors) {
                                    console.log(error.response.data.errors);
                                    this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                }
                            }
                        })
                    }
                    else if (this.modeModal === this.FormMode.Update) {
                        updateUser(this.selectedUsers).then(res => {
                            this.loadUsers();
                            this.showToast("Thêm tài khoản thành công");
                            this.dialogVisible = false;
                            this.modeModal = this.FormMode.Insert;
                            this.selectedUsers = {
                                username: null,
                                usercode: null,
                                email: null,
                                note: null,
                                level: null,
                            };
                        }).catch(error => {
                            console.log(error);
                        });
                    }
                }
                this.isDisable = false;

                setTimeout(() => {
                    this.isLoadingComponent = false;
                }, 750);
            } catch (error) {
                this.isDisable = false;
                console.log(error);
                this.showToast("Đã xảy ra lỗi", 'error');
            }
        },

        /**
        * Lấy thông tin bản ghi
        * @param {*} data
        */
        onRowSelect(data) {
            this.modeModal = this.FormMode.Update;
            this.selectedUsers = { ...data };
            this.dialogVisible = true;
        },

        /**
        * Xóa phòng thi
        * @param {*} data
        */
        deleteRowSelect(data) {
            this.isPopupDelete = true;
            this.selectedUsers = data;
        },

        /**
         * Click nút xóa phòng thi
         */
        handlerDelete() {
            deleteUser(this.selectedUsers).then(res => {
                this.isPopupDelete = false;
                this.showToast('Xóa thành công');
                this.loadUsers();
            }).catch(error => {
                console.log(error);
            })
        },

        /**
         * Validate dữ liệu
         */
        validateData() {
            var invalid = true;
            this.invalidData = [];
            if (this.selectedUsers.username == null || String(this.selectedUsers.username).trim() == '') {
                this.invalidData['username'] = 'Tên tài khoản không được để trống';
                invalid = false;
            }

            if (this.selectedUsers.usercode == null || String(this.selectedUsers.usercode).trim() == '') {
                this.invalidData['usercode'] = 'Mã tài khoản không được để trống';
                invalid = false;
            }

            if (this.selectedUsers.email != null && !this.validateemail()) {
                this.invalidData['email'] = 'email không đúng định dạng';
                invalid = false;
            }

            if (this.selectedUsers.email == null) {
                this.invalidData['email'] = 'email không được để trống';
                invalid = false;
            }

            if (this.selectedUsers.level == null) {
                this.invalidData['level'] = 'Loại tài khoản không được để trống';
                invalid = false;
            }
            return invalid;
        },

        /**
         * Validate email
         */
        validateemail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.selectedUsers.email) && this.selectedUsers.email != null && String(this.selectedUsers.email).trim() != '') {
                return true;
            }
            return false;
        },

        /**
       * Validate mã phòng thi
       * Ko cho phép nhập các kí tự đặc biệt
       * @param {*} event
       */
        handlerInputDepartmentCode(event) {
            let pattern = /[\W_]/g;
            let res = event.key.match(pattern);
            if (res) {
                event.preventDefault();
            }
        },

        /**
        * Validate mã phòng thi
        * Ko cho phép nhập các kí tự đặc biệt
        * @param {*} event
        */
        handlerInputCode(event) {
            let pattern = /[\W_]/g;
            let res = event.key.match(pattern);
            if (res) {
                event.preventDefault();
            }
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

        /**
         * Lấy danh sách user
         */
        async loadUsers() {
            this.isLoading = true;
            await getUsers().then(res => {
                this.users = res;
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false
                }, 500);
            })
        },
    },

    created() {
        this.loadUsers();
    },

}
</script>

<style scoped>
@import url('../../../public/css/components/exam-manager.css');
</style>
