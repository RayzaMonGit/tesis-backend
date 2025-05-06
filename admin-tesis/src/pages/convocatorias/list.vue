<script setup>
const router = useRouter()
const searchQuery = ref(null);
const estado = ref(null);
const estados = ref(['Abierta', 'Cerrada', 'Borrador', 'Finalizada']);
const convocatorias = ref([]);
const currentPage = ref(1);
const totalPage = ref(1);
const convocatoria_selected_deleted = ref(null);
const isDeleteConvocatoriaDialogVisible = ref(false);
const showRequisitosDialog = ref(false);
const requisitosConvocatoria = ref([]);
const convocatoriaSeleccionada = ref(null);

// Datos de ejemplo para desarrollo mientras la API esté en desarrollo
const convocatoriasMock = [
    {
        id: 1,
        titulo: 'Convocatoria para Becas Universitarias',
        descripcion: 'Becas para estudiantes de pregrado con alto rendimiento académico',
        fecha_inicio: '2025-05-01',
        fecha_fin: '2025-06-15',
        estado: 'Abierta',
        imagen: null
    },
    {
        id: 2,
        titulo: 'Fondos para Investigación',
        descripcion: 'Fondos destinados a proyectos de investigación científica',
        fecha_inicio: '2025-04-15',
        fecha_fin: '2025-05-30',
        estado: 'Borrador',
        imagen: null
    },
    {
        id: 3,
        titulo: 'Concurso de Innovación Tecnológica',
        descripcion: 'Para startups y emprendedores con ideas innovadoras',
        fecha_inicio: '2025-03-10',
        fecha_fin: '2025-04-10',
        estado: 'Cerrada',
        imagen: null
    },
    {
        id: 4,
        titulo: 'Subsidio para Proyectos Comunitarios',
        descripcion: 'Apoyo financiero para iniciativas que beneficien a comunidades locales',
        fecha_inicio: '2025-06-01',
        fecha_fin: '2025-07-15',
        estado: 'Finalizada',
        imagen: null
    }
];

const requisitosMock = {
    1: [
        { id: 1, nombre: 'Certificado académico', descripcion: 'Promedio mínimo de 8.0', obligatorio: true },
        { id: 2, nombre: 'Carta de motivación', descripcion: 'Explicar por qué merece la beca', obligatorio: true },
        { id: 3, nombre: 'Situación socioeconómica', descripcion: 'Documentos que acrediten situación familiar', obligatorio: true },
        { id: 4, nombre: 'Actividades extracurriculares', descripcion: 'Participación en eventos académicos', obligatorio: false }
    ],
    2: [
        { id: 5, nombre: 'Propuesta de investigación', descripcion: 'Documento detallado con objetivos', obligatorio: true },
        { id: 6, nombre: 'CV del investigador principal', descripcion: 'Experiencia y publicaciones previas', obligatorio: true },
        { id: 7, nombre: 'Presupuesto detallado', descripcion: 'Desglose de gastos previstos', obligatorio: true },
        { id: 8, nombre: 'Cartas de recomendación', descripcion: 'Al menos dos referencias académicas', obligatorio: false }
    ],
    3: [
        { id: 9, nombre: 'Plan de negocio', descripcion: 'Modelo de negocio y proyecciones', obligatorio: true },
        { id: 10, nombre: 'Prototipo o demo', descripcion: 'Versión funcional del producto', obligatorio: true },
        { id: 11, nombre: 'Equipo de trabajo', descripcion: 'CV de los miembros del equipo', obligatorio: true },
        { id: 12, nombre: 'Patentes relacionadas', descripcion: 'Si existen patentes previas', obligatorio: false }
    ],
    4: [
        { id: 13, nombre: 'Descripción del proyecto', descripcion: 'Objetivos y beneficiarios', obligatorio: true },
        { id: 14, nombre: 'Impacto comunitario', descripcion: 'Beneficios esperados para la comunidad', obligatorio: true },
        { id: 15, nombre: 'Cronograma de actividades', descripcion: 'Planificación temporal del proyecto', obligatorio: true },
        { id: 16, nombre: 'Sostenibilidad', descripcion: 'Plan para continuidad después del subsidio', obligatorio: true }
    ]
};

