<template>
  <VRow>
    <VCol cols="12" ms="8">
      <VCard>
        <VCardItem title="Mis Documentos de Postulación" subtitle="-----------------">
          <VTabs v-model="currentTab" grow class="disable-tab-transition">
            <VTab v-for="(tab, index) in tabsData" :key="index">
              {{ tab }}
            </VTab>
          </VTabs>


          <VCardText>
            <VWindow v-model="currentTab">
              <VWindowItem :value="0">
                <VBtn color="success" class="mt-4" :loading="guardando" @click="guardarRequisitos">
                  Guardar requisitos
                  <VIcon end icon="ri-upload-cloud-line" />
                </VBtn>

                <!-- Primera pestaña: Requisitos -->
                <VTimeline>
                  <VTimelineItem v-for="requisito in requisitosLey" :key="requisito.id + 'ley'"
                    :dot-color="archivosRequisitos[requisito.id] ? 'success' : 'primary'" size="x-small">
                    <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                      <span class="app-timeline-title">{{ requisito.nombre }}</span>
                      <span class="app-timeline-meta">Requisito: {{ requisito.req }}</span>
                    </div>

                    <div class="app-timeline-text mt-1">
                      {{ requisito.descripcion ?? 'Debe subir un documento que respalde este requisito de ley.' }}
                    </div>

                    <div class="my-2">
                      <VFileInput label="Subir archivo" accept=".pdf,.jpg,.png" prepend-icon="mdi-paperclip"
                          @change="(e) => loadFile('ley', requisito.id, e)" dense />

                      <div v-if="archivosRequisitos[requisito.id]" class="d-inline-flex align-center mt-1">
                        <VIcon icon="mdi-file-document-outline" class="me-1" />
                        <span>{{ archivosRequisitos[requisito.id]?.name }}</span>
                      </div>
                    </div>
                  </VTimelineItem>
                  <VTimelineItem v-for="requisito in requisitosPersonalizados" :key="requisito.id + 'personalizado'"
                    :dot-color="archivosRequisitos[requisito.id] ? 'success' : 'primary'" size="x-small">
                    <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                      <span class="app-timeline-title">{{ requisito.nombre }}</span>
                      <span class="app-timeline-meta">Requisito: {{ requisito.tipo }}</span>
                    </div>

                    <div class="app-timeline-text mt-1">
                      {{ requisito.descripcion ?? 'Debe subir un documento que respalde este requisito de ley.' }}
                    </div>

                    <div class="my-2">
                      <VFileInput label="Subir archivo" accept=".pdf,.jpg,.png" prepend-icon="mdi-paperclip"
                        @change="(e) => loadFile('personalizado', requisito.id, e)" dense />

                      <div v-if="archivosRequisitos[requisito.id]" class="d-inline-flex align-center mt-1">
                        <VIcon icon="mdi-file-document-outline" class="me-1" />
                        <span>{{ archivosRequisitos[requisito.id]?.name }}</span>
                      </div>
                    </div>
                    </VTimelineItem>

                </VTimeline>

              </VWindowItem>
              <!-- Segunda pestaña: CV o formulario evaluación (a completar luego) -->
              <VWindowItem :value="1">
                <p>Aquí irá el formulario evaluativo o CV (a implementar luego)</p>
              </VWindowItem>
            </VWindow>
          </VCardText>
        </VCardItem>
      </VCard>
    </VCol>

    <!--
    <VCol cols="12"md=4>
      <div class="course-content">
          <VExpansionPanels  
          v-model="panelStatus"
          variant="accordion">
          <VExpansionPanel
            v-for="(section, index) in secciones"
            :key="index"
            elevation="0"
            collapse-icon="ri-arrow-down-s-line"
            :expand-icon="$vuetify.locale.isRtl ? 'ri-arrow-left-s-line' : 'ri-arrow-right-s-line'"
            :value="index">
            <template #title>
              <div>
                <h5 class="text-h5">
                  {{ section.titulo }}
                </h5>
                
              </div>
            </template>
</VExpansionPanel>

</VExpansionPanels>
</div>
</VCol> -->
  </VRow>

</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'

const tabsData = ['Requisitos', 'Hoja de Vida/CV']
const currentTab = ref(0)
const panelStatus = ref(0)

const route = useRoute()
const postulacionId = route.params.postulacion_id

console.log('ID de postulación recibido:', postulacionId)

const postulacion = ref(null)
const postulante = ref(null)
const usuario = ref(null)
const convocatoria = ref(null)
const requisitosLey = ref([])
const requisitosPersonalizados = ref([])
const evaluadores = ref([])
const formulario = ref(null)
const secciones = ref([])
const loading = ref(true)
const requisitosTotales = ref([])

//const archivosRequisitos = ref({});


const from = ref({
  postulacion_id: parseInt(postulacionId),
  requisito_id: null,
  //archivo: null,
  nombre: '',
  es_requisito_ley: false,
  es_requisito_personalizado: false,
})

const FILE_DOCUMENTO = ref(null)
const NOMBRE_ARCHIVO_PREVIZUALIZA = ref(null)

const archivosRequisitos = reactive({
  ley: {},
  personalizado: {},
});
function loadFile(tipo, id, event) {
  const file = event?.target?.files?.[0] || event?.[0]; // compatible con VFileInput y nativo

  if (file instanceof File) {
    archivosRequisitos[tipo][id] = file;
    console.log(`📎 Archivo cargado para requisito [${tipo}] ${id}:`, file);
  } else {
    console.warn(`❌ Archivo inválido para requisito [${tipo}] ${id}`, event);
    archivosRequisitos[tipo][id] = null;
  }
}




