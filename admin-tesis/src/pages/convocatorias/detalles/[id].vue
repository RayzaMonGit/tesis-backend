<script setup>
const route = useRoute()
const convocatoria = ref(null)
import { onMounted } from 'vue';


onMounted(async () => {
    const resp = await $api(`/convocatorias/${route.params.id}/todos-requisitos`)
    convocatoria.value = resp
    console.log(resp);
})
</script>

<template>
  <div v-if="convocatoria">
    <VCard>
      <VCardTitle>{{ convocatoria.titulo }}</VCardTitle>
      <VCardText>
        <p><strong>Área:</strong> {{ convocatoria.area }}</p>
        <p><strong>Descripción:</strong> {{ convocatoria.descripcion }}</p>
        <p><strong>Fechas:</strong> {{ convocatoria.fecha_inicio }} - {{ convocatoria.fecha_fin }}</p>
        <p><strong>Sueldo:</strong> {{ convocatoria.sueldo_referencial }}</p>

        <div v-if="convocatoria.documento">
          <p><strong>Documento:</strong></p>
          <a :href="convocatoria.documento" target="_blank">Ver documento</a>
        </div>

        <VDivider class="my-4" />

        <h4>📄 Requisitos de Ley</h4>
        <ul>
          <li v-for="req in convocatoria.requisitos_ley" :key="'ley-'+req.id">
            {{ req.num }} {{ req.descripcion }} ({{ req.req }})
          </li>
        </ul>

        <h4 class="mt-4">📄 Requisitos Personalizados</h4>
        <ul>
          <li v-for="req in convocatoria.requisitos_personalizados" :key="'perso-'+req.id">
            {{ req.descripcion }} ({{ req.tipo }})
          </li>
        </ul>
      </VCardText>
    </VCard>
  </div>
</template>