const list = async () => {
    try {
        // Intenta usar la API real primero
        const resp = await $api('/convocatorias?page=' + currentPage.value +
  '&search=' + (searchQuery.value ? searchQuery.value : '') +
  '&estado=' + (estado.value ? estado.value : '')

            , {
                method: 'GET',
                onResponseError({ response }) {
    console.error("Error API:", response);
    throw new Error(response._data?.message || 'Error al obtener las convocatorias');
}

            });
            console.log("Respuesta API:", resp);

        totalPage.value = resp.total_page;
        convocatorias.value = resp.convocatorias.data;
    } catch (error) {
        console.log("Usando datos mock debido a error de API:", error);
/*
        // Filtrado local de datos mock
        let filteredData = [...convocatoriasMock];

        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            filteredData = filteredData.filter(item =>
                item.titulo.toLowerCase().includes(query) ||
                item.descripcion.toLowerCase().includes(query)
            );
        }

        if (estado.value) {
            filteredData = filteredData.filter(item =>
                item.estado === estado.value
            );
        }

        // Simular paginación
        totalPage.value = 1;  // Con pocos datos mock solo necesitamos 1 página
        convocatorias.value = filteredData;*/
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
}
*/
/*const verRequisitos = async (item) => {
    convocatoriaSeleccionada.value = item;

    try {
        // Intenta usar la API real primero
        const resp = await $api('/convocatorias/' + item.id + '/requisitos', {
            method: 'GET',
            onResponseError({ response }) {
                console.log("Error API requisitos:", response);
                throw new Error("API Error");
            }
        });
        requisitosConvocatoria.value = resp.requisitos;
    } catch (error) {
        console.log("Usando datos mock de requisitos debido a error de API:", error);
        // Usar datos mock para los requisitos
        requisitosConvocatoria.value = requisitosMock[item.id] || [];
    }

    showRequisitosDialog.value = true;
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
*/
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
                    <VTextField v-model="searchQuery" placeholder="Buscar convocatorias" style="inline-size: 300px;"
                        density="compact" class="me-3" @keyup.enter="list" />
                </div>
                <!-- 👉 busqueda por requisitos  -->
                <div>
                    <VSelect :items="estados" v-model="estado" style="inline-size: 300px;" label="Estado"
                        placeholder="Seleccionar estado" eager />
                </div>
                <div>
                    <!-- 👉 lupa  -->
                    <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="list()">
                    </VBtn>
                    <!-- 👉 recargar  -->
                    <VBtn color="secondary" class="mx-1" prepend-icon="ri-restart-line" @click="reset()">
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
                                    item.estado === 'Cerrada' ? 'error' :
                                        item.estado === 'Borrador' ? 'warning' : 'info'" size="small">
                                    {{ item.estado }}
                                </VChip>
                            </td>
                            <td>

                                <div class="d-flex gap-1">
                                    <!--para ver los requisitos individuales de cada convovatoria-->
                                    <IconBtn size="small"  title="Ver requisitos">
                                        <VIcon icon="ri-file-list-3-line" />
                                    </IconBtn>
                                </div>
                            </td>


                            <td>
                                <div class="d-flex gap-1">

                                    <IconBtn size="small" @click="editItem(item)" title="Editar">
                                        <VIcon icon="ri-pencil-line" />
                                    </IconBtn>
                                    <IconBtn size="small" @click="deleteItem(item)" title="Eliminar">
                                        <VIcon icon="ri-delete-bin-line" />
                                    </IconBtn>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </VTable>

                <VPagination v-model="currentPage" :length="totalPage" />
            </VCardText>

           
        </VCard>
    </div>
</template>

<style>
.v-btn__prepend {
    margin-inline: 0 !important;
}
</style>