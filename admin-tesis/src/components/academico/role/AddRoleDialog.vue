<script setup>
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
})

const emit = defineEmits(['update:isDialogVisible'])


const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}


const LIST_PERMISSIONS = PERMISOS;
</script>

<template> <!-- añadir roles-->
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        Agregar un nuevo rol
                    </h4>
                </div>
                <VTextField label="Rol:" placeholder="Ejemplo: Administrador" />

            </VCardText>
            <VCardText class="pa-5">
                <!-- tabla de roles-->
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                Módulo
                            </th>
                            <th class="text-uppercase">
                                Roles
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(item, index) in LIST_PERMISSIONS" :key="index">
                            <td>
                                {{ item.name }}
                            </td>
                            <td>
                                <ul>
                                    <li v-for="(permiso, index2) in item.permisos" :key="index2" style="list-style: none;">
                                        <VCheckbox :label="permiso.name" :value="permiso.permiso" />
                                    </li>
                                </ul>
                            </td>

                        </tr>
                    </tbody>
                </VTable>
                <!-- boton crear rol-->
                <VBtn>
                    Crear rol
                    <VIcon end icon="ri-checkbox-circle-line" />
                </VBtn>

            </VCardText>
        </VCard>
    </VDialog>
</template>

<style lang="scss">
.refer-link-input {
    .v-field--appended {
        padding-inline-end: 0;
    }

    .v-field__append-inner {
        padding-block-start: 0.125rem;
    }
}
</style>
