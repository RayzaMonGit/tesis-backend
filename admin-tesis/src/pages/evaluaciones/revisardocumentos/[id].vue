<script setup>
import { useRoute } from 'vue-router'
const route = useRoute()
const postulacionId = route.params.id
console.log('ID de la postulación:', postulacionId)

import { ref, onMounted, computed, reactive } from 'vue'

// Configuración inicial
const tabsData = ['Requisitos', 'Hoja de Vida/CV']
const currentTab = ref(0)
const panelesAbiertos = ref([])
const confirmDialog = ref(false)
const opcionesEvaluacion = [
  { title: 'Aprobado', value: 'aprobado' },
  { title: 'Rechazado', value: 'rechazado' },
  { title: 'Pendiente', value: 'pendiente' },
]

// Estados
const loading = ref(true)
const saving = ref(false)
const postulacion = ref(null)
const postulante = ref(null)
const convocatoria = ref(null)
const requisitosLey = ref([])
const requisitosPersonalizados = ref([])
const secciones = ref([])

// Datos de documentos
const archivosRequisitos = reactive({ ley: {}, personalizado: {} })
const archivosFormulario = ref({})

// Evaluaciones
const evaluacionId = ref(null)
const evaluacionRequisitos = reactive({ ley: {}, personalizado: {} })
const evaluacionArchivos = ref({})
const comentariosRequisitos = reactive({ ley: {}, personalizado: {} })
const comentariosArchivos = ref({})
const comentarioGeneral = ref('')
const comentarioVisible = reactive({ ley: null, personalizado: null })
const comentarioVisibleArchivos = ref({})

// Computed
const documentosCompletos = computed(() => {
  const requisitosObligatorios = [
    ...requisitosLey.value,
    ...requisitosPersonalizados.value.filter(r => r.requerido)
  ]
  return requisitosObligatorios.every(requisito => {
    return archivosRequisitos.ley[requisito.id]?.yaSubido || 
           archivosRequisitos.personalizado[requisito.id]?.yaSubido
  })
})

const puntajeTotal = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + getPuntajeSeccion(seccion.id)
  }, 0)
})

const puntajeAsignadoTotal = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + getPuntajeAsignadoSeccion(seccion.id)
  }, 0)
})

const puntajeMaximoPosible = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + parseFloat(seccion.puntaje_max)
  }, 0)
})

const progresoTotal = computed(() => {
  const total = puntajeMaximoPosible.value
  return total > 0 ? Math.round((puntajeAsignadoTotal.value / total) * 100) : 0
})

// Métodos
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('es-ES', options)
}

const getFileIcon = (filename) => {
  const ext = filename.split('.').pop().toLowerCase()
  const icons = {
    pdf: 'ri-file-pdf-2-line',
    jpg: 'ri-file-image-line',
    jpeg: 'ri-file-image-line',
    png: 'ri-file-image-line',
    doc: 'ri-file-word-line',
    docx: 'ri-file-word-line',
    xls: 'ri-file-excel-line',
    xlsx: 'ri-file-excel-line',
    default: 'ri-file-3-line'
  }
  return icons[ext] || icons.default
}

const getPuntajeAsignadoSeccion = (seccionId) => {
  const seccion = secciones.value.find(s => s.id === seccionId)
  if (!seccion) return 0

  return seccion.criterios.reduce((total, criterio) => {
    return total + getPuntajeAsignadoCriterio(seccionId, criterio.id)
  }, 0)
}

const getPuntajeAsignadoCriterio = (seccionId, criterioId) => {
  const archivos = archivosFormulario.value[seccionId]?.[criterioId] || []
  if (archivos.length === 0) return 0

  const criterio = secciones.value
    .find(s => s.id === seccionId)
    ?.criterios.find(c => c.id === criterioId)

  if (!criterio) return 0

  const aprobados = evaluacionArchivos.value[seccionId]?.[criterioId]?.filter(
    estado => estado === 'aprobado'
  ).length || 0

  return aprobados * (parseFloat(criterio.puntaje_por_item) || 0)
}

const mostrarComentario = (tipo, id) => {
  comentarioVisible[tipo] = comentarioVisible[tipo] === id ? null : id
}

const mostrarComentarioArchivo = (seccionId, criterioId, index) => {
  if (!comentarioVisibleArchivos.value[seccionId]) {
    comentarioVisibleArchivos.value[seccionId] = {}
  }
  comentarioVisibleArchivos.value[seccionId][criterioId] = 
    comentarioVisibleArchivos.value[seccionId][criterioId] === index ? null : index
}