const guardando = ref(false)
//const documentos = ref([]);
/*
const documentosAGuardar = computed(() => {
  return requisitosTodos.value
    .filter(req => archivosRequisitos.value[req.id])
    .map(req => {
      return {
        requisito_id: req.id,
        archivo: archivosRequisitos.value[req.id],
        nombre: archivosRequisitos.value[req.id]?.name || '',
        es_requisito_ley: requisitosLey.value.some(r => r.id === req.id),
        es_requisito_personalizado: requisitosPersonalizados.value.some(r => r.id === req.id),
      }
    })
})
*/


const guardarRequisitos = async () => {
  guardando.value = true;

  // Guardar requisitos de ley
  for (const [requisitoId, archivo] of Object.entries(archivosRequisitos.ley)) {
    if (!(archivo instanceof File)) continue;

    const formData = new FormData();
    formData.append('postulacion_id', postulacionId);
    formData.append('requisito_id', requisitoId);
    formData.append('archivo', archivo);
    formData.append('nombre', archivo.name);
    formData.append('es_requisito_ley', '1');
    formData.append('es_requisito_personalizado', '0');

    try {
      const res = await $api('/postulacion-documentos', {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          //'Authorization': `Bearer ${localStorage.getItem('access_token')}`
        }
      });
      console.log(`✅ Guardado requisito de ley ${requisitoId}`, res);
    } catch (err) {
      console.error(`❌ Error guardando requisito de ley ${requisitoId}`, err);
    }
  }

  // Guardar requisitos personalizados
  for (const [requisitoId, archivo] of Object.entries(archivosRequisitos.personalizado)) {
    if (!(archivo instanceof File)) continue;

    const formData = new FormData();
    formData.append('postulacion_id', postulacionId);
    formData.append('requisito_id', requisitoId);
    formData.append('archivo', archivo);
    formData.append('nombre', archivo.name);
    formData.append('es_requisito_ley', '0');
    formData.append('es_requisito_personalizado', '1');

    try {
      const res = await $api('/postulacion-documentos', {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('access_token')}`
        }
      });
      console.log(`✅ Guardado requisito personalizado ${requisitoId}`, res);
    } catch (err) {
      console.error(`❌ Error guardando requisito personalizado ${requisitoId}`, err);
    }
  }

  guardando.value = false;
};



// También actualiza la función handleFileChange para mejor manejo
function handleFileChange(id, file) {
  console.log(`Archivo recibido para requisito ${id}:`, file)

  if (!file || (Array.isArray(file) && file.length === 0)) {
    archivosRequisitos.value[id] = null
    console.log(`Archivo removido para requisito ${id}`)
  } else {
    archivosRequisitos.value[id] = file
    console.log(`Archivo asignado para requisito ${id}:`, file instanceof File ? file.name : file)
  }
}

//const archivosRequisitos = ref({})

// Computado que une todos los requisitos
const requisitosTodos = computed(() => [
  ...requisitosLey.value,
  ...requisitosPersonalizados.value,
])
//mostrar requisitosTodos.value en el template
console.log('Requisitos totales:', requisitosTodos)

const requisitos = async () => {
  try {
    console.log('Cargando convocatoria para postulación:', postulacionId)
    const response = await $api(`/postulaciones/${postulacionId}`)
    console.log('Respuesta de la API:', response.data)

    // Postulación completa
    postulacion.value = response.data

    // Subdatos
    postulante.value = response.data.postulante
    usuario.value = response.data.postulante?.user ?? null
    convocatoria.value = response.data.convocatoria

    // Subdatos de la convocatoria
    requisitosLey.value = response.data.convocatoria?.requisitos_ley ?? []
    requisitosPersonalizados.value = response.data.convocatoria?.requisitos ?? []
    evaluadores.value = response.data.convocatoria?.evaluadores ?? []

    // Formulario y secciones
    formulario.value = response.data.convocatoria?.formulario ?? null
    secciones.value = response.data.convocatoria?.formulario?.secciones ?? []
    /*
        console.log('Postulación cargada:', postulacion.value)
        console.log('Postulante:', postulante.value)
        console.log('Usuario:', usuario.value)
        console.log('Convocatoria:', convocatoria.value)
        */console.log('Requisitos de ley:', requisitosLey.value)
    console.log('Requisitos personalizados:', requisitosPersonalizados.value)
    /*console.log('Evaluadores:', evaluadores.value)
    console.log('Formulario:', formulario.value)
    console.log('Secciones del formulario:', secciones.value)
*/

    requisitosTotales.value = [
      ...requisitosLey.value.map(r => ({
        id: r.id,
        descripcion: r.descripcion,
        //es_requisito_ley: true,
        //es_requisito_personalizado: false,
      })),
      ...requisitosPersonalizados.value.map(r => ({
        id: r.id,
        descripcion: r.descripcion,
        //es_requisito_ley: false,
        //es_requisito_personalizado: true,
      })),
    ]

  } catch (error) {
    console.error('Error al cargar la convocatoria:', error)
  } finally {
    loading.value = false
  }
}


const cargarDocumentosGuardados = async () => {
  try {
    const response = await $api(`/postulaciones/${postulacionId}/documentos`)
    const documentos = response.data

    documentos.forEach(doc => {
      archivosRequisitos.value[doc.requisito_id] = {
        id: doc.id,
        nombre: doc.nombre, // nombre original
        url: `/storage/${doc.archivo}`, // asumiendo que ya hiciste `php artisan storage:link`
      }
    })

    console.log('Documentos cargados:', archivosRequisitos.value)
  } catch (error) {
    console.error('Error cargando documentos previos:', error)
  }
}


onMounted(async () => {
  requisitos()
  await cargarDocumentosGuardados()

})
</script>



<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 8px;
}

.course-content {
  position: sticky;
  inset-block: 4rem 0;

  .card-list {
    --v-card-list-gap: 1rem;
  }
}
</style>