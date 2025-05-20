<script setup>
import { VExpansionPanelTitle } from 'vuetify/components';
import { load } from 'webfontloader';
//parte nueva
const requisitosLey = ref([]);

const fetchRequisitosLey = async () => {
  try {
    const response = await $api('/requisitosley');
    console.log('Respuesta requisitos ley:', response)

    requisitosLey.value = response.requisitos.map(req => ({
  ...req,
  seleccionado: false,
}));

  } catch (error) {
    console.error("Error cargando requisitos ley:", error);
  }
}



const activeTab = ref('info');
const estados = [
  'Borrador', 'Abierta', 'Cerrado', 'Anulado'
]
// Fecha actual para validaciones
const hoy = new Date().toISOString().split('T')[0]

// Referencia al formulario
const formRef = ref(null)

const from = ref({
  id: null,
  titulo: null,
  descripcion: null,
  area: null,
  fecha_inicio: null,
  fecha_fin: null,
  plazas_disponibles: null,
  sueldo_referencial: null,
  estado: 'Borrador',

  //documento: null,
});
const error_exsist = ref(null);

const warning = ref(null);

const FILE_DOCUMENTO = ref(null)
const NOMBRE_ARCHIVO_PREVIZUALIZA = ref(null)

const success = ref(null);
const roles = ref([]);
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



const store = async () => {
  console.log("Guardando...")

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
  }  if (!from.value.plazas_disponibles) {
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

  // Preparar y enviar los requisitos personalizados
  const requisitosLeyIds = requisitosLey.value
  .filter(req => req.seleccionado)
  .map(req => req.id);

requisitosLeyIds.forEach(id => {
  formData.append('requisitos_ley_ids[]', id);
});
// Enviar los requisitos personalizados
formData.append('requisitos_personalizados', JSON.stringify(requisitosPersonalizados.value));

console.log('Requisitos personalizados:', requisitosPersonalizados.value)

try {
    const resp = await $api('/convocatorias', {
      method: 'POST',
      body: formData,
      onResponseError({ response }) {
        console.log(response)
        error_exsist.value = response._data.error
      }
    });
    console.log(resp);
    if (resp.message === 200) {
      success.value = "Convocatoria registrada correctamente"
      setTimeout(() => {
        success.value = null;
        warning.value = null;
        error_exsist.value = null;
        //redirigir a list.vue
        router.push('/convocatorias/list');
        // Limpiar formulario
        //fileClean();
        // Navegar a la lista de convocatorias o limpiar formulario
        // router.push('/convocatorias');
      }, 1000)
    } else if (resp.message === 403) {
      warning.value = resp.message_text
    }

  } catch (error) {
    console.log(error);
    error_exsist.value = error;
  }
}
//para los requisitos 
const mostrarDocumento = ref(false);
const todosSeleccionados = ref(false);

function toggleTodosObligatorios() {
  requisitosLey.value.forEach(r => (r.seleccionado = todosSeleccionados.value));
}

definePage({
  meta: {
    permissions: ["register_convocatories"
      , "edit_convocatories"
      , "delete_convocatories"]
  },
})
const requisitos = ref([])
const nuevoRequisito = ref({
  nombre: '',
  tipo: 'Obligatorio'
})
const requisitosPersonalizados = ref([]);
const agregarRequisito = () => {
  if (!nuevoRequisito.value.nombre) return;

  requisitosPersonalizados.value.push({
    nombre: nuevoRequisito.value.nombre,
    tipo: nuevoRequisito.value.tipo
  });

  // Limpiar campos
  nuevoRequisito.value.nombre = '';
  nuevoRequisito.value.tipo = 'Obligatorio';
};
const fileClean=()=>{
  FILE_DOCUMENTO.value = null;
  NOMBRE_ARCHIVO_PREVIZUALIZA.value = null;
  from.value.id = null;
  from.value.titulo = null;  
  from.value.descripcion = null;
  from.value.area = null;
  from.value.fecha_inicio = null;
  from.value.fecha_fin = null;
  from.value.plazas_disponibles = null;
  from.value.sueldo_referencial = null;
  from.value.estado = 'Borrador';
  requisitosPersonalizados.value = [];
todosSeleccionados.value = false;
mostrarDocumento.value = false;


}

onMounted(() => {
  fetchRequisitosLey();
});

