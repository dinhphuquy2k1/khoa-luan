<template>
    <div class="main-view flex1 flex-column">
        <div class="form-list flex-column flex1">
            <div class="flex-column flex1">
                <div class="flex-row title-box">
                    <div class="list-title flex1">Danh sách phòng thi</div>
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
                        <button class="ms-button btn primary" @click="btnInsertDepartment">
                            <div class="icon24 icon left add-white"></div>
                            <div class="text pl-0">Thêm phòng thi</div>
                        </button>
                    </div>
                </div>
                <div class="box list-content flex1 flex-row">
                    <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                        :class="{ 'loading': isLoading }" :loading="isLoading"
                        :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : data"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}"
                        paginatorTemplate="FirstPageLink PrevPageLink flex1 CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                        @rowDblclick="onRowSelect($event.data)" tableStyle="min-width: 100%" rowHover>
                        <template #paginatorstart>
                            <Button type="button" icon="pi pi-refresh" text />
                        </template>
                        <template #paginatorend>
                            bản ghi/trang
                        </template>
                        <template #empty>
                            <div>
                                Không có dữ liệu
                            </div>
                        </template>
                        <Column header="STT" style="width: 100px;" class="text-center">
                            <template #body="slotProps">
                                <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="DepartmentCode" style="width: 30vw;" header="Mã phòng thi">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="DepartmentName" dataKey="id" header="Tên phòng thi">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column dataKey="id" header="Trạng thái" style="width: 200px;">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading">
                                    <div class="d-flex status-ctn max-content" v-if="data['is_exist']"
                                        style="background-color: rgb(229, 250, 237);">
                                        <div class="status-dot" style="background-color: rgb(0, 200, 83);"></div>
                                        <div class="status-text" style="color: rgb(0, 200, 83);">Đang sử dụng</div>
                                    </div>
                                    <div class="d-flex status-ctn max-content" v-else
                                        style="background-color: rgb(254, 243, 231);">
                                        <div class="status-dot" style="background-color: rgb(243, 141, 21);"></div>
                                        <div class="status-text" style="color: rgb(243, 141, 21);">Không sử dụng</div>
                                    </div>
                                </div>
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
    </div>
    <Dialog v-model:visible="departmentPopupVisible" @keydown.enter.prevent="doSave" modal
        :header="modePopup == FormMode.Insert ? 'Thêm sửa thi' : 'Sửa đề thi'" :style="{ width: '30vw' }" closeOnEscape>
        <div class="w-full flex flex-column">
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Tên phòng thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <InputText v-model="department.DepartmentName" type="text" class="ms-input-item flex1"
                            :class="{ 'error': invalidData['DepartmentName'] }" placeholder="Nhập tên phòng thi"
                            @input="handlerGenerateCode" />
                        <div class="error-text" v-if="invalidData['DepartmentName']">
                            {{ invalidData['DepartmentName'] }}
                        </div>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Mã phòng thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <InputText v-model="department.DepartmentCode" type="text" class="ms-input-item flex1"
                            :class="{ 'error': invalidData['DepartmentName'] }" placeholder="Nhập mã phòng thi"
                            @keypress="handlerInputDepartmentCode"
                            @input="modeGenerate = department.DepartmentCode ? false : true;" />
                        <div class="error-text" v-if="invalidData['DepartmentCode']">
                            {{ invalidData['DepartmentCode'] }}
                        </div>
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
                <Button label="Đóng" class="ms-button btn detail-button secondary"
                    @click="departmentPopupVisible = false" />
                <Button label="Lưu" class="ms-button btn detail-button primary" @click="doSave" @keyup.enter="doSave" />
            </div>

        </template>
    </Dialog>

    <Dialog v-model:visible="warningVisible" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
        header="Thông báo">
        <div class="w-full flex flex-column">
            <span> Phòng thi <b>{{ department.DepartmentName }}</b> đang được sử dụng. Bạn không thể xóa.</span>
        </div>
        <template #footer>
            <Button label="Không" class="ms-button btn detail-button secondary" @click="warningVisible = false" />
        </template>
    </Dialog>

    <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
        header="Xóa phòng thi">
        <div class="w-full flex flex-column">
            <span> Bạn có chắc chắn muốn xóa phòng thi <b>{{ department.DepartmentName }}</b></span>
        </div>
        <template #footer>
            <Button label="Không" class="ms-button btn detail-button secondary" @click="isPopupDelete = false" />
            <Button label="Xóa phòng thi" class="ms-button btn w-100 danger" @click="handlerDelete" />
        </template>
    </Dialog>
    <Toast />
</template>

