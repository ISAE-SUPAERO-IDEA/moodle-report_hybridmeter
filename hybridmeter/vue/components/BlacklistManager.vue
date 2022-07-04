<template>
    <div id="blacklist">
        <Category :id=0 :expanded=true :root=true />
    </div>
</template>

<script>
import Category from './blacklist/Category.vue';

export default {
    components : { Category },
    name : "BlacklistManager",
}
</script>
