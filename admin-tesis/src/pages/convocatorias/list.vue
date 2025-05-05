<script setup>
    const router = useRouter()
    const searchQuery = ref(null);
    const estado = ref(null);
    const estados = ref(['Abierta', 'Cerrada', 'En evaluación', 'Finalizada']);
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
            fecha_cierre: '2025-06-15',
            estado: 'Abierta',
            imagen: null
        },
        {
            id: 2,
            titulo: 'Fondos para Investigación',
            descripcion: 'Fondos destinados a proyectos de investigación científica',
            fecha_inicio: '2025-04-15',
            fecha_cierre: '2025-05-30',
            estado: 'En evaluación',
            imagen: null
        },
        {
            id: 3,
            titulo: 'Concurso de Innovación Tecnológica',
            descripcion: 'Para startups y emprendedores con ideas innovadoras',
            fecha_inicio: '2025-03-10',
            fecha_cierre: '2025-04-10',
            estado: 'Cerrada',
            imagen: null
        },
        {
            id: 4,
            titulo: 'Subsidio para Proyectos Comunitarios',
            descripcion: 'Apoyo financiero para iniciativas que beneficien a comunidades locales',
            fecha_inicio: '2025-06-01',
            fecha_cierre: '2025-07-15',
            estado: 'Finalizada',
            imagen: null
        }
    ];

    const requisitosMock = {
        1: [
            { id: 1, nombre: 'Certificado académico', descripcion: 'Promedio mínimo de 8.0', obligatorio: true, peso: 30 },
            { id: 2, nombre: 'Carta de motivación', descripcion: 'Explicar por qué merece la beca', obligatorio: true, peso: 25 },
            { id: 3, nombre: 'Situación socioeconómica', descripcion: 'Documentos que acrediten situación familiar', obligatorio: true, peso: 35 },
            { id: 4, nombre: 'Actividades extracurriculares', descripcion: 'Participación en eventos académicos', obligatorio: false, peso: 10 }
        ],
        2: [
            { id: 5, nombre: 'Propuesta de investigación', descripcion: 'Documento detallado con objetivos', obligatorio: true, peso: 40 },
            { id: 6, nombre: 'CV del investigador principal', descripcion: 'Experiencia y publicaciones previas', obligatorio: true, peso: 30 },
            { id: 7, nombre: 'Presupuesto detallado', descripcion: 'Desglose de gastos previstos', obligatorio: true, peso: 20 },
            { id: 8, nombre: 'Cartas de recomendación', descripcion: 'Al menos dos referencias académicas', obligatorio: false, peso: 10 }
        ],
        3: [
            { id: 9, nombre: 'Plan de negocio', descripcion: 'Modelo de negocio y proyecciones', obligatorio: true, peso: 35 },
            { id: 10, nombre: 'Prototipo o demo', descripcion: 'Versión funcional del producto', obligatorio: true, peso: 40 },
            { id: 11, nombre: 'Equipo de trabajo', descripcion: 'CV de los miembros del equipo', obligatorio: true, peso: 15 },
            { id: 12, nombre: 'Patentes relacionadas', descripcion: 'Si existen patentes previas', obligatorio: false, peso: 10 }
        ],
        4: [
            { id: 13, nombre: 'Descripción del proyecto', descripcion: 'Objetivos y beneficiarios', obligatorio: true, peso: 30 },
            { id: 14, nombre: 'Impacto comunitario', descripcion: 'Beneficios esperados para la comunidad', obligatorio: true, peso: 35 },
            { id: 15, nombre: 'Cronograma de actividades', descripcion: 'Planificación temporal del proyecto', obligatorio: true, peso: 20 },
            { id: 16, nombre: 'Sostenibilidad', descripcion: 'Plan para continuidad después del subsidio', obligatorio: true, peso: 15 }
        ]
    };

    const list = async() => {
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
            
            totalPage.value = resp.total_page;
            convocatorias.value = resp.convocatorias.data;
        } catch (error) {
            console.log("Usando datos mock debido a error de API:", error);
            
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
            convocatorias.value = filteredData;
        }
    }

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
        if(INDEX != -1){
            convocatorias.value.splice(INDEX, 1);
        }
    }

    const verRequisitos = async(item) => {
        convocatoriaSeleccionada.value = item;
        
        try {
            // Intenta usar la API real primero
            const resp = await $api('/convocatorias/' + item.id + '/requisitos', {
                method: 'GET',
                onResponseError({response}){
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
    }

    const reset = () => {
        searchQuery.value = null;
        estado.value = null;
        currentPage.value = 1;
        list();
    }

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
        if(val == false){
            convocatoria_selected_deleted.value = null;
        }
    })

    watch(showRequisitosDialog, (val) => {
        if(val == false){
            requisitosConvocatoria.value = [];
            convocatoriaSeleccionada.value = null;
        }
    })

    onMounted(() => {
        list()
    })

    definePage({
        meta: {
            permisssion: 'list_convocatoria'
        },
    })
</script>

<template>
    <div>
        <VCard title="Convocatorias">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
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
                    <VBtn
                        color="primary"
                        prepend-icon="ri-add-line"
                        @click="router.push({name: 'convocatoria-add'})"
                    >
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
                                Acciones
                            </th>
                        </tr>
                    </thead>
    
                    <tbody>
                        <tr
                            v-for="item in convocatorias"
                            :key="item.id"
                        >
                            <td>
                                <div class="d-flex align-center">
                                    <VAvatar
                                        size="32"
                                        :color="item.imagen ? '' : 'primary'"
                                        :class="item.imagen ? '' : 'v-avatar-light-bg primary--text'"
                                        :variant="!item.imagen ? 'tonal' : undefined"
                                    >
                                        <VImg
                                            v-if="item.imagen"
                                            :src="item.imagen"
                                        />
                                        <span
                                            v-else
                                            class="text-sm"
                                        >{{ avatarText(item.titulo) }}</span>
                                    </VAvatar> 
                                    <div class="d-flex flex-column ms-3">
                                        <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.titulo }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-truncate" style="max-width: 250px; display: block;">{{ item.descripcion }}</span>
                            </td>
                            <td>
                                {{ new Date(item.fecha_inicio).toLocaleDateString() }}
                            </td>
                            <td>
                                {{ new Date(item.fecha_cierre).toLocaleDateString() }}
                            </td>
                            <td>
                                <VChip
                                    :color="item.estado === 'Abierta' ? 'success' : 
                                           item.estado === 'Cerrada' ? 'error' : 
                                           item.estado === 'En evaluación' ? 'warning' : 'info'"
                                    size="small"
                                >
                                    {{ item.estado }}
                                </VChip>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <IconBtn
                                        size="small"
                                        @click="verRequisitos(item)"
                                        title="Ver requisitos"
                                    >
                                        <VIcon icon="ri-file-list-3-line" />
                                    </IconBtn>
                                    <IconBtn
                                        size="small"
                                        @click="editItem(item)"
                                        title="Editar"
                                    >
                                        <VIcon icon="ri-pencil-line" />
                                    </IconBtn>
                                    <IconBtn
                                        size="small"
                                        @click="deleteItem(item)"
                                        title="Eliminar"
                                    >
                                        <VIcon icon="ri-delete-bin-line" />
                                    </IconBtn>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </VTable>

                <VPagination
                    v-model="currentPage"
                    :length="totalPage"
                />
            </VCardText>

            <!-- Modal para ver los requisitos -->
            <VDialog
                v-model="showRequisitosDialog"
                max-width="800"
            >
                <VCard v-if="convocatoriaSeleccionada">
                    <VCardTitle>
                        Requisitos para: {{ convocatoriaSeleccionada.titulo }}
                    </VCardTitle>
                    <VCardText>
                        <VTable>
                            <thead>
                                <tr>
                                    <th class="text-uppercase">Requisito</th>
                                    <th class="text-uppercase">Descripción</th>
                                    <th class="text-uppercase">Obligatorio</th>
                                    <th class="text-uppercase">Peso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="requisito in requisitosConvocatoria" :key="requisito.id">
                                    <td>{{ requisito.nombre }}</td>
                                    <td>{{ requisito.descripcion }}</td>
                                    <td>
                                        <VChip
                                            :color="requisito.obligatorio ? 'success' : 'info'"
                                            size="small"
                                        >
                                            {{ requisito.obligatorio ? 'Sí' : 'No' }}
                                        </VChip>
                                    </td>
                                    <td>{{ requisito.peso }}</td>
                                </tr>
                                <tr v-if="!requisitosConvocatoria.length">
                                    <td colspan="4" class="text-center">
                                        No hay requisitos registrados para esta convocatoria
                                    </td>
                                </tr>
                            </tbody>
                        </VTable>
                    </VCardText>
                    <VCardActions>
                        <VSpacer></VSpacer>
                        <VBtn
                            color="primary"
                            @click="showRequisitosDialog = false"
                        >
                            Cerrar
                        </VBtn>
                    </VCardActions>
                </VCard>
            </VDialog>

            <!-- Modal para confirmar eliminación -->
            <VDialog
                v-model="isDeleteConvocatoriaDialogVisible"
                max-width="500"
                persistent
            >
                <VCard v-if="convocatoria_selected_deleted">
                    <VCardTitle class="text-h5">
                        Confirmar eliminación
                    </VCardTitle>
                    <VCardText>
                        ¿Estás seguro de que deseas eliminar la convocatoria "{{ convocatoria_selected_deleted.titulo }}"?
                        Esta acción no se puede deshacer.
                    </VCardText>
                    <VCardActions>
                        <VSpacer></VSpacer>
                        <VBtn
                            color="secondary"
                            variant="text"
                            @click="isDeleteConvocatoriaDialogVisible = false"
                        >
                            Cancelar
                        </VBtn>
                        <VBtn
                            color="error"
                            variant="text"
                            @click="deleteConvocatoria(convocatoria_selected_deleted); isDeleteConvocatoriaDialogVisible = false"
                        >
                            Eliminar
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