<script>
import Toast from 'primevue/toast';
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { generateCode } from '../common/Functions'
import { saveData, getDataDepartment, updateDepartment, deleteDepartment } from '../../api/department';
export default {
    name: "DepartmentList",
    components: {
        Dialog,
        Button,
        InputText,
        Toast,
        DataTable,
        Column,
        Skeleton,
    },

    data() {
        return {
            departmentPopupVisible: false,
            modePopup: this.FormMode.Insert,
            department: {
                DepartmentId: null,
                DepartmentName: null,
                DepartmentCode: null,
            },
            warningVisible: false,
            dataChanged: null,

            data: [],  // data table

            isLoading: true,

            isPopupDelete: false,

            invalidData: [],   //mảng chứa thông tin lỗi
            modeGenerate: true, //cho phép sinh mã theo tên
        }
    },
    methods: {

        /**
         * Xử lý hàm sinh mã theo tên
         */
        handlerGenerateCode() {
            if (this.modeGenerate) this.department.DepartmentCode = generateCode(this.department.DepartmentName);
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
         * Lưu phòng thi
         */
        doSave(event) {
            event.preventDefault();
            this.invalidData = [];
            if (this.validateData()) {
                switch (this.modePopup) {
                    case this.FormMode.Insert:
                        saveData(this.department).then(res => {
                            this.department = {};
                            this.showToast('Thêm thành công');
                            this.departmentPopupVisible = false;
                        }).catch(error => {
                            if (error.response.status == 422) {
                                for (var itemError in error.response.data.errors) {
                                    // console.log(error.response.data.errors);
                                    this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                }
                            }
                            return;
                        })
                        break;
                    case this.FormMode.Update:
                        if (JSON.stringify(this.dataChanged) !== JSON.stringify(this.department)) {
                            updateDepartment(this.department).then(res => {
                                this.department = {};
                                this.departmentPopupVisible = false;
                                this.showToast('Cập nhật thành công');
                                this.loadData();
                            }).catch(error => {
                                if (error.response.status == 422) {
                                    for (var itemError in error.response.data.errors) {
                                        this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                    }
                                }
                                return;
                            });
                        } else this.departmentPopupVisible = false;
                        break;
                    default:
                        break;
                }
            }
            this.modeGenerate = true;
            this.loadData();
        },

        /**
         * Click nút xóa phòng thi
         */
        handlerDelete() {
            deleteDepartment(this.department.DepartmentId).then(res => {
                this.isPopupDelete = false;
                this.showToast('Xóa thành công');
                this.loadData();
            }).catch(error => {
                console.log(error);
            })
        },

        /**
         * Hiển thị toast message
         * @param {*} message
         */
        showToast(message, severity = 'success') {
            this.$toast.add({ severity: severity, summary: 'Thông báo', detail: message, life: 3000 });
        },

        /**
         * Xóa phòng thi
         * @param {*} data
         */
        deleteRowSelect(data) {
            if (data.is_exist) {
                this.warningVisible = true;
            }
            else {
                this.isPopupDelete = true;
                this.department = data;
            }
        },

        /**
         * validate dữ liệu
         */
        validateData() {
            var invalid = true;
            if (this.department.DepartmentName == null) {
                this.invalidData['DepartmentName'] = "Tên phòng thi không được để trống";
                invalid = false;
            }
            if (this.department.DepartmentCode == null) {
                this.invalidData['DepartmentCode'] = "Mã phòng thi không được để trống";
                invalid = false;
            }
            return invalid;
        },

        /**
         * Lấy thông tin bản ghi
         * @param {*} data
         */
        onRowSelect(data) {
            this.modePopup = this.FormMode.Update;
            this.department = { ...data };
            this.dataChanged = data;
            this.departmentPopupVisible = true;
        },

        /**
         * Click nút thêm phòng thi
         */
        btnInsertDepartment() {
            //set form mode
            this.modePopup = this.FormMode.Insert;
            //mở popup
            this.departmentPopupVisible = true;
            this.department = {};

        },

        /**
         * Load dữ liệu
         */
        async loadData() {
            this.isLoading = true;
            // this.data = Array.from({ length: 5 }, () => ({ ...this.department }));
            await getDataDepartment().then(res => {
                setTimeout(() => {
                    this.isLoading = false
                }, 500);
                this.data = res;
            }).catch(error => {
                console.log(error);
            });

            // console.log(this.data);
            // console.log(Array.from({ length: 10 }, () => ({ ...this.department })));
            // this.data = [
            //     { DepartmentCode: 1, DepartmentName: 1 }
            // ]
            // debugger
        }
    },

    created() {
        this.loadData();
    },

    mounted() {
    }
}
</script>
