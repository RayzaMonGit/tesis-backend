<template>
  <v-container class="pa-6" max-width="1000px">
    <v-card elevation="4" class="pa-6">
      <v-card-title class="text-h5 font-weight-bold mb-4">
        Crear Formulario de Evaluación
      </v-card-title>

      <v-row dense>
        <v-col cols="12" md="8">
          <v-text-field
            v-model="formulario.nombre"
            label="Nombre del Formulario"
            outlined
            dense
            required
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-text-field
            v-model="formulario.resolucion"
            label="Resolución"
            outlined
            dense
          ></v-text-field>
        </v-col>
        <v-col cols="12">
          <v-textarea
            v-model="formulario.descripcion"
            label="Descripción"
            outlined
            rows="2"
            auto-grow
            dense
          ></v-textarea>
        </v-col>
      </v-row>

      <v-divider class="my-4"></v-divider>

      <div v-for="(seccion, i) in formulario.secciones" :key="i" class="mb-6">
        <v-card class="pa-4" variant="outlined">
          <v-card-title class="text-subtitle-1 font-weight-medium">
            Sección {{ i + 1 }}
            <v-spacer />
            <v-btn icon color="error" variant="text" @click="eliminarSeccion(i)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-card-title>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="seccion.titulo"
                label="Título de la Sección"
                outlined
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="seccion.puntaje_max"
                label="Puntaje Máximo de la Sección"
                type="number"
                outlined
                dense
              ></v-text-field>
            </v-col>
          </v-row>

          <v-divider class="my-3"></v-divider>

          <div v-for="(criterio, j) in seccion.criterios" :key="j" class="mb-3">
            <v-row dense align="center">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="criterio.nombre"
                  label="Nombre del Criterio"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model.number="criterio.puntaje_por_item"
                  label="Puntaje por ítem"
                  type="number"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model.number="criterio.puntaje_maximo"
                  label="Puntaje máximo acumulable"
                  type="number"
                  outlined
                  dense
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="2" class="text-right">
                <v-btn icon color="error" variant="text" @click="eliminarCriterio(i, j)">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </v-col>
            </v-row>
          </div>

          <v-btn
            prepend-icon="mdi-plus"
            variant="text"
            color="primary"
            class="mt-2"
            @click="agregarCriterio(i)"
          >
            Añadir Criterio
          </v-btn>
        </v-card>
      </div>

      <v-btn
        prepend-icon="mdi-plus-box"
        color="secondary"
        variant="tonal"
        class="mt-2"
        @click="agregarSeccion"
      >
        Añadir Sección
      </v-btn>

      <v-divider class="my-6"></v-divider>

      <v-btn
        color="primary"
        class="mt-4"
        size="large"
        block
        @click="enviarFormulario"
      >
        Guardar Formulario
      </v-btn>
    </v-card>
  </v-container>
</template>


<script setup>
import { ref } from 'vue'
import axios from 'axios'

const formulario = ref({
  nombre: '',
  descripcion: '',
  resolucion: '',
  secciones: [
  {
    titulo: '',
    puntaje_max: null,
    criterios: [],
  },
],

})

function agregarSeccion() {
  formulario.value.secciones.push({
    titulo: '',
    puntaje_max: 0,
    orden: formulario.value.secciones.length + 1,
    criterios: []
  })
}

function agregarCriterio(seccionIndex) {
  formulario.value.secciones[seccionIndex].criterios.push({
    nombre: '',
    puntaje_por_item: 0,
    puntaje_maximo: 0,
    orden: formulario.value.secciones[seccionIndex].criterios.length + 1
  })
}

const enviarFormulario = async () => {
  console.log(formulario.value)

  const formData = {
    nombre: formulario.value.nombre,
    resolucion: formulario.value.resolucion,
    descripcion: formulario.value.descripcion,
    secciones: (formulario.value.secciones || []).map((s) => ({
      titulo: s.titulo,
      puntaje_max: s.puntaje_max,
      criterios: (s.criterios || []).map((c) => ({
        nombre: c.nombre,
        puntaje_por_item: c.puntaje_por_item,
        puntaje_maximo: c.puntaje_maximo,
      })),
    })),
  }

  try {
    const resp = await $api('/formularios-evaluacion', {
      method: 'POST',
      body: formData,
      onResponseError({ response }) {
        console.log(response)
        error_exsist.value = response._data?.message || 'Error al guardar el formulario'
      },
    })

    if (resp.message === 403) {
      warning.value = resp.message_text
    } else {
      success.value = 'Formulario guardado correctamente'

      setTimeout(() => {
        success.value = null
        warning.value = null
        error_exsist.value = null
        router.push('/formularios-evaluacion/list') // ajusta esta ruta si es diferente
      }, 1500)
    }
  } catch (error) {
    console.error(error)
    error_exsist.value = 'Error al registrar el formulario'
  }
}


</script>