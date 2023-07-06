<template>
    <div class="tooltip vue-tooltip-theme" x-placement="top" aria-hidden="true">
        <div class="position-relative v-tooltip">
            <div class="tooltip-arrow"></div>
            <div class="tooltip-inner">{{ tooltipTitle }}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        tooltipTitle: {
            default: '',
        }
    },
}
</script>

<style scoped>
@import url('../../../../public/css/base/base-tooltip.css');
</style>
