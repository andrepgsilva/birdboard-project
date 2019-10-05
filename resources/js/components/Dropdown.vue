<template>
    <div class="dropdown">
        <!-- trigger -->
        <slot name="trigger"></slot>

        <!-- menu links -->
        <div class="dropdown-menu">
            <slot>

            </slot>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false,
        }
    }
}
</script>