const guardarEvaluacion = async (finalizar = false) => {
  saving.value = true
  try {
    // 1. Guardar/Actualizar evaluación general
    const endpoint = evaluacionId.value 
      ? `/evaluaciones/${evaluacionId.value}`
      : '/evaluaciones'
      
    const method = evaluacionId.value ? 'POST' : 'POST'
    
    const evalData = {
      postulacion_id: postulacionId,
      puntaje_total: puntajeAsignadoTotal.value,
      comentarios_generales: comentarioGeneral.value,
      estado: finalizar ? 'finalizada' : 'borrador'
    }

    const res = await $api(endpoint, {
      method,
      body: evalData
    })

    evaluacionId.value = res.data.id

    // 2. Guardar requisitos
    for (const tipo in evaluacionRequisitos) {
      for (const requisitoId in evaluacionRequisitos[tipo]) {
        await $api('/evaluaciones/requisitos', {
          method: 'POST',
          body: {
            evaluacion_id: evaluacionId.value,
            requisito_id: requisitoId,
            estado: evaluacionRequisitos[tipo][requisitoId],
            comentarios: comentariosRequisitos[tipo][requisitoId],
            es_requisito_ley: tipo === 'ley'
          }
        })
      }
    }

    // 3. Guardar documentos
    for (const seccionId in evaluacionArchivos.value) {
      for (const criterioId in evaluacionArchivos.value[seccionId]) {
        for (let i = 0; i < evaluacionArchivos.value[seccionId][criterioId].length; i++) {
          const archivo = archivosFormulario.value[seccionId][criterioId][i]
          
          await $api('/evaluaciones/documentos', {
            method: 'POST',
            body: {
              evaluacion_id: evaluacionId.value,
              postulacion_documento_id: archivo.id,
              estado: evaluacionArchivos.value[seccionId][criterioId][i],
              puntaje: getPuntajeAsignadoCriterio(seccionId, criterioId),
              comentarios: comentariosArchivos.value[seccionId]?.[criterioId]?.[i] || ''
            }
          })
        }
      }
    }

    if (finalizar) {
      // Actualizar estado postulación
      await $api(`/postulaciones/${postulacionId}/actualizar-estado`, {
        method: 'POST',
        body: { estado: 'evaluada', nota_final: puntajeAsignadoTotal.value }
      })
    }

  } catch (error) {
    console.error('Error guardando evaluación:', error)
  } finally {
    saving.value = false
  }
}

const cargarPostulacion = async () => {
  try {
    loading.value = true;
    
    // 1. Cargar datos básicos de postulación
    const postRes = await $api(`/postulaciones/${postulacionId}`);
    postulacion.value = postRes.data;
    postulante.value = postRes.data.postulante;
    convocatoria.value = postRes.data.convocatoria;
    requisitosLey.value = postRes.data.convocatoria?.requisitos_ley || [];
    requisitosPersonalizados.value = postRes.data.convocatoria?.requisitos || [];
    secciones.value = postRes.data.convocatoria?.formulario?.secciones || [];
    
    // 2. Cargar documentos (puede venir en postRes o necesitar otra llamada)
    cargarDocumentos(postRes.data.documentos || []);

    // 3. Intentar cargar evaluaciones (si falla, no es crítico)
    try {
      const evalRes = await $api(`/postulaciones/${postulacionId}/evaluaciones`);
      if (evalRes.data && evalRes.data.length > 0) {
        cargarEvaluacionExistente(evalRes.data[0]);
      }
    } catch (error) {
      console.warn('No se pudieron cargar las evaluaciones:', error);
    }

  } catch (error) {
    console.error('Error cargando postulación:', error);
    // Mostrar notificación al usuario
  } finally {
    loading.value = false;
  }
}

const cargarDocumentos = (documentos) => {
  documentos.forEach(doc => {
    if (doc.requisito_id) {
      const tipo = doc.es_requisito_ley ? 'ley' : 'personalizado'
      archivosRequisitos[tipo][doc.requisito_id] = {
        name: doc.nombre,
        url: doc.archivo,
        yaSubido: true,
        id: doc.id
      }
    } else if (doc.seccion_id && doc.criterio_id) {
      if (!archivosFormulario.value[doc.seccion_id]) {
        archivosFormulario.value[doc.seccion_id] = {}
      }
      if (!archivosFormulario.value[doc.seccion_id][doc.criterio_id]) {
        archivosFormulario.value[doc.seccion_id][doc.criterio_id] = []
      }
      
      archivosFormulario.value[doc.seccion_id][doc.criterio_id].push({
        id: doc.id,
        name: doc.nombre,
        url: doc.archivo,
        fecha: doc.created_at,
        yaSubido: true
      })
    }
  })
}

