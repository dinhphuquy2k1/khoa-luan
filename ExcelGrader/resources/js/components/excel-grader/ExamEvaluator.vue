<template>
    <div class="main-view flex1 flex-column">
        <div class="form-list flex-column flex1">
            <div class="flex-column flex1">
                <div class="flex-center title-box">
                    <div class="list-title flex1">Thực hiện chấm thi</div>
                    <div class="flex-1">
                        <div class="flex-row">
                            <div class="flex1"></div>
                            <div class="split-button">
                                <Button label="Thực hiện chấm" class="ms-button btn detail-button primary"
                                    v-if="tabItemSelected == 1" @click="isShowGradingType = !isShowGradingType"
                                    @mouseover="" v-click-outside="() => isShowGradingType = false"
                                    :disabled="!selectedDepartment || !selectedExamShift || !selectedManager || valuesFile[0].Empty || valuesFile[1].Empty">
                                    <div class="text">
                                        Thực hiện chấm
                                    </div>
                                    <div class="button-line"> </div>
                                    <div class="split-icon icon">
                                        <div class="icon24 icon right chevron-down-white"></div>
                                    </div>
                                </Button>
                                <ul class="v-context" v-if="isShowGradingType" @mouseleave="isShowGradingType = false"
                                    style="position: absolute; right: 0; width: 100%; box-shadow: 0 0 16px rgba(23,27,42,.24); margin-top: 3px;">
                                    <li @click="gradingType = false, btnEvaluator(true)">Chấm tuyệt
                                        đối</li>
                                    <li @click="gradingTypeVisible = true, gradingType = true">Chấm
                                        tương
                                        đối
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="list-content flex1 flex-row">
                    <div class="w-full flex1 flex-row">
                        <div class="box left-box">
                            <div class="list-tab-menu">
                                <div class="tab-item">
                                    <div class="tab-item-parent" :class="{ 'active': tabItemSelected == 1 }"
                                        @click="tabItemSelected = 1">
                                        <span>1. Thông tin kì thi</span>
                                    </div>
                                    <div class="tab-item-child mt-10">
                                        <div class="form-group flex-row">
                                            <div class="flex1 mr-10">
                                                <div class="form-group-label d-flex label-form">
                                                    1.1. Kì thi
                                                    <span class="required">*</span>
                                                </div>
                                                <Dropdown v-model="selectedManager" :options="examManager"
                                                    optionLabel="ExamName" optionValue="ExamId" placeholder="Chọn kì thi"
                                                    @change="onChangeExamManager" />
                                                <div class="flex1">
                                                    <div class="ms-input ms-editor w-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-item-child">
                                        <div class="form-group flex-row">
                                            <div class="flex1 mr-10">
                                                <div class="form-group-label d-flex label-form">
                                                    1.2. Ca thi
                                                    <span class="required">*</span>
                                                </div>
                                                <Dropdown v-model="selectedExamShift" :options="examShift"
                                                    optionLabel="ExamShiftName" optionValue="ExamShiftId"
                                                    placeholder="Chọn ca thi" @change="onChangeExamShift" />
                                                <div class="flex1">
                                                    <div class="ms-input ms-editor w-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-item-child">
                                        <div class="form-group flex-row">
                                            <div class="flex1 mr-10">
                                                <div class="form-group-label d-flex label-form">
                                                    1.2. Phòng thi
                                                    <span class="required">*</span>
                                                </div>
                                                <Dropdown v-model="selectedDepartment" :options="department"
                                                    optionLabel="DepartmentName" optionValue="DepartmentId"
                                                    placeholder="Chọn phòng thi" @change="onChangeDepartment" />
                                                <div class="flex1">
                                                    <div class="ms-input ms-editor w-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-item">
                                    <div class="tab-item-parent" @click="tabItemSelected = 2"
                                        :class="{ 'active': tabItemSelected == 2 }">2. Danh sách chia đề thi</div>
                                </div>
                            </div>
                        </div>
                        <div class="box list-content flex1 flex-column">
                            <div class="content-body flex1 flex-column" v-if="tabItemSelected == 1">
                                <div class="mb-20">
                                    <Panel header="1. Thông tin file" :collapsed="examResult.length > 0" toggleable
                                        class="customPanel flex1" :toggle-button-props="{ 'aria-label': 'customPanel' }">
                                        <DataTable class="flex1 flex-column"
                                            :value="isLoading ? Array.from({ length: 2 }, () => ({ ...this.department })) : valuesFile"
                                            scrollable @rowDblclick="onRowSelect($event.data)"
                                            :class="{ 'loading': isLoading }" :loading="isLoading"
                                            tableStyle="min-width: 50rem" rowHover table-class="grid-group">
                                            <Column header="STT" style="width: 100px;" class="text-center">
                                                <template #body="slotProps">
                                                    <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column field="Type" dataKey="id" header="Loại File" style="width: 450px;">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading"> {{ data[field] }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column field="Resource" dataKey="id" header="Tên File">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading">
                                                        <div v-if="data.Empty">
                                                            <span class="error-text"> Chưa có file. Tải lên để thực hiện
                                                                chấm</span>
                                                        </div>
                                                        <div v-else>
                                                            {{ data.FileName }}
                                                        </div>
                                                    </div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column alignFrozen="right" style="width: 180px;" frozen header="Thao tác">
                                                <template #body="slotProps">
                                                    <div class="row-actions" v-if="!isLoading">
                                                        <div class="position-relative">
                                                            <div class="item"
                                                                @click="onRowSelect(slotProps.data), isShowPopupUploadFile = !isShowPopupUploadFile, fileSelectedOnUpload = slotProps.data, headerUploadFile = slotProps.data.Type">
                                                                <div class="v-popover popover">
                                                                    <div class="trigger">
                                                                        <div class="icon24 upload"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                        </DataTable>
                                    </Panel>
                                </div>
                                <div class="mb-20 flex-column flex1">
                                    <Panel header="2. Kết quả chấm" toggleable :collapsed="examResult.length == 0"
                                        class="customPanel flex1" :toggle-button-props="{ 'aria-label': 'customPanel' }">
                                        <div class="ms-panel-header">
                                            <div class="flex1"></div>
                                            <Button class="ms-button btn primary has-tooltip" aria-label="Youtube"
                                                v-if="examResult.length != 0" @click="exportExamResult">
                                                <img alt="logo"
                                                    src="../../../../public/assets/icon/ic_export_excel.cd7bdef7.svg"
                                                    class="export-excel" />
                                                <span class="px-3 text">Xuất kết quả</span>
                                            </Button>
                                        </div>
                                        <DataTable class="flex1 mt-10 flex-column" :class="{ 'loading': isLoading }"
                                            :loading="isLoading" table-class="grid-group"
                                            :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : examResult"
                                            tableStyle="min-width: 100%" rowHover>

                                            <Column header="STT" style="width: 40px;" class="text-center">
                                                <template #body="slotProps">
                                                    <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column header="Số báo danh" field="CandidateNumber" style="width: 40px;"
                                                class="text-center">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading"> {{ data[field] }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column header="Họ và tên" field="StudentName" style="width: 80px;"
                                                class="text-left">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading"> {{ data[field] }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column header="Phòng thi" field="DepartmentName" style="width: 100px;"
                                                class="text-center">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading"> {{ data[field] }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column header="Mã sinh viên" field="StudentCode" style="width: 100px"
                                                class="text-center">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading"> {{ data[field] }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column header="Tổng điểm" field="TotalScore" style="width: 50px;"
                                                class="text-center">
                                                <template #body="{ data, field, slotProps }">
                                                    <div v-if="!isLoading"> {{ data[field] }}</div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                            <Column alignFrozen="right" style="width: 80px;" frozen header="Thao tác">
                                                <template #body="slotProps">
                                                    <div class="row-actions" v-if="!isLoading">
                                                        <div class="text-link"
                                                            @click="onRowSelect(slotProps.data), selectedResult = slotProps.data, loadExamResultDetail()">
                                                            <div>Xem chi tiết</div>
                                                        </div>
                                                    </div>
                                                    <div v-else>
                                                        <Skeleton height="18px" class="mb-2"></Skeleton>
                                                    </div>
                                                </template>
                                            </Column>
                                        </DataTable>
                                    </Panel>
                                </div>
                            </div>

                            <div class="content-body flex1 flex-column" v-if="tabItemSelected == 2">
                                <div class="grid-nodata-content flex-column">
                                    <div class="img-nodata"></div>
                                    <div class="description-nodata">Chưa có danh sách phân chia đề thi</div>
                                    <div class="file-caution-center description-nodata">
                                        Lưu ý <span style="color: red;">*</span>: Bạn vui lòng chọn thông tin kì thi
                                        và tải lên danh sách thi
                                        <br>của kì thi đó.
                                    </div>
                                    <Button class="ms-button btn detail-button primary" @click="getListExam">
                                        <div class="text">
                                            Lấy danh sách
                                        </div>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Dialog v-model:visible="isShowPopupUploadFile" modal :header="headerUploadFile" :style="{ width: '30vw' }"
        @afterHide="afterHideUploadFile">
        <input type="file" hidden ref="file" :accept="fileSelectedOnUpload.FileAccept" id="assetsFieldHandle"
            @change="onChangeFile">
        <div class="flex1 d-flex" @dragover="dragover" @dragleave="dragleave" @drop="drop">
            <label for="assetsFieldHandle" class="flex1">
                <div class="d-flex">
                    <div class="upload-container flex1 flex-center" v-if="!fileSelected">
                        <div class="no-file d-flex"
                            :class="{ 'file-zip': fileSelectedOnUpload.FileType == 2, 'file-xlsx': fileSelectedOnUpload.FileType == 1 }">
                        </div>
                    </div>
                    <div class="import-attachment-container flex1" v-if="fileSelected">
                        <div class="file-info d-flex">
                            <div class="d-flex text-truncate">
                                <div class="file-icon text-left " style="width: 20px;"></div>
                                <div class="file-name text-left">{{ fileSelected.name }}
                                </div>
                            </div>
                            <div class="file-size text-left"> {{ formatSize(fileSelected.size) }}</div>
                            <div class="file-accepted text-left d-flex">
                                <div class="icon-success" v-if="FileSuccess"></div>
                                <div v-if="FileSuccess">Hợp lệ</div>
                                <div class="icon-unsuccess" v-if="!FileSuccess"></div>
                                <div v-if="!FileSuccess">Không hợp lệ</div>
                            </div>
                            <div class="change-file blue-text pointer text-left text-link mw-maxcontent">
                                Đổi
                                tệp khác
                            </div>
                        </div>
                        <div class="file-caution" v-if="FileSuccess">
                            <ProgressBar :value="progress" :showValue="false" v-if="isLoading"></ProgressBar>
                            <div class="file-caution-img">
                            </div>
                            <div class="file-caution-center mt-20">
                            </div>
                        </div>
                        <div class="file-error" v-else>
                            <div class="file-error-title red-text"> Lý do không hợp lệ: </div>
                            <div class="file-error-title red-text">- Dung lượng quá lớn</div>
                        </div>
                    </div>
                </div>
            </label>
        </div>
        <template #footer>
            <Button class="ms-button secondary btn has-tooltip mr-10" aria-label="Youtube" @click="afterHideUploadFile()">
                <span class="px-3 text">Đóng</span>
            </Button>
            <Button class="ms-button btn primary has-tooltip" aria-label="Youtube" @click="uploadEvent()"
                :disabled="!fileSelected">
                <span class="px-3 text">Thực hiện tải</span>
            </Button>
        </template>
    </Dialog>
    <Dialog v-model:visible="visibleExamResultDetail" modal
        :header="selectedResult ? `Điểm chi tiết ${selectedResult.StudentName}` : ''" :style="{ width: '70vw' }" scrollable
        closeIcon="close-button">
        <DataTable class="flex1 flex-column" :class="{ 'loading': isLoading }" :loading="isLoading" table-class="grid-group"
            :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : resultDetail"
            tableStyle="min-width: 100%" rowHover>
            <Column header="STT" style="width: 15px;" class="text-center">
                <template #body="slotProps">
                    <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Mã sinh viên" field="StudentCode" style="width: 100px;" class="text-center">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Đề thi" field="ExamBankName" style="width: 100px;" class="text-center">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Phòng thi" field="DepartmentName" style="width: 100px;" class="text-center">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Tiêu chí" field="PropertyName" style="width: 100px;" class="text-center">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Điểm tiêu chí" field="Point" style="width: 100px;" class="text-center">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Điểm đạt được" field="RealScore" style="width: 100px;" class="text-center">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
        </DataTable>
        <template #footer>
            <Button label="Đóng" class="text-white ms-button text-white btn w-100 danger"
                @click="visibleExamResultDetail = false, selectedResult = null" />
        </template>
    </Dialog>
    <Dialog v-model:visible="dialogVisible" modal header="Thông báo" :style="{ width: '40vw' }" closeIcon="close-button">
        <p>
            {{ contentDialog }}
        </p>
        <template #footer>
            <Button label="Đóng" class="text-white ms-button text-white btn w-100 danger" @click="dialogVisible = false" />
        </template>
    </Dialog>

    <Dialog v-model:visible="gradingTypeVisible" @keydown.enter.prevent="btnEvaluator(true)" modal
        header="Thiết lập điểm trừ" :style="{ width: '20vw' }" closeIcon="close-button">
        <div class="flex1 mr-10">
            <div class="form-group-label d-flex label-form">
                Phần trăm điểm bị trừ
                <span class="required">*</span>
            </div>
            <div class="ms-input ms-editor w-100">
                <InputNumber v-model="deductedPoint" :class="{ 'error': invalidDeductedPoint }"
                    placeholder="Nhập tên kì thi" :min="0" :max="100" :max-fraction-digits="2" />
                <div class="error-text" v-if="invalidDeductedPoint">
                    {{ invalidDeductedPoint }}
                </div>
            </div>
            <div class="flex1">
                <div class="ms-input ms-editor w-100"></div>
            </div>
        </div>
        <template #footer>
            <Button label="Đóng" class="text-white ms-button text-white btn w-100 danger"
                @click="gradingTypeVisible = false" />
            <Button class="ms-button btn primary has-tooltip" aria-label="Youtube" @click="btnEvaluator(true)">
                <span class="px-3 text">Chấm tương đối</span>
            </Button>
        </template>
    </Dialog>
    <Toast />
    <TheLoadingProgress v-show="isLoadingComponent" :fixed="false" />
