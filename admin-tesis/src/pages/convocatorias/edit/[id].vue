<script setup>
import { onMounted } from 'vue';

const warning = ref(null);
const success = ref(null);
const error_exists = ref(null);
const route = useRoute('convocatorias-edit-id');
const fechaInicioOriginal = ref(null);
const from = ref({
    titulo: null,
    descripcion: null,
    area: null,
    fecha_inicio: null,
    fecha_fin: null,
    plazas_disponibles: null,
    sueldo_referencial: null,
});
import { VExpansionPanelTitle } from 'vuetify/components';

const activeTab = ref('info');
const estados = [
    'Borrador', 'Abierta', 'Cerrado', 'Anulado'
]
// Fecha actual para validaciones
const hoy = new Date().toISOString().split('T')[0];

const error_exsist = ref(null);

const FILE_DOCUMENTO = ref(null)
const NOMBRE_ARCHIVO_PREVIZUALIZA = ref(null)
const conv_selected = ref({
    titulo: '',
});
const loadDocument = ($event) => {
    const file = $event.target.files[0]
    if (!file) return

    const validTypes = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    ]

    if (!validTypes.includes(file.type)) {
        warning.value = "Solo se permiten archivos PDF o Word"
        FILE_DOCUMENTO.value = null
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = null
        return
    }

    FILE_DOCUMENTO.value = file

    if (file.type === "application/pdf") {
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = URL.createObjectURL(file)
    } else {
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = null // Word no puede mostrarse
    }
}
const edit = async () => {
    try {
        warning.value = null;
        // Validaciones existentes
        if (!from.value.titulo) {
            warning.value = "Se debe llenar el TÍTULO del cargo";
            return;
        } if (!from.value.area) {
            warning.value = "Se debe llenar el ÁREA del cargo";
            return;
        } if (!from.value.descripcion) {
            warning.value = "Se debe llenar la DESCRIPCIÓN del cargo";
            return;
        } if (!from.value.fecha_inicio) {
            warning.value = "Se debe llenar la FECHA INICIO del cargo";
            return;
        } if (!from.value.fecha_fin) {
            warning.value = "Se debe llenar la FECHA FIN del cargo";
            return;
        } if (!from.value.plazas_disponibles) {
            warning.value = "Se debe llenar la PLAZAS DISPONIBLES del cargo";
            return;
        } if (!FILE_DOCUMENTO.value) {
            warning.value = "Se debe seleccionar un DOCUMENTO para el cargo";
            return;
        }

        let formData = new FormData();
        formData.append('titulo', from.value.titulo);
        formData.append('descripcion', from.value.descripcion);
        formData.append('area', from.value.area);
        formData.append('fecha_inicio', from.value.fecha_inicio);
        formData.append('fecha_fin', from.value.fecha_fin);
        formData.append('estado', from.value.estado);
        formData.append('plazas_disponibles', from.value.plazas_disponibles);

        if (from.value.sueldo_referencial) {
            formData.append('sueldo_referencial', from.value.sueldo_referencial);
        }
        if (FILE_DOCUMENTO.value) {
            formData.append('documento', FILE_DOCUMENTO.value);
        }
        // Preparar y enviar los requisitos obligatorios
        formData.append('requisitos_obligatorios', JSON.stringify(requisitosObligatorios.value));

        // Preparar y enviar los requisitos personalizados
        formData.append('requisitos_personalizados', JSON.stringify(requisitosPersonalizados.value));

        const resp = await $api('/convocatorias/' + route.params.id, {
            method: 'POST',
            data: formData,
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response.data.message;

            }
        })
        console.log(resp);
        success.value = "Convocatoria editada correctamente";
        setTimeout(() => {
            success.value = null;
            error_exists.value = null;
            warning.value = null;
        }, 1500);


    } catch (error) {
        console.error(error);
    }
}




const show = async () => {
    try {
        const resp = await $api('/convocatorias/' + route.params.id, {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
            }
        })
        console.log(resp);
        conv_selected.value = resp.convocatoria;

        //info
        from.value.titulo = conv_selected.value.titulo;
        from.value.area = conv_selected.value.area;
        from.value.descripcion = conv_selected.value.descripcion;
        from.value.fecha_inicio = new Date(conv_selected.value.fecha_inicio).toISOString().split('T')[0];
        from.value.fecha_fin = new Date(conv_selected.value.fecha_fin).toISOString().split('T')[0];

        from.value.plazas_disponibles = conv_selected.value.plazas_disponibles;
        from.value.sueldo_referencial = conv_selected.value.sueldo_referencial;
        from.value.estado = conv_selected.value.estado;
        //documento
        FILE_DOCUMENTO.value = conv_selected.value.documento;
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = conv_selected.value.documento;

        fechaInicioOriginal.value = new Date(conv_selected.value.fecha_inicio).toISOString().split('T')[0];
    } catch (error) {
        console.error(error);
    }
}
const requisitosObligatorios = ref([]);
const requisitosPersonalizados = ref([]);

