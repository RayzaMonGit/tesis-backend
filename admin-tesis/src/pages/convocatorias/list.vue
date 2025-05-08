<script setup>
const router = useRouter()
const searchQuery = ref(null);
const estado = ref(null);
const estados = ref(['Abierta', 'Cerrado', 'Borrador', 'Anulado']);
const convocatorias = ref([]);
const currentPage = ref(1);
const totalPage = ref(1);
const convocatoria_selected_deleted = ref(null);
const isDeleteConvocatoriaDialogVisible = ref(false);


const showRequisitosDialog = ref(false);
const requisitosConvocatoria = ref([]);
const convocatoriaSeleccionada = ref(null);



const list = async () => {
    try {
            // Intenta usar la API real primero
            const resp = await $api('/convocatorias?page=' + currentPage.value + 
            '&search=' + (searchQuery.value ? searchQuery.value : '') +
            '&estado=' + (estado.value ? estado.value : '')
            ,{
                method: 'GET',
                onResponseError({response}){
                  console.log("Error API:", response);
                  throw new Error("API Error");
                }
            });
            console.log("Respuesta API:", resp);

            convocatorias.value = resp.data;
totalPage.value = resp.meta.last_page;

    } catch (error) {
        console.log("Error de API:", error);
    }
}
/*
const editItem = (item) => {
    router.push({
        name: 'convocatoria-edit-id',
        params: {
            id: item.id
        }
    })
}

const deleteItem = (item) => {
    convocatoria_selected_deleted.value = item;
    isDeleteConvocatoriaDialogVisible.value = true;
}

const deleteConvocatoria = (item) => {
    let INDEX = convocatorias.value.findIndex((convocatoria) => convocatoria.id == item.id);
    if (INDEX != -1) {
        convocatorias.value.splice(INDEX, 1);
    }
}*/

const verRequisitos = async (item) => {
    convocatoriaSeleccionada.value = item;
    requisitosConvocatoria.value = []; // Reiniciar requisitos
    showRequisitosDialog.value = true; // Mostrar el diálogo inmediatamente

    try {
        const resp = await $api('/convocatorias/' + item.id + '/requisitos', {
            method: 'GET',
            onResponseError({ response }) {
                console.error("Error API requisitos:", response);
                throw new Error("API Error");
            }
        });

        console.log("Respuesta requisitos:", resp);
        
        // Manejar diferentes formatos de respuesta
        if (resp && resp.requisitos) {
            requisitosConvocatoria.value = resp.requisitos;
        } else if (Array.isArray(resp)) {
            requisitosConvocatoria.value = resp;
        } else {
            requisitosConvocatoria.value = [];
        }
    } catch (error) {
        console.error("Error al obtener requisitos:", error);
        requisitosConvocatoria.value = [];
    }
}
// Esta función reemplazaría tu actual verRequisitos
/*
const verRequisitos = async (item) => {
    convocatoriaSeleccionada.value = item;
    console.log("Convocatoria seleccionada:", item);
    //mostrar requisitos
    
    //recuperar datos de requisitos
    try {
        const resp = await $api('/convocatorias/' + item.id + '/requisitos', {
            method: 'GET',
                    onResponseError({response}){
                      console.log("Error API:", response);
                      throw new Error("API Error");
                    }
                });
        
        console.log("Respuesta requisitos:", resp);
       
    } catch (error) {
        console.error("Error al obtener requisitos:", error);
        requisitosConvocatoria.value = [];
    }
}*/

const reset = () => {
    searchQuery.value = null;
    estado.value = null;
    currentPage.value = 1;
    list();
}
/*
const avatarText = value => {
    if (!value)
        return ''
    const nameArray = value.split(' ')

    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}
*/

watch(currentPage, (val) => {
    console.log(val);
    list();
})

watch(isDeleteConvocatoriaDialogVisible, (val) => {
    if (val == false) {
        convocatoria_selected_deleted.value = null;
    }
})

watch(showRequisitosDialog, (val) => {
    if (val == false) {
        requisitosConvocatoria.value = [];
        convocatoriaSeleccionada.value = null;
    }
})
onMounted(() => {
    list()
})

definePage({
    meta: {
        permission: 'list_convocatoria'
    },
})
</script>

