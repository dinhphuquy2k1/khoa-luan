<template>
    <div class="flex1">
        <div class="ms-input ms-editor w-100" :style="{ 'width': width + 'px' }">
            <div class="flex-row border">
                <input :type="typeInput" ref="inputText" class="ms-input-item flex1" :placeholder="placeholder"
                    v-model.trim="modelValue" @input="$emit('update:modelValue', modelValue)" :tabindex="tabindex">
                <div class="icon24 error error-icon" v-if="inputIcon"></div>
            </div>
            <div class="error-text" v-if="errorText != ''">{{ errorText }}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        errorText: {
            default: ''
        },
        inputIcon: {
            default: false,
        },
        placeholder: {
            default: '',
        },
        width: {
            default: 200
        },
        modelValue: {
            default: null,
        },
        tabindex: {
            type: String,
            default: null,
        },
        autofocus: {
            type: Boolean,
            default: false,
        },
        typeInput: {
            type: String,
            default: 'text',
        }
    },
    data() {
        return {

        }
    },
    created() {
        if (this.autofocus) {

            // //focus vào ô nhập liệu đầu tiên
            this.$nextTick(() => {
                this.$refs.inputText.focus();
            })
        }
    }
}
</script>
