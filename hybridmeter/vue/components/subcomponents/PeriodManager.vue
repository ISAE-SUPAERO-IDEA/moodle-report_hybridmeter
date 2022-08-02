<template>
    <div id="plage" class="management-module">
        <div class="form-item row">
            <!-- Beginning date -->
            <div class="form-label col-sm-3 text-sm-right">
                <label></label>
            </div>
            <div class="form-setting col-sm-9">
                <div class="form-text defaultsnext">
                    <input type="date"  v-model="begin_date"/>
                </div>
            </div>
            <!-- End date -->
            <div class="form-item row">
            <div class="form-label col-sm-3 text-sm-right">
                <label></label>
            </div>
            <div class="form-setting col-sm-9">
                <div class="form-text defaultsnext">
                    <input type="date"  v-model="end_date">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default{
    setup() {
        const begin_date = ref(undefined)
        const end_date = ref(undefined)

        
    },
}
</script>
