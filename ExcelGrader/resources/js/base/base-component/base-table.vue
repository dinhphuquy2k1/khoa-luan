<template>
    <div class="ms-grid-viewer flex1 flex-box flex-column">
        <div class="ms-content--table flex1 scroller">
            <table cellspacing="0" cellpadding="0" class="ms-table">
                <thead class="ms-thead">
                    <tr class="ms-tr tr-1">
                        <th class="ms-th multiple-cell sticky" style="left: 0px;"></th>
                        <th class="ms-th" v-for="header in columns" :style="{
                            'min-width': header.width + 'px'
                        }">
                            <div class="ms-th-content flex-row">
                                <div class="ms-th-title flex1">
                                    <p class="text-left">{{ header.title }}</p>
                                </div>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="ms-tbody data">
                    <tr class="ms-tr action-pointer" v-for="item, index in dataTable" @mouseover="selectedItem = item"
                        @mouseleave="selectedItem = false, isShowActions = false" @click="isSelectedRow = item"
                        @dblclick="$emit('update:selectedData', item), toggleModal($FormMode.Update)"
                        :class="{ 'row-selected': isSelectedRow == item }">
                        <td class="ms-td multiple-cell sticky"></td>
                        <td class="ms-td ms-col-td" v-for="column in columns">
                            <div class="text-left text-overflow">
                                <span class="text-left">{{ item[column.field] }}</span>
                            </div>
                        </td>
                        <div class="row-actions" v-if="selectedItem == item" @dblclick.stop="unDbl">
                            <div class="position-relative">
                                <div class="item"
                                    @click="$emit('update:selectedData', item), toggleModal($FormMode.Update)">
                                    <div class="v-popover popover">
                                        <div class="trigger">
                                            <div class="icon24 edit"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item" @click="isShowActions = !isShowActions">
                                    <div class="v-popover popover">
                                        <div class="icon24 more"></div>
                                    </div>
                                </div>
                                <ul class="v-context" v-if="isShowActions">
                                    <li class="li-active" @click="toggleConfigureExam(item)">
                                        <div class="action-menu menu-item">
                                            <div>Thiết lập đề</div>
                                        </div>
                                    </li>
                                    <li class="li-Delete">
                                        <div class="action-menu menu-item text-danger">
                                            <div>Xóa</div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="ms-pagination flex-row">
            <div class="d-flex h-20 pr-12">
                <div class="min-page-caption">Tổng số: </div>
                <div class="total">
                    <span>11</span>
                </div>
                <div class="min-page-caption">bản ghi</div>
            </div>
            <div class="flex1"></div>
            <div class="d-flex h-40">
                <span class="min-page-caption">Số bản ghi trên trang</span>
                <div class="page-size"></div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
export default {
    name: "BaseTable",
    props: {
        dataTable: {
            default: [],
        },
        // Xác định cột cho table
        columns: {
            default: [],
        },
        modelValue: {
            default: [],
        },
        selectedData: {
            default: [],
        }
    },
    data() {
        return {
            selectedItem: false,
            isShowActions: false,
            isSelectedRow: false,
        }
    },
    methods: {
        ...mapActions('popup', ['toggleModal']),
        ...mapActions('exambank', ['getConfigureExam']),
        ...mapActions('exambank', ['toggleConfigureExam']),
    }
}
</script>