<template>
    <div>
        <VCard title="Convocatorias">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 busqueda  -->
                    <VTextField
                        v-model="searchQuery"
                        placeholder="Buscar convocatorias"
                        style="inline-size: 300px;"
                        density="compact"
                        class="me-3"
                        @keyup.enter="list"
                    />
                </div>
                <div>
                    <VSelect
                        :items="estados"
                        v-model="estado"
                        style="inline-size: 300px;"
                        label="Estado"
                        placeholder="Seleccionar estado"
                        eager
                    />
                </div>
                <div>
                    <VBtn
                        color="info"
                        class="mx-1"
                        prepend-icon="ri-search-2-line"
                        @click="list()"
                    >
                    </VBtn>
                    <VBtn
                        color="secondary"
                        class="mx-1"
                        prepend-icon="ri-restart-line"
                        @click="reset()"
                    >
                    </VBtn>
                </div>
                <VSpacer />

                <div class="d-flex gap-x-4 align-center">
                    <VBtn color="primary" prepend-icon="ri-add-line" @click="router.push({ name: 'convocatorias-add' })">
                        Agregar Convocatoria
                    </VBtn>
                </div>
            </VCardText>

            <VCardText class="pa-5">
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                Título
                            </th>
                            <th class="text-uppercase">
                                Descripción
                            </th>
                            <th class="text-uppercase">
                                Fecha Inicio
                            </th>
                            <th class="text-uppercase">
                                Fecha Cierre
                            </th>
                            <th class="text-uppercase">
                                Estado
                            </th>
                            <th class="text-uppercase">
                                Requisitos
                            </th>
                            <th class="text-uppercase">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <!--para ver  cada convovatoria-->
                        <tr v-for="item in convocatorias" :key="item.id">
                            <td>
                                <div class="d-flex align-center">
                                    
                                    <div class="d-flex flex-column ms-2">
                                        <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{
                                            item.titulo }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-truncate" style="max-width: 250px; display: block;">{{
                                    item.descripcion }}</span>
                            </td>
                            <td>
                                {{ new Date(item.fecha_inicio).toLocaleDateString() }}
                            </td>
                            <td>
                                {{ new Date(item.fecha_fin).toLocaleDateString() }}
                            </td>
                            <td>
                                <VChip :color="item.estado === 'Abierta' ? 'success' :
                                    item.estado === 'Cerrado' ? 'error' :
                                        item.estado === 'Borrador' ? 'warning' : 'info'" size="small">
                                    {{ item.estado }}
                                </VChip>
                            </td>
                            <td>

                                <div class="d-flex gap-1">
                                    <!--para ver los requisitos individuales de cada convovatoria-->
                                    <IconBtn size="small"  title="Ver requisitos" @click="verRequisitos(item) ">
                                        <VIcon icon="ri-file-list-3-line" />
                                    </IconBtn>
                                </div>
                            </td>


                            <td>
                                <div class="d-flex gap-1">

                                    <IconBtn size="small" title="Editar">
                                        <VIcon icon="ri-pencil-line" />
                                    </IconBtn>
                                    <IconBtn size="small" title="Eliminar">
                                        <VIcon icon="ri-delete-bin-line" />
                                    </IconBtn>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </VTable>

                <VPagination v-model="currentPage" :length="totalPage" />
            </VCardText>
            
            <VDialog v-model="showRequisitosDialog" max-width="600">
  <VCard>
    <VCardTitle>
      <VIcon icon="ri-file-list-3-line" class="me-2" />
      Requisitos de {{ convocatoriaSeleccionada?.titulo }}
    </VCardTitle>
    
    <VDivider />
    
    <VCardText>
      <VProgressCircular 
        v-if="!requisitosConvocatoria" 
        indeterminate 
        class="ma-5 d-block mx-auto"
      />
      
      <div v-else-if="requisitosConvocatoria.length === 0" class="d-flex flex-column align-center pa-5">
        <VIcon icon="ri-information-line" size="x-large" color="info" class="mb-4" />
        <p class="text-h6 text-center">No hay requisitos registrados para esta convocatoria.</p>
      </div>
      
      <VList v-else density="compact">
        <VListItem v-for="req in requisitosConvocatoria" :key="req.id">
          <template #prepend>
            <VIcon 
              :icon="req.tipo && req.tipo.toLowerCase() === 'obligatorio' ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line'" 
              :color="req.tipo && req.tipo.toLowerCase() === 'obligatorio' ? 'error' : 'info'"
              class="me-2"
            />
          </template>
          <VListItemTitle class="mb-1">{{ req.descripcion }}</VListItemTitle>
          <template #append>
            <VChip
              size="small" 
              :color="req.tipo && req.tipo.toLowerCase() === 'obligatorio' ? 'error' : 'info'" 
              variant="outlined"
              class="ms-2"
            >
              {{ req.tipo || 'No especificado' }}
            </VChip>
          </template>
        </VListItem>
      </VList>
    </VCardText>
    
    <VCardActions>
      <VSpacer />
      <VBtn color="primary" @click="showRequisitosDialog = false">
        Cerrar
      </VBtn>
    </VCardActions>
  </VCard>
</VDialog>

           
        </VCard>
    </div>
</template>

<style>
.v-btn__prepend {
    margin-inline: 0 !important;
}
</style>