</template>

<script>
import Knob from 'primevue/knob';
import Toast from 'primevue/toast';
import ProgressBar from 'primevue/progressbar';
import FileUpload from 'primevue/fileupload';
import Menubar from 'primevue/menubar';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Panel from 'primevue/panel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import TheLoadingProgress from '../TheLoadingProgress.vue';
import Resumable from 'resumablejs';
import { getDetailExamManager, excuteEvaluator, getExamResult, getExamResultDetail, exportExamResult, exportExamList } from '../../../api/examevaluator';
import Button from 'primevue/button';
export default {
    components: {
        Menubar,
        Dropdown,
        TabView,
        TabPanel,
        Panel,
        ProgressBar,
        DataTable,
        Skeleton,
        Column,
        InputNumber,
        Knob,
        Toast,
        FileUpload,
        Dialog,
        Button,
        TheLoadingProgress,
    },

    data() {
        return {
            valuesFile: [
                { STT: 1, Type: 'File danh sách thi', Resource: 'Chưa có file danh sách thi. Tải lên để thực hiện chấm', Empty: true, FileSelected: null, ResourcePath: null, FileName: null, FileType: 1, FileAccept: '.xlsx', MaxFileSize: 30 * 1024 * 1024 },
                { STT: 2, Type: 'File bài thi', Resource: 'Chưa có file bài thi. Tải lên để thực hiện chấm', Empty: true, FileSelected: null, ResourcePath: null, FileName: null, FileType: 2, FileAccept: '.zip', MaxFileSize: 800 * 1024 * 1024 },
            ],

            columnsFile: [
                { field: 'STT', header: 'STT' },
                { field: 'Type', header: 'Loại file' },
                { field: 'Resource', header: 'Tên File' },
            ],

            tabItemSelected: 1,

            visibleUploadFile: true,

            headerUploadFile: 'Tải file',
            fileSelectedOnUpload: null,
            gradingType: false, //loại chấm điểm
            isShowGradingType: false,
            gradingTypeVisible: false,
            deductedPoint: 50, //điểm bị trừ
            invalidDeductedPoint: null,

            fileSelected: null,
            isShowPopupUploadFile: false,
            File: null,
            FileSuccess: false,
            isUpload: false,
            totalSize: 0,
            dialogVisible: false,
            totalSizePercent: 0,

            resultDetail: [],
            visibleExamResultDetail: false,
            selectedResult: null,
            objCheckSelectedResult: null,



            isLoadingComponent: false,

            modeFile: null,
            fileXlsxSelected: null,
            fileZipSelected: null,

            isLoading: false,
            resumable: null,
            progress: 0,

            examManager: [],
            selectedManager: null,
            examShift: [],
            selectedExamShift: null,
            department: [],
            selectedDepartment: null,

            examResult: [],
        }
    },

    methods: {
        onRowSelect(data) {
            // console.log(data);
        },

        async btnEvaluator(flag) {
            //thực hiện chấm
            if (flag) {
                this.gradingTypeVisible = false;
                this.deductedPoint = 50;
                this.isLoadingComponent = true;
                this.isShowGradingType = false;
                var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
                data = JSON.parse(data.ExamShift).filter(_item => _item.ExamShiftId == this.selectedExamShift && _item.DepartmentId == this.selectedDepartment && _item.ExamShiftId == this.selectedExamShift);
                await excuteEvaluator({ data: data, gradingType: this.gradingType, deductedPoint: this.deductedPoint }).then(res => {
                    this.loadExamResult();
                }).catch(error => {
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoadingComponent = false;
                    }, 300);
                });
            }
        },

        /**
        * Hiển thị toast message
        * @param {*} message
        */
        showToast(message, severity = 'success') {
            this.$toast.add({ severity: severity, summary: 'Thông báo', detail: message, life: 3000 });
        },

        /**
         * Sự kiện kéo thả file
         * @param {*} event
         */
        drop(event) {
            event.preventDefault();
            this.$refs.file.files = event.dataTransfer.files;
            this.onChangeFile(event); // Trigger the onChange event manually
            // Clean up
            event.currentTarget.classList.add('bg-gray-100');
            event.currentTarget.classList.remove('bg-green-300');
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
         * Sự kiện chọn kì thi
         */
        onChangeExamManager() {
            this.isLoading = true;
            this.valuesFile[1].FileName = this.valuesFile[0].FileName = null;
            this.valuesFile[1].Empty = this.valuesFile[0].Empty = true;
            //dữ liệu kì thi

            this.selectedFirst();
            this.loadExamResult();
        },

        /**
         * Sự kiện chọn ca thi
         */
        onChangeExamShift() {
            this.valuesFile[1].FileName = this.valuesFile[0].FileName = null;
            this.valuesFile[1].Empty = this.valuesFile[0].Empty = true;

            //dữ liệu kì thi
            var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
            data = JSON.parse(data.ExamShift);
            //dữ liệu ca thi
            var result = data.filter(_item => _item.ExamShiftId == this.selectedExamShift);
            //Mảng dữ liệu phòng thi
            this.department = this.getUniqueItems(result, 'DepartmentId');
            //auto focus giá trị đầu
            this.selectedDepartment = this.department[0].DepartmentId;

            //file danh sách
            if (this.department[0].ResourcePathFileList != null) {
                this.valuesFile[0].FileName = this.department[0].ResourcePathFileList.substring(3);
                this.valuesFile[0].Empty = false;
            }

            if (this.department[0].ResourcePathFileAssignment != null) {
                this.valuesFile[1].FileName = this.department[0].ResourcePathFileAssignment.substring(3);
                this.valuesFile[1].Empty = false;
            }
            this.loadExamResult();
        },

        /**
         * Sự kiện chọn phòng thi
         */
        onChangeDepartment() {
            this.examResult = []; //kết quả thi
            this.valuesFile[1].FileName = this.valuesFile[0].FileName = null;
            this.valuesFile[1].Empty = this.valuesFile[0].Empty = true;

            var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
            data = JSON.parse(data.ExamShift);
            var result = data.find(_item => this.selectedDepartment == _item.DepartmentId && this.selectedExamShift == _item.ExamShiftId);
            try {
                if (result.ResourcePathFileList != null) {
                    this.valuesFile[0].FileName = String(result.ResourcePathFileList).substring(3);
                    this.valuesFile[0].Empty = false;
                }

                if (result.ResourcePathFileAssignment != null) {
                    this.valuesFile[1].FileName = String(result.ResourcePathFileAssignment).substring(3);
                    this.valuesFile[1].Empty = false;
                }
            } catch (error) {
                console.log(result, this.selectedDepartment, this.department, this.selectedExamShift, data);
            }
            this.loadExamResult();
        },

        /**
         *
         * @param {*} arr
         */
        selectedFirst() {
            try {
                var data = this.examManager.find(_item => this.selectedManager == _item.ExamId).ExamShift;
                var data = JSON.parse(data);
                this.examShift = this.getUniqueItems(data, 'ExamShiftCode');
                this.department = this.getUniqueItems(data, 'DepartmentId');
                if (!this.examShift.find(_item => _item.ExamShiftId == this.selectedExamShift)) {
                    this.selectedExamShift = this.examShift[0].ExamShiftId;
                }

                var index = this.department.findIndex(_item => _item.DepartmentId == this.selectedDepartment);
                if (index == -1) {
                    this.selectedDepartment = this.department[0].DepartmentId;
                }

                if (this.department[index] != null && this.department[index].ResourcePathFileList != null) {
                    this.valuesFile[0].FileName = this.department[index].ResourcePathFileList.substring(3);
                    this.valuesFile[0].Empty = false;
                }

                if (this.department[index] != null && this.department[index].ResourcePathFileAssignment != null) {
                    this.valuesFile[1].FileName = this.department[index].ResourcePathFileAssignment.substring(3);
                    this.valuesFile[1].Empty = false;
                }
            } catch (error) {
                console.log(error);
            }
        },

        /**
         * Cập nhật thông tin file
         */
        updateFileInformation() {
            try {
                this.valuesFile[1].FileName = this.valuesFile[0].FileName = null;
                this.valuesFile[1].Empty = this.valuesFile[0].Empty = true;
                //dữ liệu kì thi
                var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
                data = JSON.parse(data.ExamShift);
                //dữ liệu ca thi
                var result = data.filter(_item => _item.ExamShiftId == this.selectedExamShift);
                //Mảng dữ liệu phòng thi
                this.department = this.getUniqueItems(result, 'DepartmentId');

                var index = this.department.findIndex(_item => _item.DepartmentId == this.selectedDepartment);
                if (this.department[index] != null && this.department[index].ResourcePathFileList != null) {
                    this.valuesFile[0].FileName = this.department[index].ResourcePathFileList.substring(3);
                    this.valuesFile[0].Empty = false;
                }

                if (this.department[index] != null && this.department[index].ResourcePathFileAssignment != null) {
                    this.valuesFile[1].FileName = this.department[index].ResourcePathFileAssignment.substring(3);
                    this.valuesFile[1].Empty = false;
                }
            } catch (error) {
                console.log(error);
            }
        },

        /**
         * Sự kiện kéo file vào
         * @param {*} event
         */
        dragover(event) {
            event.preventDefault();
            // Add some visual fluff to show the user can drop its files
            if (!event.currentTarget.classList.contains('bg-green-300')) {
                event.currentTarget.classList.remove('bg-gray-100');
                event.currentTarget.classList.add('bg-green-300');
            }
        },

        /**
         * Sự kiện kéo file ra
         * @param {*} event
         */
        dragleave(event) {
            // Clean up
            event.currentTarget.classList.add('bg-gray-100');
            event.currentTarget.classList.remove('bg-green-300');
        },

        /**
         * Lấy danh sách đã phân chia đề thi
         */
        getListExam() {
            if (this.selectedDepartment == null) {
                this.showToast("Chưa chọn thông tin kì thi", 'error');
                return;
            }

            if (this.selectedManager == null) {
                this.showToast("Chưa chọn thông tin kì thi", 'error');
                return;
            }

            if (this.selectedExamShift == null) {
                this.showToast("Chưa chọn thông tin kì thi", 'error');
                return;
            }

            if (this.valuesFile[0].FileName == null) {
                this.showToast("Chưa tải lên file danh sách thi", 'error');
                return;
            }

            this.isLoadingComponent = true;
            //dữ liệu kì thi
            var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
            data = JSON.parse(data.ExamShift);
            //dữ liệu ca thi
            var result = data.filter(_item => _item.ExamShiftId == this.selectedExamShift);
            //Mảng dữ liệu phòng thi
            this.department = this.getUniqueItems(result, 'DepartmentId');

            var index = this.department.findIndex(_item => _item.DepartmentId == this.selectedDepartment);
            data = data.filter(_item => _item.ExamShiftId == this.selectedExamShift && _item.DepartmentId == this.selectedDepartment && _item.ExamShiftId == this.selectedExamShift);
            exportExamList({ fileInfo: this.department[index], exam: data }).then(res => {
                var filename = this.department.find(_item => _item.DepartmentId == this.selectedDepartment).DepartmentName;
                const url = window.URL.createObjectURL(new Blob([res]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `Danh sách phân chia đề thi ${filename}.xlsx`);
                document.body.appendChild(link);
                link.click();
            }).catch(error => {

            }).finally(() => {
                setTimeout(() => {
                    this.isLoadingComponent = false;
                }, 750);
            });

            console.log(this.department[index]);

        },

        /**
         * Thông tin kì thi
         */
        async loadExamManager() {
            await getDetailExamManager().then(res => {
                this.examManager = res;
            }).catch(error => {
                console.log(error);
            });
        },



        /**
         * Xuất kết quả
         */
        exportExamResult() {
            if (this.selectedDepartment != null && this.selectedExamShift != null && this.selectedManager != null && this.examResult.length > 0) {
                this.isLoadingComponent = true;
                exportExamResult({ data: this.examResult }).then(res => {
                    var filename = this.department.find(_item => _item.DepartmentId == this.selectedDepartment).DepartmentName;
                    const url = window.URL.createObjectURL(new Blob([res]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Kết quả chấm ${filename}.xlsx`);
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    this.showToast("Đã xảy ra lỗi, vui lòng liên hệ với nhà phát triển", 'error');
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoadingComponent = false;
                    }, 750);
                });
            }
        },

        /**
         * Sự kiện thay đổi file
         */
        onChangeFile(event) {
            event.preventDefault();
            if (this.selectedDepartment == null || this.selectedExamShift == null || this.selectedManager == null) {
                this.showToast("Chọn thông tin kì thi để thực hiện tải", 'error');
                return;
            }
            if (this.$refs.file.files[0]) {
                // if (this.$refs.file.files[0].size > this.fileSelectedOnUpload.MaxFileSize) {
                //     return;
                // }

                // if (this.$refs.file.files[0].size > this.fileSelectedOnUpload.MaxFileSize) {
                //     console.log(1);
                //     return;
                // }
                //xlsx
                if (this.fileSelectedOnUpload.FileType == 1) {
                    var validExts = new Array(".xlsx", ".xls");
                    var fileExt = this.$refs.file.files[0].name;
                    fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
                    if (validExts.indexOf(fileExt) < 0) {
                        this.contentDialog = ' File danh sách thi chỉ hỗ trợ định dạng *.xlsx';
                        this.dialogVisible = true;
                        return;
                    }
                }
                //zip
                else {
                    var validExts = new Array(".zip");
                    var fileExt = this.$refs.file.files[0].name;
                    fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
                    if (validExts.indexOf(fileExt) < 0) {
                        this.contentDialog = ' File bài thi chỉ hỗ trợ định dạng *.zip';
                        this.dialogVisible = true;
                        return;
                    }
                }
                this.FileSuccess = true;
                this.fileSelected = this.$refs.file.files[0];
            }
        },

        /**
         * Click nút upload
         */
        uploadEvent() {
            try {
                this.isLoading = true;
                switch (this.fileSelectedOnUpload.FileType) {
                    //xlsx
                    case 1:
                        //đổi tên để kiểm tra trên backend
                        this.fileSelected = new File([this.fileSelected], 'DS@' + this.fileSelected.name, { type: this.fileSelected.type });
                        break;
                    //zip
                    case 2:
                        //đổi tên để kiểm tra trên backend
                        this.fileSelected = new File([this.fileSelected], 'BT@' + this.fileSelected.name, { type: this.fileSelected.type });
                        break;
                    default:
                        break;
                }
                //tên kì thi
                this.resumable.opts.query.ExamName = this.examManager.find(_item => _item.ExamId == this.selectedManager).ExamName;
                //cập nhật param
                this.resumable.opts.query.DepartmentId = this.selectedDepartment;
                this.resumable.opts.query.ExamId = this.selectedManager;
                this.resumable.opts.query.ExamShiftId = this.selectedExamShift;
                this.resumable.addFile(this.fileSelected);
            } catch (error) {

            }

        },

        /**
         * Khởi tạo Resumable upload file
         */
        createResumable() {
            this.resumable = new Resumable({
                target: '/api/exam-evaluator/uploadFile',
                method: 'POST',
                query: {
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // Thêm CSRF token để tránh lỗi 419
                },
                headers: {
                    'Accept': 'application/json',
                    'Content-Disposition': 'form-data; name="title"',
                },
                testChunks: false,
                throttleProgressCallbacks: 1,
                simultaneousUploads: 4,
            });
            this.resumable.on('fileAdded', this.onFileAdded);
            this.resumable.on('fileProgress', this.onFileProgress);
            this.resumable.on('fileSuccess', this.onFileSuccess);
            this.resumable.on('fileError', this.onFileError);
        },

        /**
         * Thêm file
         * @param {*} file
         */
        onFileAdded(file) {
            this.progress = 0;
            this.resumable.upload();
        },

        /**
         * Lấy kết quả chấm
         */
        async loadExamResult() {
            this.isLoading = true;
            this.examResult = [];
            if (this.selectedDepartment == null || this.selectedExamShift == null || this.selectedManager == null) {
                return;
            }
            await getExamResult({ ExamId: this.selectedManager, DepartmentId: this.selectedDepartment, ExamShiftId: this.selectedExamShift }).then(res => {
                this.examResult = res;
            }).catch(error => {
                this.showToast("Đã xảy ra lỗi, vui lòng liên hệ với nhà phát triển");
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            });
            var department = this.department.find(_department => _department.DepartmentId == this.selectedDepartment);

            if (department != null && department.ResourcePathFileList != null) {
                this.valuesFile[0].FileName = department.ResourcePathFileList.substring(3);
                this.valuesFile[0].Empty = false;
            }

            if (department != null && department.ResourcePathFileAssignment != null) {
                this.valuesFile[1].FileName = department.ResourcePathFileAssignment.substring(3);
                this.valuesFile[1].Empty = false;
            }
        },

        /**
         *
         * @param {*} file
         */
        onFileProgress(file) {
            this.progress = Math.floor(file.progress() * 100);
        },

        /**
         * Lấy thông tin chi tiết điểm của sinh viên
         */
        async loadExamResultDetail() {
            if (JSON.stringify(this.selectedResult) != JSON.stringify(this.objCheckSelectedResult)) {
                this.resultDetail = [];
                await getExamResultDetail(this.selectedResult.StudentCode).then(res => {
                    this.visibleExamResultDetail = true;
                    this.resultDetail = res;
                    console.log(res);
                }).catch(error => {
                    this.showToast("Đã xảy ra lỗi, vui lòng liên hệ với nhà phát triển");
                    console.log(error);
                });
            }
            else {
                this.visibleExamResultDetail = true;
            }
            this.objCheckSelectedResult = { ...this.selectedResult }
        },

        /**
         * Sự kiện trước khi ẩn popup upload file
         */
        afterHideUploadFile() {
            if (this.resumable.isUploading()) {
                this.resumable.cancel();
                this.showToast("File đang được tải lên, bạn có chắc chắn muốn thoát không?");
                return;
            }
            this.progress = 0;
            this.fileSelectedOnUpload = null;
            this.fileSelected = null;
            this.isShowPopupUploadFile = false;
            this.fileSelected = null
        },

        /**
         *
         * @param {*} file
         * @param {*} response
         */
        async onFileSuccess(file, response) {
            this.progress = 100;
            this.isLoading = false;
            this.showToast('Tải file thành công');
            this.isShowPopupUploadFile = false;
            await this.loadExamManager();
            //cập nhật lại thông tin file trên view
            this.updateFileInformation();
            this.resumable.removeFile(file);

        },

        onFileError(file, message) {
            console.log('File error:', file, message);
        },

        /**
         * Format file size
         * @param {*} bytes Dung lượng cần format
         */
        formatSize(bytes) {
            if (bytes === 0) return "0 B";
            const k = 1024;
            const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
        },
    },
    async created() {
        this.isLoading = true;
        await this.loadExamManager();

        //auto focus kì thi đầu tiên khi mở lên
        if (this.examManager[0]) {
            this.selectedManager = this.examManager[0].ExamId;
            this.selectedFirst();
        }
        //lấy kết quả chấm
        await this.loadExamResult();
        console.log(this.examResult);
        setTimeout(() => {
            this.isLoading = false;
        }, 750);
    },

    mounted() {
        this.createResumable();
    },

}
</script>

<style scoped>
@import url('../../../../public/css/components/exam-evaluator.css');
/* @import url('../../../../public/css/components/exam-manager-popup.css');  */
</style>
