<template>
    <div class="main-view flex1 flex-column position-relative">
        <div class="form-list flex-column flex1">
            <div class="flex-column flex1">
                <div class="flex-row title-box">
                    <div class="list-title flex1">Danh sách kì thi </div>
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
                        <button class="ms-button btn primary"
                            @click="modeModal = FormMode.Insert, examShiftDialogVisible = true">
                            <div class="icon24 icon left add-white"></div>
                            <div class="text pl-0">Thêm kì thi</div>
                        </button>
                    </div>
                </div>
                <div class="box list-content flex1 flex-row">
                    <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                        :loading="isLoading" :class="{ 'loading': isLoading }"
                        :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.exam })) : dataExamManager"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}"
                        paginatorTemplate="FirstPageLink PrevPageLink flex1 CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                        @rowDblclick="onRowSelect($event.data), modeModal = FormMode.Update, showExamManager()"
                        tableStyle="min-width: 100%" rowHover>
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
                        <!-- <Column selectionMode="multiple" headerStyle="width: 3rem">
                            <template #body="slotProps">
                                <div v-if="isLoading">
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                            <template #loading>
                                <div class="flex align-items-center"
                                    :style="{ height: '17px', 'flex-grow': '1', overflow: 'hidden' }">
                                    <Skeleton width="60%" height="1rem" />
                                </div>
                            </template>
                        </Column> -->
                        <Column header="STT" style="width: 100px;" class="text-center">
                            <template #body="slotProps">
                                <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="ExamCode" style="width: 10vw;" header="Mã kì thi">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="ExamName" dataKey="id" header="Tên kì thi">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ data[field] }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column dataKey="id" header="Các ca thi">
                            <template #body="slotProps">
                                <div v-if="!isLoading"> {{ getUniqueItems(JSON.parse(slotProps.data.ExamShift),
                                    'ExamShiftName').map(examBank => examBank.ExamShiftName).join(', ') }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="ExamStartDate" dataKey="id" header="Ngày bắt đầu kì thi">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ (data[field]) }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column field="ExamEndDate" dataKey="id" header="Ngày kết thúc kì thi">
                            <template #body="{ data, field, slotProps }">
                                <div v-if="!isLoading"> {{ (data[field]) }}</div>
                                <div v-else>
                                    <Skeleton height="18px" class="mb-2"></Skeleton>
                                </div>
                            </template>
                        </Column>
                        <Column alignFrozen="right" style="width: 100px; text-align: center;" frozen header="Thao tác">
                            <template #body="slotProps">
                                <div class="row-actions flex-row" v-if="!isLoading">
                                    <div class="item"
                                        @click="modeModal = FormMode.Update, onRowSelect(slotProps.data), showExamManager()">
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
        <ExamManagerPopup v-if="examShiftDialogVisible" @showExamManager="showExamManager"
            @loadExamManager="loadExamManager" @showToast="showToast" :modeModal="modeModal" :selectedData="selectedData" />
        <Toast />

        <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw' }" header="Xóa kì thi">
            <TheLoadingProgress v-if="isLoadingDelete" />
            <div class="w-full flex flex-column" style="line-height: 1.5;">
                <span> Kì thi có rất nhiều dữ liệu liên quan, bạn có chắc chắn muốn xóa kì thi <b>{{
                    selectedData.ExamName }} không?</b></span>
            </div>
            <template #footer>
                <Button label="Không" class="ms-button btn detail-button secondary" @click="isPopupDelete = false" />
                <Button label="Xóa kì thi" class="ms-button btn w-100 danger" @click="handlerDelete" />
            </template>
        </Dialog>
    </div>
</template>

