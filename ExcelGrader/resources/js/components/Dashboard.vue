<template>
    <div class="main-view flex1 flex-column position-relative">
        <div class="form-list flex-column h-100 dashboard">
            <div class="flex-row title-box">
                <div class="list-title flex">Tổng quan</div>
            </div>
            <div class="content-container">
                <div class="card-container">
                    <div class="card blue">
                        <div class="header">Ngân hàng đề thi</div>
                        <div class="body">{{ countExamBank }}</div>
                    </div>
                    <div class="card green">
                        <div class="header">Kì thi</div>
                        <div class="body">{{ countExam }}</div>
                    </div>
                    <div class="card purple">
                        <div class="header">Phòng thi</div>
                        <div class="body">{{ countDepartment }}</div>
                    </div>
                    <div class="card orange">
                        <div class="header">Người dùng</div>
                        <div class="body">{{ countUser }}</div>
                    </div>
                </div>
                <div class="flex-column flex-text group-chart">
                    <div class="chart-container" style="margin-bottom: 16px;">
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate"> Tổng hợp kết quả điểm </div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                            class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate">Theo kì thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="doughnut" :data="chartData" :options="chartOptions" :plugins="plugins"
                                            class="w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate">Thống kê ngân hàng đề thi</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                            class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate"> Dữ liệu về ngân hàng đề thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="bar" :data="chartDataExam" :options="chartOptionsExam"
                                            class="w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="chart-container">
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate">Thống kê ngân hàng đề thi</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                            class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate"> Dữ liệu về ngân hàng đề thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="bar" :data="chartDataExam" :options="chartOptionsExam"
                                            class="w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate">Thống kê ngân hàng đề thi</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                            class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate"> Dữ liệu về ngân hàng đề thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="bar" :data="chartDataExam" :options="chartOptionsExam"
                                            class="w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <TheLoadingProgress v-if="isLoading" :useBackground="true" />
    </div>
</template>

<script>
import Chart from 'primevue/chart';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import TheLoadingProgress from './TheLoadingProgress.vue';
import { getDashBoardData } from '../../api/dashboard'
export default {
    components: {
        Chart,
        TheLoadingProgress
    },

    data() {
        return {
            isLoading: false,
            countExamBank: 0,
            countExam: 0,
            countDepartment: 0,
            countUser: 0,
            plugins: [ChartDataLabels],
            chartData: {
                labels: ['Dưới 5 điểm', 'Từ 5 - 6.5 điểm', 'Từ 6.5 - 8 điểm', 'Từ 8 - 9 điểm', 'Từ 9 - 10 điểm'],
                datasets: [
                    {
                        data: null,
                        backgroundColor: [
                            'rgb(255, 109, 0)',
                            'rgba(255, 159, 64)',
                            'rgb(68, 138, 255)',
                            '#5b4dff',
                            'rgb(53, 189, 123)',
                        ],

                        hoverOffset: 10,
                    }
                ],
            },

            chartOptions: {
                plugins: {
                    datalabels: {
                        color: '#fff',
                        textAlign: 'center',
                        font: {
                            size: 13,
                            align: 'center',
                            family: "GoogleSans",
                        },
                    },

                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function (context) {
                                var label = context.dataset.data[context.dataIndex] || '';
                                if (label) {
                                    label += ': ';
                                }
                                var percentage = ((context.raw / context.chart.data.datasets[0].data.reduce((a, b) => a + b)) * 100).toFixed(2);
                                return percentage + '%';
                            }
                        }
                    },
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            font: {
                                size: 14,
                                family: "GoogleSans"
                            },
                            useBorderRadius: true,
                            borderRadius: 5
                        },
                        textDirection: 'rtl'
                    },

                },
                cutout: '50%',
                responsive: true,
                cutoutPercentage: 50,
                maintainAspectRatio: false,
                layout: {
                    padding: 20
                },
            },

            chartDataExam: {
                labels: ['Đề thi chưa được thiết lập', 'Đề thi được sử dụng'],
                datasets: [
                    {
                        label: '',
                        data: [0, 0],
                        backgroundColor: [
                            'rgba(255, 159, 64)',
                            '#5b4dff',
                        ],
                        minBarLength: 2,
                        barThickness: 30,
                        hoverOffset: 10,
                    }
                ],
            },

            chartOptionsExam: {
                plugins: {
                    datalabels: {
                        color: '#fff',
                        textAlign: 'center',
                        font: {
                            size: 13,
                            align: 'center',
                            family: "GoogleSans",
                        },
                    },

                    legend: {
                        position: 'top',
                        align: 'center',
                        labels: {
                            font: {
                                size: 14,
                                family: "GoogleSans"
                            },
                            useBorderRadius: true,
                            borderRadius: 5
                        },
                        textDirection: 'rtl'
                    },

                },
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 20
                },

                scales: {

                    y: {
                        min: 0,
                        max: 10,
                        ticks: {
                            stepSize: 2,
                        }
                    }
                }
            }

        }
    },

    methods: {

        setChartData(data) {
            const documentStyle = getComputedStyle(document.body);

            this.chartData = 1;
        },

        loadData() {
            this.isLoading = true;
            getDashBoardData().then(res => {
                this.countDepartment = res.Department;
                this.countExam = res.Exam;
                this.countExamBank = res.ExamBank;
                this.countUser = res.User;
                this.chartData.datasets[0].data = [res.Score005, res.Score565, res.Score658, res.Score809, res.Score910];
                this.chartDataExam.datasets[0].data = [res.ExamBankNoUse, res.ExamBankUse];
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            });
        },
    },

    created() {
        this.loadData();
    },

    mounted() {
    },
}
</script>

<style scoped>
@import url('../../../public/css/components/dashboard.css');

.p-chart-legend {
    position: absolute;
    bottom: 0;
    left: 100%;
}
</style>