const cargarEvaluacionExistente = (evaluacion) => {
  evaluacionId.value = evaluacion.id
  comentarioGeneral.value = evaluacion.comentarios_generales

  // Cargar requisitos evaluados
  evaluacion.requisitos.forEach(req => {
    const tipo = req.es_requisito_ley ? 'ley' : 'personalizado'
    evaluacionRequisitos[tipo][req.requisito_id] = req.estado
    comentariosRequisitos[tipo][req.requisito_id] = req.comentarios
  })

  // Cargar documentos evaluados
  evaluacion.documentos.forEach(doc => {
    const seccionId = doc.documento.seccion_id
    const criterioId = doc.documento.criterio_id
    
    if (!evaluacionArchivos.value[seccionId]) {
      evaluacionArchivos.value[seccionId] = {}
    }
    if (!evaluacionArchivos.value[seccionId][criterioId]) {
      evaluacionArchivos.value[seccionId][criterioId] = []
    }

    const docIndex = archivosFormulario.value[seccionId][criterioId].findIndex(
      d => d.id === doc.postulacion_documento_id
    )

    if (docIndex !== -1) {
      evaluacionArchivos.value[seccionId][criterioId][docIndex] = doc.estado
      
      if (!comentariosArchivos.value[seccionId]) {
        comentariosArchivos.value[seccionId] = {}
      }
      if (!comentariosArchivos.value[seccionId][criterioId]) {
        comentariosArchivos.value[seccionId][criterioId] = []
      }
      
      comentariosArchivos.value[seccionId][criterioId][docIndex] = doc.comentarios
    }
  })
}

onMounted(() => {
  cargarPostulacion()
})
////////////////////////////////////////////////////////////////////

