<template>
    <div class="detail-popup emulation-slide-detail ms-popup-main ams-popup" v-if="isShowModal">
        <div class="popup-modal"></div>
        <div class="vdr inactive">
            <div class="ms-popup">
                <div class="buttons" @click="toggleModal($FormMode.Insert)">
                    <div class="tooltip-top">
                        <div class="button close"></div>
                        <BaseTooltip tooltipTitle="Đóng" />
                    </div>
                </div>
                <div class="popup-header"> {{ modeModal == $FormMode.Insert ? 'Thêm đề thi' : 'Sửa đề thi' }}</div>
                <div class="popup-content">
                    <div class="w-full flex flex-column">
                        <div class="form-group flex-row">
                            <div class="flex1 mr-10">
                                <div class="form-group-label d-flex label-form">
                                    Mã đề thi
                                    <span class="required">*</span>
                                </div>
                                <div class="ms-input ms-editor w-100" style="width: 300px;">
                                    <div class="flex-row border">
                                        <input v-model.trim="exambank.ExamBankCode" type="text" ref="ExamBankCode"
                                            class="ms-input-item flex1" placeholder="Nhập tên đề thi">
                                    </div>
                                    <div class="error-text" v-if="invalidExamBank.ExamBankCode">
                                        {{ invalidExamBank.ExamBankCode }}</div>
                                </div>
                                <div class="flex1">
                                    <div class="ms-input ms-editor w-100"></div>
                                </div>
                            </div>
                            <div class="flex1 mr-10">
                                <div class="form-group-label d-flex label-form">
                                    Tên đề thi
                                    <span class="required">*</span>
                                </div>
                                <div class="flex1">
                                    <div class="ms-input ms-editor w-100" style="width: 300px;">
                                        <div class="flex-row border">
                                            <input v-model.trim="exambank.ExamBankName" type="text" ref="inputText"
                                                class="ms-input-item flex1" placeholder="Nhập tên đề thi">
                                        </div>
                                        <div class="error-text" v-if="invalidExamBank.ExamBankName">{{
                                            invalidExamBank.ExamBankName }}</div>
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
                                    File tư liệu
                                    <span class="required">*</span>
                                </div>
                                <div class="flex1">
                                    <div class="content-step d-flex">
                                        <div class="upload-container flex1 flex-center" v-if="!File.FileName"
                                            @click="$refs.fileInput.click()">
                                            <div class="no-file d-flex">
                                                <input type="file" id="fileInput" ref="fileInput"
                                                    @change="onFileChange('fileInput')"
                                                    accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                                    hidden>
                                            </div>
                                        </div>
                                        <div class="import-attachment-container flex1" v-else>
                                            <div class="file-info d-flex">
                                                <div class="d-flex">
                                                    <div class="file-icon text-left"></div>
                                                    <div class="file-name flex text-left">{{ File.FileName }}</div>
                                                </div>
                                                <div class="file-size text-left"> {{ File.FileSize }} </div>
                                                <div class="file-accepted text-left d-flex">
                                                    <div class="icon-success" v-if="File.Success"></div>
                                                    <div v-if="File.Success">Hợp lệ</div>
                                                    <div class="icon-unsuccess" v-if="!File.Success"></div>
                                                    <div v-if="!File.Success">Không hợp lệ</div>
                                                </div>
                                                <input type="file" ref="fileInput1" hidden
                                                    @change="onFileChange('fileInput1')"
                                                    accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                                <div class="change-file blue-text pointer text-left text-link"
                                                    @click="$refs.fileInput1.click()"> Đổi tệp khác
                                                </div>
                                            </div>
                                            <div class="file-caution" v-if="File.Success">
                                                <div class="file-caution-img"></div>
                                                <div class="file-caution-center mt-20">
                                                    Lưu ý <span style="color: red;">*</span>: Bạn vui lòng chọn sheet tư
                                                    liệu và dòng tiêu đề
                                                    <br>của sheet đó.
                                                </div>
                                            </div>
                                            <div class="file-error" v-if="!File.Success">
                                                <div class="file-error-title red-text"> Lý do không hợp lệ: </div>
                                                <div class="file-error-title red-text">- Dung lượng quá lớn</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-input ms-editor w-100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group flex-row">
                            <div class="flex1 mr-10">
                                <div class="form-group-label d-flex label-form">
                                    Sheet tư liệu
                                    <span class="required">*</span>
                                </div>
                                <Dropdown :options="sheetOptions" optionLabel="sheetName" optionValue="sheetIndex"
                                    v-model="exambank.SheetIndexReference"
                                    dropdownIcon="icon-combo--dropdown dropdown-list" />
                                <div class="error-text" v-if="invalidExamBank.SheetIndexReference">{{
                                    invalidExamBank.SheetIndexReference }}</div>
                                <div class="flex1">
                                    <div class="ms-input ms-editor w-100"></div>
                                </div>
                            </div>
                            <div class="flex1 mr-10">
                                <div class="form-group-label d-flex label-form">
                                    Dòng tiêu đề
                                    <span class="required">*</span>
                                </div>
                                <div class="flex1">
                                    <InputNumber v-model="exambank.RowReference" ref="RowReference" showButtons :min="1"
                                        :max="100" />
                                </div>
                                <div class="error-text" v-if="invalidExamBank.RowReference">{{
                                    invalidExamBank.RowReference }}</div>
                                <div class="flex1">
                                    <div class="ms-input ms-editor w-100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="popup-footer">
                    <div class="flex-row">
                        <div class="flex1"></div>
                        <div class="flex-rtl">
                            <div class="tooltip-top">
                                <button class="ms-button btn primary has-tooltip btn-tooltip-save" @click="btnSave">
                                    <div class="text">Lưu</div>
                                </button>
                                <BaseTooltip tooltipTitle="Ctrl + S" class="v-tooltip-save" />
                            </div>
                            <div class="tooltip-top">
                                <button class="ms-button btn mr-2 secondary btn-tooltip-close"
                                    @click="toggleModal($FormMode.Insert)">
                                    <div class="text">Hủy</div>
                                </button>
                                <BaseTooltip tooltipTitle="Esc" class="v-tooltip-close" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Dialog v-model:visible="dialogVisible" modal header="Thông báo" :style="{ width: '40vw' }"
            closeIcon="close-button">
            <p>
                {{ contentDialog }}
            </p>
            <template #footer>
                <Button label="Đóng" class="ms-button text-white btn w-100 danger" @click="dialogVisible = false" />
            </template>
        </Dialog>
    </div>
