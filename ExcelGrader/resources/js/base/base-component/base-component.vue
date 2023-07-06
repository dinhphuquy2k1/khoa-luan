<template>
    <div class="main-view flex1 flex-column">
        <div class="form-list flex-column flex1">
            <div class="flex-column flex1">
                <div class="flex-row title-box">
                    <div class="list-title flex1">Danh sách đề thi</div>
                </div>
                <div class="flex-row toolbar-box">
                    <div class="left-toolbar flex-row">
                        <div class="ms-input ms-editor w-100 search-box mr-2">
                            <div class="flex-row border">
                                <input type="text" class="ms-input-item flex1" placeholder="Tìm kiếm...">
                                <div class="icon24 error error-icon" style="display: none;"></div>
                                <div class="icon24 icon search-right search"></div>
                            </div>
                        </div>
                    </div>
                    <div class="right-toolbar flex-row">
                        <button class="ms-button btn primary" @click="toggleModal($FormMode.Insert)">
                            <div class="icon24 icon left add-white"></div>
                            <div class="text pl-0">Thêm đề thi</div>
                        </button>
                    </div>
                </div>
                <div class="box list-content flex1 flex-column">
                    <BaseTable :dataTable="products" key="field" value="header" :columns="columns"
                        v-model:selectedData="selectedData" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState, mapActions } from 'vuex'
import BaseTable from './base-table.vue';
import ExamPopup from '../../components/exam-bank/ExamPopup.vue';
export default {
    name: "BaseComponent",
    props: {
        popupContent: {
            default: 'popupContent'
        }
    },
    components: {
        BaseTable,
        ExamPopup
    },
    data() {
        return {
            products: [
                { field: 'code', header: 'Code', property: 'Phú Quý' },
                { field: 'name', header: 'Name', property: 'Phú Quý 1' },
                { field: 'category', header: 'Category', property: 'Phú Quý 2' },
                { field: 'quantity', header: 'Quantity', property: 'Phú Quý 3' }
            ],
            selectedData: [],

            columns: [
                {
                    field: 'field',
                    title: 'Mã đề thi',
                    type: "Number",
                    width: 40,
                },
                {
                    field: 'property',
                    title: 'Tên đề thi',
                    type: "Number",
                    width: 180,
                }
            ]
        }
    },
    methods: {
        ...mapActions('popup', ['toggleModal']),
    },
    computed: {
        ...mapState('popup', ['isShowModal']),
    },
}
</script>

<style scoped>
@import url('../../../../public/css/base/base-component.css');
</style>
