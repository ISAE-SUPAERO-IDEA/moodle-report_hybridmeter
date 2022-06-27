<template>
    <div id="coucou">
        <p>Salut, lorem ipsum etc etc</p>
        <Composantdisons bene="hehehehe" />
    </div>
</template>

<script>
    import Composantdisons from './Composantdisons.vue';
    export default {
        components: { Composantdisons },
        name: "test",
    }
</script>