</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- Encabezado de postulación -->
      <VCard v-if="convocatoria" class="pa-6 mb-6">
        <VCardTitle class="text-h4 font-weight-bold d-flex align-center">
          <VIcon icon="ri-briefcase-line" class="me-3" />
          {{ convocatoria.titulo }} - Evaluación
        </VCardTitle>

        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <p><strong>Postulante:</strong> {{ postulante?.nombre_completo }}</p>
              <p><strong>Área:</strong> {{ convocatoria.area }}</p>
              <p><strong>Estado:</strong> 
                <VChip :color="postulacion?.estado === 'en evaluacion' ? 'warning' : 'success'" size="small">
                  {{ postulacion?.estado }}
                </VChip>
              </p>
            </VCol>
            <VCol cols="12" md="6">
              <p><strong>Nota preliminar:</strong> {{ postulacion?.nota_preliminar || '0' }}%</p>
              <p><strong>Fecha postulación:</strong> {{ formatDate(postulacion?.created_at) }}</p>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- Pestañas -->
      <VTabs v-model="currentTab" grow class="disable-tab-transition">
        <VTab v-for="(tab, index) in tabsData" :key="index">{{ tab }}</VTab>
      </VTabs>

      <!-- Contenido de pestañas -->
      <VWindow v-model="currentTab">
        <!-- Pestaña Requisitos -->
        <VWindowItem :value="0">
          <VTimeline>
            <!-- Requisitos de ley -->
            <VTimelineItem 
              v-for="requisito in requisitosLey" 
              :key="'ley-'+requisito.id"
              :dot-color="archivosRequisitos.ley[requisito.id] ? 'success' : 'error'"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                <span class="app-timeline-title">{{ requisito.nombre }}</span>
                <span class="app-timeline-meta">Requisito obligatorio</span>
              </div>

              <div v-if="archivosRequisitos.ley[requisito.id]" class="mt-3">
                <VCard class="mb-3">
                  <VCardText>
                    <div class="d-flex justify-space-between align-center">
                      <div>
                        <VIcon icon="mdi-file-document-outline" class="me-1" />
                        <a :href="archivosRequisitos.ley[requisito.id].url" target="_blank" class="text-primary text-decoration-underline">
                          {{ archivosRequisitos.ley[requisito.id].name }}
                        </a>
                      </div>
                      <div class="d-flex align-center gap-2">
                        <VSelect
                          v-model="evaluacionRequisitos.ley[requisito.id]"
                          :items="opcionesEvaluacion"
                          label="Estado"
                          density="compact"
                          style="width: 150px"
                        />
                        <VBtn
                          icon="ri-chat-1-line"
                          variant="text"
                          color="info"
                          @click="mostrarComentario('ley', requisito.id)"
                        />
                      </div>
                    </div>
                  </VCardText>
                </VCard>

                <VTextarea
                  v-if="comentarioVisible.ley === requisito.id"
                  v-model="comentariosRequisitos.ley[requisito.id]"
                  label="Comentarios"
                  placeholder="Ingrese comentarios sobre este documento"
                  rows="2"
                  class="mt-2"
                />
              </div>
              <div v-else class="text-error">
                <VIcon icon="mdi-alert-circle-outline" /> Documento no enviado
              </div>
            </VTimelineItem>

            <!-- Requisitos personalizados -->
            <VTimelineItem 
              v-for="requisito in requisitosPersonalizados" 
              :key="'per-'+requisito.id"
              :dot-color="archivosRequisitos.personalizado[requisito.id] ? 'success' : requisito.requerido ? 'error' : 'warning'"
              size="x-small"
            >
              <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                <span class="app-timeline-title">{{ requisito.nombre }}</span>
                <span class="app-timeline-meta">{{ requisito.requerido ? 'Obligatorio' : 'Opcional' }}</span>
              </div>

              <div v-if="archivosRequisitos.personalizado[requisito.id]" class="mt-3">
                <VCard class="mb-3">
                  <VCardText>
                    <div class="d-flex justify-space-between align-center">
                      <div>
                        <VIcon icon="mdi-file-document-outline" class="me-1" />
                        <a :href="archivosRequisitos.personalizado[requisito.id].url" target="_blank" class="text-primary text-decoration-underline">
                          {{ archivosRequisitos.personalizado[requisito.id].name }}
                        </a>
                      </div>
                      <div class="d-flex align-center gap-2">
                        <VSelect
                          v-model="evaluacionRequisitos.personalizado[requisito.id]"
                          :items="opcionesEvaluacion"
                          label="Estado"
                          density="compact"
                          style="width: 150px"
                        />
                        <VBtn
                          icon="ri-chat-1-line"
                          variant="text"
                          color="info"
                          @click="mostrarComentario('personalizado', requisito.id)"
                        />
                      </div>
                    </div>
                  </VCardText>
                </VCard>

                <VTextarea
                  v-if="comentarioVisible.personalizado === requisito.id"
                  v-model="comentariosRequisitos.personalizado[requisito.id]"
                  label="Comentarios"
                  placeholder="Ingrese comentarios sobre este documento"
                  rows="2"
                  class="mt-2"
                />
              </div>
              <div v-else class="text-warning" v-if="!requisito.requerido">
                <VIcon icon="mdi-information-outline" /> Documento opcional no enviado
              </div>
              <div v-else class="text-error">
                <VIcon icon="mdi-alert-circle-outline" /> Documento obligatorio no enviado
              </div>
            </VTimelineItem>
          </VTimeline>
        </VWindowItem>

        <!-- Pestaña CV/Formulario -->
        <VWindowItem :value="1">
          <!-- Resumen -->
          <VCard class="mb-4">
            <VCardText>
              <div class="d-flex justify-space-between align-center flex-wrap">
                <div>
                  <h4 class="text-h4 mb-1">Evaluación de documentos</h4>
                  <p class="mb-0">Puntaje asignado: {{ puntajeAsignadoTotal }}/{{ puntajeMaximoPosible }} pts</p>
                </div>
                <VChip color="primary" size="large" class="d-flex align-center">
                  <VIcon icon="ri-star-fill" start />
                  Progreso: {{ progresoTotal }}%
                </VChip>
              </div>
              <VProgressLinear 
                :model-value="progresoTotal" 
                color="primary" 
                height="10" 
                class="mt-3" 
                striped
              />
            </VCardText>
          </VCard>

          <!-- Secciones -->
          <VExpansionPanels v-model="panelesAbiertos" variant="accordion">
            <VExpansionPanel v-for="seccion in secciones" :key="seccion.id" class="mb-2">
              <VExpansionPanelTitle>
                <div class="d-flex justify-space-between align-center w-100">
                  <div class="d-flex align-center">
                    <VIcon icon="mdi-folder" color="primary" class="me-2" />
                    <span class="h-6 mb-1 font-weight-bold">{{ seccion.titulo }}</span>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <span class="text-caption">
                      Pts: {{ getPuntajeAsignadoSeccion(seccion.id) }}/{{ seccion.puntaje_max }}
                    </span>
                  </div>
                </div>
              </VExpansionPanelTitle>

              <VExpansionPanelText class="pt-0 pb-4 px-2 bg-grey-lighten-4 rounded-b-xl">
                <!-- Criterios -->
                <div 
                  v-for="criterio in seccion.criterios" 
                  :key="criterio.id"
                  class="pa-4 mb-4 rounded-lg elevation-1 bg-white"
                >
                  <div class="d-flex justify-space-between align-start mb-2">
                    <div>
                      <div class="text-title-1 font-weight-medium">{{ criterio.nombre }}</div>
                      <div class="text-primary">
                        {{ criterio.puntaje_por_item }} pts por item (Máx: {{ criterio.puntaje_maximo }} pts)
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="font-weight-bold text-primary">
                        Pts: {{ getPuntajeAsignadoCriterio(seccion.id, criterio.id) }}/{{ criterio.puntaje_maximo }}
                      </div>
                    </div>
                  </div>

                  <!-- Documentos -->
                  <div v-if="archivosFormulario[seccion.id]?.[criterio.id]?.length" class="mt-3">
                    <VList lines="two" density="compact">
                      <VListItem 
                        v-for="(archivo, index) in archivosFormulario[seccion.id][criterio.id]" 
                        :key="'doc-'+index"
                      >
                        <template #prepend>
                          <VIcon :icon="getFileIcon(archivo.name)" color="success" />
                        </template>
                        <VListItemTitle>
                          <a :href="archivo.url" target="_blank" class="text-primary text-decoration-none">
                            {{ archivo.name }}
                          </a>
                        </VListItemTitle>
                        <VListItemSubtitle class="text-caption">
                          Subido el {{ formatDate(archivo.fecha) }}
                        </VListItemSubtitle>
                        <template #append>
                          <div class="d-flex align-center gap-2">
                            <VSelect
                              v-model="evaluacionArchivos[seccion.id][criterio.id][index]"
                              :items="opcionesEvaluacion"
                              label="Estado"
                              density="compact"
                              style="width: 150px"
                            />
                            <VBtn
                              icon="ri-chat-1-line"
                              variant="text"
                              color="info"
                              @click="mostrarComentarioArchivo(seccion.id, criterio.id, index)"
                            />
                          </div>
                        </template>
                      </VListItem>
                    </VList>

                    <!-- Comentarios -->
                    <VTextarea
                      v-if="comentarioVisibleArchivos[seccion.id]?.[criterio.id] === index"
                      v-model="comentariosArchivos[seccion.id][criterio.id][index]"
                      label="Comentarios"
                      placeholder="Ingrese comentarios sobre este documento"
                      rows="2"
                      class="mt-2"
                    />
                  </div>
                  <div v-else class="text-warning">
                    <VIcon icon="mdi-information-outline" /> No se han subido documentos
                  </div>
                </div>
              </VExpansionPanelText>
            </VExpansionPanel>
          </VExpansionPanels>

          <!-- Comentarios generales -->
          <VCard class="mt-4">
            <VCardText>
              <VTextarea
                v-model="comentarioGeneral"
                label="Comentarios generales"
                placeholder="Ingrese observaciones generales sobre la postulación"
                rows="3"
              />
            </VCardText>
          </VCard>
        </VWindowItem>
      </VWindow>

      <!-- Botones de acción -->
      <VCard class="mt-4">
        <VCardText>
          <div class="d-flex justify-end gap-4">
            <VBtn 
              color="secondary" 
              :loading="saving"
              @click="guardarEvaluacion(false)"
            >
              <VIcon icon="ri-save-line" start />
              Guardar borrador
            </VBtn>
            <VBtn 
              color="success" 
              :loading="saving"
              @click="guardarEvaluacion(true)"
            >
              <VIcon icon="ri-check-line" start />
              Finalizar evaluación
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Diálogo de confirmación -->
  <VDialog v-model="confirmDialog" max-width="500">
    <VCard>
      <VCardTitle class="text-h5">Confirmar evaluación</VCardTitle>
      <VCardText>
        ¿Está seguro que desea finalizar la evaluación? Esta acción no podrá deshacerse.
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="error" @click="confirmDialog = false">Cancelar</VBtn>
        <VBtn color="success" @click="guardarEvaluacion(true)">Confirmar</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>


<style scoped>
/*------------------------------------------------------------------------------ */
.v-list-item__prepend {
  margin-right: 12px;
}

.app-timeline-title {
  font-weight: 600;
  font-size: 1rem;
}

.app-timeline-meta {
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  font-size: 0.75rem;
}

.app-timeline-text {
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}
</style>