<script setup>
//import DeleteRoleDialog from '@/components/academico/role/DeleteRoleDialog.vue';
//import EditRoleDialog from '@/components/academico/role/EditRoleDialog.vue';
import { watch } from 'vue';

//import data from '@/views/js/datatable'
const data = ref([]);
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Avatar', key: 'imagen' },
  { title: 'Nombre y Apellido', key: 'full_name' },
  { title: 'Telefono', key: 'telefono' },
  { title: 'Email', key: 'email' },
  { title: 'Op', key: 'action' },
]

const searchQuery = ref(null);
const isAddStaffDialogVisible = ref(false);
const isEditstaffDialogVisible = ref(false);
const isDeletestaffDialogVisible = ref(false);
const staff_Selected = ref(null);
const staff_Selected_deleted = ref(null);
const list = async () => {
        const resp =  await $api('/staffs?search='+(searchQuery.value ? searchQuery.value : ''),{
            method: 'GET',
            onResponseError({response}){
              console.log(response);
            }
        })
        console.log(resp);

        data.value = resp.users.data;
        console.log(resp.users.data);
       // roles.value = resp.roles;
    }
const editItem = (item) => {
  isEditstaffDialogVisible.value = true;
  staff_Selected.value = item;
}
const deleteItem = (item) => {
  isDeletestaffDialogVisible.value = true;
  staff_Selected_deleted.value = item;
}
onMounted(() => {
  list();
})
watch(isEditstaffDialogVisible, (event) => {
  console.log(event);
  if (event == false) {
    staff_Selected.value = null;
  }
})
watch(isDeletestaffDialogVisible, (event) => {
  console.log(event);
  if (event == false) {
    staff_Selected_deleted.value = null;
  }
})
</script>

<template>
  <div>
    <h1>"Usuarios"</h1>
    <VCard title="Usuarios del sistema">

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="d-flex align-center">
          <!--  lupa  -->
          <VTextField v-model="searchQuery" placeholder="Search Staff" style="inline-size: 300px;" density="compact"
            class="me-3" @keyup.enter="list" />
        </div>

        <VSpacer />

        <div class="d-flex gap-x-4 align-center">

          <VBtn color="primary" prepend-icon="ri-add-line" @click="isAddStaffDialogVisible = !isAddStaffDialogVisible">
            Añadir usuario
          </VBtn>
        </div>
        </VCardText>

        <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">

          <template #item.id="{ item }">
            <span class="text-h6">{{ item.id }}</span>
          </template>

          <template #item.imagen="{ item }">
                    <div class="d-flex align-center">
                        <VAvatar
                        size="32"
                        :color="item.avatar ? '' : 'primary'"
                        :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
                        :variant="!item.avatar ? 'tonal' : undefined"
                        >
                        <VImg
                            v-if="item.avatar"
                            :src="item.avatar"
                        />
                            <span
                                v-else
                                class="text-sm"
                                >{{ avatarText(item.full_name) }}</span>
                           
                        </VAvatar> 
                        <!-- <div class="d-flex flex-column ms-3">
                        <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
                        <small>{{ item.post }}</small>
                        </div> -->
                    </div>
                </template>

          <template #item.action="{ item }">
            <div class="d-flex gap-1">
              <!--boton editar-->
              <IconBtn size="small" @click="editItem(item)">
                <VIcon icon="ri-pencil-line" />
              </IconBtn>
              <!--boton borrar-->
              <IconBtn size="small" @click="deleteItem(item)">
                <VIcon icon="ri-delete-bin-line" />
              </IconBtn>
            </div>
          </template>
        </VDataTable>
        <!-- <AddStaffDialog v-model:is-dialog-visible="isAddStaffDialogVisible" @addStaff="list()" />
      <EditStaffDialog v-if="staff_Selected" :rolSelected="staff_Selected"
        v-model:is-dialog-visible="isEditstaffDialogVisible" @editStaff="list()" />
      <DeleteStaffDialog v-if="staff_Selected_deleted" :rolSelected="staff_Selected_deleted"
        v-model:is-dialog-visible="isDeletestaffDialogVisible" @deleteStaff="list()" />
-->
    </VCard>



  </div>
</template>