<script>
import { generateCode, convertTimezoneToDatetime } from '../common/Functions';
import Toast from 'primevue/toast';
import ExamManagerPopup from '../components/exam-manager/ExamManagerPopup.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import TheLoadingProgress from '../components/TheLoadingProgress.vue';
import Calendar from 'primevue/calendar';
import { getExamManager, deleteExamManager, insertExamShift } from '../../api/exammanager';
export default {
    components: {
        DataTable,
        Column,
        Dialog,
        Button,
        Calendar,
        InputText,
        Dropdown,
        Toast,
        Skeleton,
        ExamManagerPopup,
        TheLoadingProgress
    },
    data() {
        return {
            dialogVisible: true,
            examShiftDialogVisible: false,

            columns: [
                { field: 'ExamShiftCode', header: 'Mã ca thi' },
                { field: 'ExamShiftName', header: 'Tên ca thi' },
                { field: 'DateTimeStart', header: 'Ngày bắt đầu' },
                { field: 'DateTimeEnd', header: 'Ngày kết thúc' },
                { field: 'Department', header: 'Phòng thi' },
            ],

            //ca thi
            listExamShift: [],

            isPopupDelete: false,
            isLoadingDelete: false,

            examshift: {
                ExamShiftId: null,
                ExamShiftCode: null,
                ExamShiftName: null,
                DateTimeStart: null,
                DateTimeEnd: null,
                Department: null
            },

            modeModal: this.FormMode.Insert,

            //tên các ca thi
            examShiftNames: null,

            isLoading: false,
            selectedData: {},

            dataExamManager: [],

            modeGenerate: true, //cho phép sinh mã theo tên

            modeGenerateExamShift: true, //sinh mã ca thi
            exam: {
                ExamId: null,
                ExamCode: null,
                ExamName: null,
                ExamStartDate: null,
                ExamEndDate: null,
                Note: null,
            },

            invalidData: [],

            invalidExamShift: [],

        }
    },

    methods: {

        /**
         * Ẩn / hiển form thêm kì thi
         */
        showExamManager() {
            this.examShiftDialogVisible = !this.examShiftDialogVisible;
        },

        /**
        * Xử lý hàm sinh mã theo tên
        */
        handlerGenerateCode(name) {
            if (name == 'exam' && this.modeGenerate) this.exam.ExamCode = generateCode(this.exam.ExamName);
            if (name == 'examShift' && this.modeGenerateExamShift) {
                this.examshift.ExamShiftCode = generateCode(this.examshift.ExamShiftName);
            }
        },

        /**
         * Xóa kì thi
         * @param {*} data
         */
        deleteRowSelect(data) {
            this.selectedData = { ...data };
            this.isPopupDelete = true;

            console.log(data);
        },

        /**
         * Call api xóa kì thi
         */
        async handlerDelete() {
            this.isLoadingDelete = true;
            var success = false;
            await deleteExamManager(this.selectedData.ExamId).then(res => {
                success = true;
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoadingDelete = false;
                    this.isPopupDelete = false;
                    this.loadExamManager();
                    if (success) this.showToast("Xóa thành công");
                }, 750);
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
         * selected item
         * @param {*} data
         */
        onRowSelect(data) {
            this.selectedData = { ...data };
        },

        /**
       * Lấy ra các phần tử không trùng lặp trong mảng
       * @param {*} data Mảng
       * @param {*} propName Phần tử cần lấy
       */
        getUniqueItems(data, propName) {
            return data.reduce((acc, curr) => {
                if (!acc.some(item => item[propName] === curr[propName])) {
                    acc.push(curr);
                }
                return acc;
            }, []);
        },

        /**
        * Lấy danh sách kì thi
        */
        async loadExamManager() {
            try {
                this.isLoading = true;
                await getExamManager().then(res => {
                    if (res[0]) {
                        var data = JSON.parse(res[0].ExamShift);
                        const examShiftNames = data.reduce((acc, curr) => {
                            if (!acc.includes(curr.ExamShiftName) && !acc.includes(curr.ExamShiftCode)) {
                                acc.push(curr.ExamShiftName);
                            }
                            return acc;
                        }, []).join(', ');
                        setTimeout(() => {
                            this.isLoading = false
                        }, 500);
                    }
                    this.dataExamManager = res;
                    // console.log(examShiftNames);
                }).catch(error => {
                    this.showToast("Có lỗi xảy ra, vui lòng liên hệ nhà phát triển", 'error');
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoading = false
                    }, 500);
                });
            } catch (error) {
                console.log(error);
            }
        },

        /**
         * Thêm ca thi
         */
        btnAddExamShift() {
            this.examShift.push({
                ExamShiftCode: null,
                ExamShiftName: null,
                DateTimeStart: null,
                DateTimeEnd: null,
                Department: null
            });
        },

        /**
         * Validate ngày kết thúc kì thi
         */
        handlerExamEndDate() {
            this.invalidData = [];
            if (this.exam.ExamEndDate.getTime() - this.exam.ExamStartDate.getTime() < 2 * 60 * 60 * 1000) {
                this.invalidData['ExamEndDate'] = 'Thời gian tối tiểu là 2h';
            }
        },

        /**
         * Xóa ca thi
         * @param {*} index
         */
        btnRemoveExamShift(index) {
            this.examShift.splice(index, 1)
            if (this.examShift.length < 1) this.btnAddExamShift();
        }
    },

    created() {
        this.loadExamManager();
    }
}
</script>

<style scoped>
@import url('../../../public/css/components/exam-manager.css');
</style>