</template>

<script>
import InputNumber from 'primevue/inputnumber';
import { insertExamBank, updateExamBank } from '../../../api/exambank'
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Resumable from 'resumablejs';
import * as XLSX from 'xlsx';
import BaseTooltip from '../../base/base-tooltip/base-tooltip.vue';
import { mapState, mapActions } from 'vuex'
import Dialog from 'primevue/dialog';
export default {
    emits: ["setData", "loadExamBank", "showModal"],
    props: {
        FileData: {
            default: null
        },
        url: {
            type: String,
            default: '/exam'
        },
        selectedData: {
            default: {},
        },
    },
    components: {
        BaseTooltip,
        Dropdown,
        InputNumber,
        Dialog,
        Button,
    },
    data() {
        return {
            exambank: {
                id: null,
                ExamBankCode: null,
                ExamBankName: null,
                SheetIndexReference: null,
                DataReference: null,
                RowReference: null,
                ResourceFile: null,
                ResourcePath: null,
            },

            resumable: null,

            //dialog
            dialogVisible: false,
            contentDialog: null,
            url: null,

            sheetOptions: [],
            selectedSheet: {},
            File: {
                FileName: null,
                FileSize: null,
                Success: true,
                SheetCount: 1,
            },
            FileName: null,

            selectedFile: null,

            invalidExamBank: {
                ExamBankCode: null,
                ExamBankName: null,
                SheetIndexReference: null,
                RowReference: null,
                FileData: null
            },
        }
    },
    methods: {
        ...mapActions('popup', ['toggleModal']),
        ...mapActions('exambank', ['getExamBank']),
        async btnSave() {
            if (this.validateExamBank()) {
                // let formData = new FormData();
                // formData.append('id', this.exambank.id);
                // formData.append('ExamBankCode', this.exambank.ExamBankCode);
                // formData.append('ExamBankName', this.exambank.ExamBankName);
                // formData.append('SheetIndexReference', JSON.stringify([this.sheetOptions, this.exambank.SheetIndexReference, this.File.SheetCount]));
                // formData.append('RowReference', this.exambank.RowReference);
                // formData.append('FileData', this.selectedFile);
                // formData.append('FileInfo', this.File.FileSize);
                // formData.append('DataReference', this.exambank.DataReference);
                // formData.append('ResourceFile', this.exambank.ResourceFile);
                // formData.append('ResourcePath', this.exambank.ResourcePath);
                var me = this;
                switch (this.modeModal) {
                    case this.$FormMode.Insert:
                        // formData.append('_method', 'POST');
                        // //thêm mới
                        // await insertExamBank(formData).then(res => {
                        //     me.$emit("showModal");
                        //     me.$emit("loadExamBank");
                        // }).catch(error => {
                        //     console.log(error);
                        // });

                        this.callApi = true;
                        this.resumable.opts.query.param = JSON.stringify(this.exambank);
                        this.resumable.opts.query.FileInfo = this.File.FileSize;
                        // this.resumable.addFile(this.selectedFile);
                        this.resumable.opts.target = 'exambank';
                        this.resumable.addFile(this.selectedFile);
                        break;
                    case this.$FormMode.Update:
                        formData.append('_method', 'PUT');
                        //thêm mới
                        await updateExamBank(formData).then(res => {
                            me.toggleModal();
                        }).catch(error => {
                            console.log(error);
                        });
                        break;
                    default:
                        break;
                }
                //load lại dữ liệu
                this.$emit("loadExamBank");
            }
        },

        /**
         * Khởi tạo Resumable upload file
         */
        createResumable() {
            this.resumable = new Resumable({
                target: this.url,
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
            this.resumable.upload();
        },


        /**
         * Tiến trình tải
         * @param {*} file
         */
        onFileProgress(file) {
        },

        /**
         * Upload thành công
         * @param {*} file
         * @param {*} response
         */
        onFileSuccess(file, response) {

        },

        /**
         * Upload thất bại
         * @param {*} file
         * @param {*} message
         */
        onFileError(file, message) {
            message = JSON.parse(message);
            if (message.errorCode == 422) {
                for (var error in message.errors) {
                    this.invalidExamBank[error] = message.errors[error][0];
                }
            }
            else {
            }

        },

        /**
         * Call api thành công
         * @param {*} value
         */
        setCallApiSuccess(value) {
            this.callApiSuccess = value;
        },

        /**
         * Sự kiện thay đổi file
         * @param {*} ref ref input
         *
         */
        onFileChange(ref) {
            if (this.$refs[ref].files[0].type === "application/vnd.ms-excel" || this.$refs[ref].files[0].type === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                this.selectedFile = this.$refs[ref].files[0];
                this.sheetOptions = [];
                var _size = this.selectedFile.size;
                var fSExt = new Array('Bytes', 'KB', 'MB', 'GB'),
                    i = 0; while (_size > 900) { _size /= 1024; i++; }
                this.File.FileSize = (Math.round(_size * 100) / 100) + ' ' + fSExt[i];
                this.File.FileName = this.selectedFile.name;
                //kiểm tra dung lượng file
                if (this.$refs[ref].files[0].size > 30 * 1024 * 1024) {
                    this.File.Success = false;
                    this.sheetOptions = [];
                    this.exambank.RowReference = null;
                }
                else {
                    this.File.Success = true;
                    //đọc file upload lấy ra danh sách tên các sheet
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        /* Parse data */
                        const bstr = e.target.result;
                        const workbook = XLSX.read(bstr, { type: 'binary' });
                        //gán sheet mặc định là sheet đầu tiên
                        this.nameSheet = workbook.SheetNames[0];
                        //duyệt các sheet
                        for (let index = 0; index < workbook.SheetNames.length; index++) {
                            this.sheetOptions.push({
                                sheetIndex: index,
                                sheetName: workbook.SheetNames[index]
                            });
                        }
                        this.File.SheetCount = workbook.SheetNames.length;
                        this.selectedSheet = this.sheetOptions[0];
                    }
                    this.exambank.RowReference = 1;
                    reader.readAsBinaryString(this.selectedFile);
                }
            } else {
                this.contentDialog = ' File tư liệu chỉ hỗ trợ định dạng *.xlsx';
                this.dialogVisible = true;
            }
        },

        /**
         *  Validate dữ liệu
         *  @return bool
         */
        validateExamBank() {
            var invalid = true;
            if (this.exambank.ExamBankCode == null || this.exambank.ExamBankCode == '') {
                invalid = false;
                this.invalidExamBank.ExamBankCode = 'Mã đề thi không được để trống';
            }
            else {
                this.invalidExamBank.ExamBankCode = null;
            }
            if (this.exambank.ExamBankName == null || this.exambank.ExamBankName == '') {
                invalid = false;
                this.invalidExamBank.ExamBankName = 'Tên đề thi không được để trống';
            }
            else {
                this.invalidExamBank.ExamBankName = null;
            }
            if (this.exambank.RowReference == null || this.exambank.RowReference == '') {
                invalid = false;
                this.invalidExamBank.RowReference = 'Dòng tiêu đề không được để trống';
            }
            else {
                this.invalidExamBank.RowReference = null;
            }
            if (this.exambank.SheetIndexReference == null) {
                invalid = false;
                this.invalidExamBank.SheetIndexReference = 'Sheet tư liệu không được để trống';
            }
            else {
                this.invalidExamBank.SheetIndexReference = null;
            }
            if (!this.File.Success) {
                invalid = false;
            }
            return invalid;
        }
    },
    created() {
        if (this.modeModal == this.$FormMode.Update) {
            this.exambank = { ...this.selectedData };
            var SheetIndexReference = JSON.parse(this.exambank.SheetIndexReference);
            this.exambank.SheetIndexReference = SheetIndexReference[1];
            this.sheetOptions = SheetIndexReference[0];
            this.File.FileSize = this.selectedData.FileInfo;
            this.File.FileName = this.selectedData.ResourceFile;
        }
    },

    mounted() {
        this.createResumable();
    },
    computed: {
        ...mapState('popup', ['isShowModal']),
        ...mapState('popup', ['modeModal']),
        ...mapState('exambank', ['examBankData']),
    }
}
</script>

<style scoped>
@import url('../../../../public/css/components/ExamPopup.css');
</style>
