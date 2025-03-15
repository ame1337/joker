<template>
    <div class="alert position-fixed end-0 bottom-0 mb-2 me-2"
        :class="this.type" v-show="show">
        <i class="fas fa-exclamation-circle"></i> <span v-text="this.message"></span>
    </div>
</template>

<script>
    export default {
        props: ['message', 'type'],

        data() {
            return {
                show: true
            }
        },

        created() {
            this.hide();
        },

        methods: {
            hide() {
                setTimeout(() => {
                    this.show = false;
                }, 4000);
            }
        },
    }
</script>