function cargarConvocatoria(convocatoria) {
    // Filtra los requisitos según su origen
    requisitosObligatorios.value = convocatoria.requisitos
        .filter(r => r.req_sec === 'Ministerio')
        .map(r => ({
            texto: r.descripcion,
            tipo: r.tipo,
            seleccionado: r.tipo === 'Obligatorio', // marcar por defecto si era obligatorio
            
        }));

    requisitosPersonalizados.value = convocatoria.requisitos
        .filter(r => r.req_sec === 'Institucion')
        .map(r => ({
            nombre: r.descripcion,
            tipo: r.tipo,
            
        }));
}
const todosSeleccionados = ref(false);

function toggleTodosObligatorios() {
  requisitosObligatorios.value.forEach(req => {
    req.seleccionado = todosSeleccionados.value;
  });
}
const mostrarDocumento = ref(false);




onMounted(() => {
    show();
})
definePage({
    meta: {
        permissions: 'editar-convocatoria',
    }
})
</script>
<template>
    <VCard class=" refer-and-earn-dialog pa-3 pa-sm-11">


        <VCardTitle class="text-h4 text-center">Editar Convocatoria</VCardTitle>
        <VTabs v-model="activeTab" grow>
            <VTab value="info">Información</VTab>
            <VTab value="requisitos">Requisitos</VTab>
        </VTabs>

        <VDivider />

        <VWindow v-model="activeTab">
            <!-- Información -->
            <VWindowItem value="info">
                <VCardText class="pa-5">
                    <!-- Aquí va tu formulario actual -->
                    <VRow dense>
                        <!-- TÍTULO -->
                        <VCol cols="12" md="6">
                            <VTextField v-model="from.titulo" label="Título del cargo"
                                placeholder="Ej: Cargo docente computación"
                                :rules="[v => !!v || 'Este campo es obligatorio']" required />
                        </VCol>

                        <!-- ÁREA -->
                        <VCol cols="12" md="6">
                            <VTextField v-model="from.area" label="Área/Carrera" placeholder="Ej: Sistemas informáticos"
                                :rules="[v => !!v || 'Este campo es obligatorio']" required />
                        </VCol>

                        <!-- PERFIL / DESCRIPCIÓN -->
                        <VCol cols="12">
                            <VTextarea v-model="from.descripcion" label="Descripción o perfil profesional"
                                placeholder="Ej: Perfil en provisión nacional..." auto-grow rows="3"
                                :rules="[v => !!v || 'Este campo es obligatorio']" required />
                        </VCol>

                        <!-- FECHA INICIO -->
                        <VCol cols="12" md="6">
                            <VTextField v-model="from.fecha_inicio" label="Fecha de inicio" type="date"
                                :min="fechaInicioOriginal" :rules="[
                                    v => !!v || 'Obligatorio',
                                    v => v >= fechaInicioOriginal || 'La fecha de inicio no puede ser anterior a la original'
                                ]" required />
                        </VCol>

                        <!-- FECHA FIN -->
                        <VCol cols="12" md="6">
                            <VTextField v-model="from.fecha_fin" label="Fecha de finalización" type="date"
                                :min="from.fecha_inicio" :rules="[
                                    v => !!v || 'Obligatorio',
                                    v => v >= from.fecha_inicio || 'La fecha de finalización no puede ser anterior a la fecha de inicio'
                                ]" required />
                        </VCol>

                        <!-- PLAZAS DISPONIBLES -->
                        <VCol cols="12" md="4">
                            <VTextField v-model="from.plazas_disponibles" label="Plazas disponibles" placeholder="Ej: 1"
                                type="number" :rules="[v => v > 0 || 'Debe ser un número mayor a 0']" required />
                        </VCol>

                        <!-- SUELDO REFERENCIAL -->
                        <VCol cols="12" md="4">
                            <VTextField v-model="from.sueldo_referencial" label="Sueldo referencial"
                                placeholder="Ej: 3500 Bs." type="number"
                                :rules="[v => v >= 0 || 'Debe ser un número válido']" />
                        </VCol>
                        <!-- ESTADO -->
                        <VCol cols="12" md="4">
                            <VSelect v-model="from.estado" :items="estados" label="Estado"
                                placeholder="Selecciona un estado" :rules="[v => !!v || 'Selecciona un estado']"
                                required />
                        </VCol>

                        <!-- DOCUMENTO -->
                        <VCol cols="12" md="12">
                            <VRow>
                                <VCol cols="12">
                                    <VFileInput label="Documento de convocatoria" accept=".pdf,.doc,.docx"
                                        @change="loadDocument($event)" />
                                </VCol>

                                <!-- PREVISUALIZAR SOLO PDF -->
                                <VCol cols="12" v-if="NOMBRE_ARCHIVO_PREVIZUALIZA">
                                    <iframe :src="NOMBRE_ARCHIVO_PREVIZUALIZA" type="application/pdf" width="100%"
                                        height="400px" style="border: 1px solid #ccc; border-radius: 8px;" />
                                </VCol>

                                <!-- MOSTRAR SOLO EL NOMBRE SI ES DOC/DOCX -->
                                <VCol cols="12" v-else-if="FILE_DOCUMENTO">
                                    <p class="text-caption">Archivo seleccionado: {{ FILE_DOCUMENTO.name }}</p>
                                </VCol>
                            </VRow>

                        </VCol>
                    </VRow>
                </VCardText>
            </VWindowItem>

            <!-- Requisitos -->
            <VWindowItem value="requisitos" >
                <VCardText>
                    <VRow>
                        <VCol cols="12">
                            <p class="text-h6">Requisitos para la convocatoria</p>

                            <!-- 🔹 Sección 1: Mostrar documento -->
                            <VCheckbox v-model="mostrarDocumento" label="Ver documento adjunto" />

                            <!-- Muestra solo si se seleccionó "ver" y es PDF -->
                            <VCol cols="12" v-if="mostrarDocumento && NOMBRE_ARCHIVO_PREVIZUALIZA">
                                <iframe :src="NOMBRE_ARCHIVO_PREVIZUALIZA" type="application/pdf" width="100%"
                                    height="400px" style="border: 1px solid #ccc; border-radius: 8px;" />
                            </VCol>

                            <!-- Muestra nombre si no es PDF -->
                            <VCol cols="12" v-else-if="mostrarDocumento && FILE_DOCUMENTO">
                                <p class="text-caption">Archivo seleccionado: {{ FILE_DOCUMENTO.name }}</p>
                            </VCol>

                            <VExpansionPanels variant="accordion">
                                <!-- 🔹 Sección 2: Requisitos obligatorios con seleccionar todos -->
                                <VExpansionPanel>
                                    <VExpansionPanelTitle>Requisitos obligatorios (Ministerio de Educación)
                                    </VExpansionPanelTitle>
                                    <VExpansionPanelText>
                                        <VCheckbox v-model="todosSeleccionados" label="Seleccionar / Quitar todos"
                                            @change="toggleTodosObligatorios" />
                                        <VList dense>
                                            <VListItem v-for="(req, index) in requisitosObligatorios"
                                                :key="'obligatorio-' + index">
                                                <VCheckbox v-model="req.seleccionado" :label="req.texto" hide-details />
                                            </VListItem>
                                        </VList>
                                    </VExpansionPanelText>
                                </VExpansionPanel>

                                <!-- 🔹 Sección 3: Requisitos personalizados -->
                                <VExpansionPanel>
                                    <VExpansionPanelTitle>Requisitos personalizados</VExpansionPanelTitle>
                                    <VExpansionPanelText>
                                        <VRow>
                                            <VCol cols="6">
                                                <VTextField label="Nombre del requisito"
                                                    v-model="nuevoRequisito.nombre" />
                                            </VCol>
                                            <VCol cols="4">
                                                <VSelect :items="['Obligatorio', 'Opcional']"
                                                    v-model="nuevoRequisito.tipo" label="Tipo" />
                                            </VCol>
                                            <VCol cols="2">
                                                <VBtn @click="agregarRequisito" color="primary">Agregar</VBtn>
                                            </VCol>
                                        </VRow>
                                        <VList dense>
                                            <VListItem v-for="(req, index) in requisitosPersonalizados"
                                                :key="'perso-' + index">
                                                <VListItemTitle>{{ req.nombre }} ({{ req.tipo }})</VListItemTitle>
                                                <VBtn icon="mdi-delete"
                                                    @click="requisitosPersonalizados.splice(index, 1)" />
                                            </VListItem>
                                        </VList>
                                    </VExpansionPanelText>
                                </VExpansionPanel>

                            </VExpansionPanels>
                            <!--<VList>
                  <VListItem v-for="(req, index) in requisitosPersonalizados" :key="'personalizado-' + index">
                    <VListItemTitle>{{ req.nombre }} ({{ req.tipo }})</VListItemTitle>
                    <VBtn icon="mdi-delete" @click="requisitosPersonalizados.splice(index, 1)" />
                  </VListItem>
                </VList>
  -->
                        </VCol>
                    </VRow>
                </VCardText>
            </VWindowItem>

            <VAlert type="warning" v-if="warning" class="mt-3">
                <strong>{{ warning }}</strong>
            </VAlert>

            <VAlert type="error" v-if="error_exsist" class="mt-3">
                <strong>hubo un error al guardar en el servidor</strong>
            </VAlert>

            <VAlert type="success" v-if="success" class="mt-3">
                <strong>{{ success }}</strong>
            </VAlert>
        </VWindow>

        <!-- Botón Guardar -->
        <VCardText class="pa-5">
            <VBtn @click="edit()">
                Guardar convocatoria
                <VIcon end icon="ri-checkbox-circle-line" />
            </VBtn>
        </VCardText>
    </VCard>


</template>