</script>
<template>
  <VCard class=" refer-and-earn-dialog pa-3 pa-sm-11">


    <VCardTitle class="text-h4 text-center">Crear Convocatoria</VCardTitle>
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
              <VTextField v-model="from.titulo" label="Título del cargo" placeholder="Ej: Cargo docente computación"
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
              <VTextField v-model="from.fecha_inicio" label="Fecha de inicio" type="date" :min="hoy"
                :rules="[v => !!v || 'Obligatorio', v => v >= hoy || 'La fecha de inicio no puede ser anterior a la fecha actual']"
                required />
            </VCol>

            <!-- FECHA FIN -->
            <VCol cols="12" md="6">
              <VTextField v-model="from.fecha_fin" label="Fecha de finalización" type="date" :min="from.fecha_inicio"
                :rules="[v => !!v || 'Obligatorio', v => v >= from.fecha_inicio || 'La fecha de finalización no puede ser anterior a la fecha de inicio']"
                required />
            </VCol>

            <!-- PLAZAS DISPONIBLES -->
            <VCol cols="12" md="4">
              <VTextField v-model="from.plazas_disponibles" label="Plazas disponibles" placeholder="Ej: 1" type="number"
                :rules="[v => v > 0 || 'Debe ser un número mayor a 0']" required />
            </VCol>

            <!-- SUELDO REFERENCIAL -->
            <VCol cols="12" md="4">
              <VTextField v-model="from.sueldo_referencial" label="Sueldo referencial" placeholder="Ej: 3500 Bs."
                type="number" :rules="[v => v >= 0 || 'Debe ser un número válido']"  />
            </VCol>
            <!-- ESTADO -->
            <VCol cols="12" md="4">
          <VSelect
            v-model="from.estado"
            :items="estados"
            label="Estado"
            placeholder="Selecciona un estado"
            :rules="[v => !!v || 'Selecciona un estado']"
            required
          />
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
                  <iframe :src="NOMBRE_ARCHIVO_PREVIZUALIZA" type="application/pdf" width="100%" height="400px"
                    style="border: 1px solid #ccc; border-radius: 8px;" />
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
      <VWindowItem value="requisitos">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <p class="text-h6">Requisitos para la convocatoria</p>

              <!-- 🔹 Sección 1: Mostrar documento -->
              <VCheckbox v-model="mostrarDocumento" label="Ver documento adjunto" />

              <!-- Muestra solo si se seleccionó "ver" y es PDF -->
              <VCol cols="12" v-if="mostrarDocumento && NOMBRE_ARCHIVO_PREVIZUALIZA">
                <iframe :src="NOMBRE_ARCHIVO_PREVIZUALIZA" type="application/pdf" width="100%" height="400px"
                  style="border: 1px solid #ccc; border-radius: 8px;" />
              </VCol>

              <!-- Muestra nombre si no es PDF -->
              <VCol cols="12" v-else-if="mostrarDocumento && FILE_DOCUMENTO">
                <p class="text-caption">Archivo seleccionado: {{ FILE_DOCUMENTO.name }}</p>
              </VCol>

              <VExpansionPanels variant="accordion">
                <!-- 🔹 Sección 2: Requisitos obligatorios con seleccionar todos -->
                <VExpansionPanel >
                  <VExpansionPanelTitle>Requisitos obligatorios (Ministerio de Educación)</VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <VCheckbox v-model="todosSeleccionados" label="Seleccionar / Quitar todos"
                @change="toggleTodosObligatorios" />
                <VList dense>
                      <VListItem v-for="(req, index) in requisitosLey" :key="'ley-' + index">
                        <VCheckbox
                          v-model="req.seleccionado"
                          :label="req.num + req.descripcion"
                          hide-details
                        />
                      </VListItem>
                    </VList>



                  </VExpansionPanelText>
                </VExpansionPanel>

                <!-- 🔹 Sección 3: Requisitos personalizados -->
                <VExpansionPanel >
                  <VExpansionPanelTitle>Requisitos personalizados</VExpansionPanelTitle>
                  <VExpansionPanelText>
    <VRow>
      <VCol cols="6">
        <VTextField label="Nombre del requisito" v-model="nuevoRequisito.nombre" />
      </VCol>
      <VCol cols="4">
        <VSelect
          :items="['Obligatorio', 'Opcional']"
          v-model="nuevoRequisito.tipo"
          label="Tipo"
        />
      </VCol>
      <VCol cols="2">
        <VBtn @click="agregarRequisito" color="primary">Agregar</VBtn>
      </VCol>
    </VRow>

    <VList dense>
      <VListItem
        v-for="(req, index) in requisitosPersonalizados"
        :key="index"
      >
        <VListItemTitle>{{ req.nombre }} ({{ req.tipo }})</VListItemTitle>
        <VBtn icon="mdi-delete" @click="requisitosPersonalizados.splice(index, 1)" />
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
      <VBtn @click="store()">
        Guardar convocatoria
        <VIcon end icon="ri-checkbox-circle-line" />
      </VBtn>
    </VCardText>
  </VCard>


</template>