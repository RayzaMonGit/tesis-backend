<script setup>

definePage({
  meta: {
    permissions: ["calendar"]
  },
})
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const convocatorias = ref([])
const cargando = ref(true)

const fetchConvocatoriasAbiertas = async () => {
  try {
    const resp = await $api('/convocatorias?estado=Abierta')
    convocatorias.value = resp.data || []
  } catch (error) {
    console.error('Error al cargar convocatorias abiertas:', error)
  } finally {
    cargando.value = false
  }
}

const verConvocatoria = (id) => {
  router.push({ name: 'postulante-convocatoria-detalle', params: { id } })
}

onMounted(() => {
  fetchConvocatoriasAbiertas()
})
</script>

<template>
  <div>
    <h2 class="text-h4 font-weight-bold mb-6">Convocatorias Abiertas</h2>

    <VRow>
      <VCol
        v-for="conv in convocatorias"
        :key="conv.id"
        cols="12"
        md="6"
        lg="6"
      >
        <VCard>
          <VRow no-gutters>
            <VCol cols="12" sm="8" md="12" lg="7">
              <VCardItem>
                <VCardTitle class="text-primary">{{ conv.titulo }}</VCardTitle>
              </VCardItem>
              <VCardText>
                <div class="text-subtitle-1">{{ conv.area }}</div>
                <p class="text-body-2 text-wrap">
                  {{ conv.descripcion.slice(0, 100) }}...
                </p>
                <div class="text-caption text-medium-emphasis mt-2">
                  Inicio: {{ new Date(conv.fecha_inicio).toLocaleDateString() }}
                  <br />
                  Fin: {{ new Date(conv.fecha_fin).toLocaleDateString() }}
                </div>
              </VCardText>
              <VCardText>
                <VBtn
                  color="primary"
                  @click="verConvocatoria(conv.id)"
                >
                  Ver más detalles
                </VBtn>
              </VCardText>
            </VCol>

            <VCol cols="12" sm="4" md="12" lg="5" class="bg-grey-lighten-4 text-center">
              <div class="d-flex flex-column align-center py-10 h-100 justify-center">
                <p class="text-h6 mb-1">Plazas disponibles</p>
                <p class="text-h4 font-weight-bold">{{ conv.plazas_disponibles }}</p>

                <VDivider class="my-2" />

                <p class="text-sm mb-0">Sueldo Referencial</p>
                <p class="text-subtitle-1">{{ conv.sueldo_referencial || 'No especificado' }}</p>
              </div>
            </VCol>
          </VRow>
        </VCard>
      </VCol>
    </VRow>

    <VAlert v-if="!cargando && convocatorias.length === 0" type="info">
      No hay convocatorias abiertas actualmente.
    </VAlert>
  </div>
</template>
