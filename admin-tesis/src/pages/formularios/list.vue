<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Formularios de Evaluación</h1>

    <v-row dense>
      <v-col
        v-for="formulario in formularios"
        :key="formulario.id"
        cols="12"
        md="6"
        lg="4"
      >
        <v-card class="rounded-xl shadow">
          <v-card-title class="text-xl font-semibold">{{ formulario.nombre }}</v-card-title>

          <v-card-subtitle class="text-sm text-gray-600">
            Puntaje total: {{ formulario.puntaje_total }}
          </v-card-subtitle>

          <v-card-text>
            <p class="mb-2 text-gray-700">{{ formulario.descripcion || 'Sin descripción' }}</p>

            <ul class="text-sm text-gray-600">
              <li v-for="(seccion, i) in formulario.secciones" :key="i">
                • {{ seccion.titulo }} ({{ seccion.puntaje_max }} pts)
              </li>
            </ul>
          </v-card-text>

          <v-card-actions>
            <v-btn color="primary" @click="abrirDialogoEdicion(formulario)" icon>
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn color="error" @click="confirmarEliminacion(formulario)" icon>
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Modal de edición -->
    <v-dialog v-model="dialogoActivo" max-width="800px">
      <v-card>
        <v-card-title>Editar Formulario</v-card-title>
        <v-card-text>
         <!-- <FormularioEditor :formulario="formularioSeleccionado" @guardado="recargarFormularios" /> -->
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn text @click="dialogoActivo = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Confirmación de borrado -->
    <v-dialog v-model="dialogoBorrar" max-width="500px">
      <v-card>
        <v-card-title>¿Eliminar formulario?</v-card-title>
        <v-card-text>
          ¿Deseas eliminar el formulario <strong>{{ formularioSeleccionado?.nombre }}</strong>?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn text @click="dialogoBorrar = false">Cancelar</v-btn>
          <v-btn color="error" @click="eliminarFormulario">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
//import FormularioEditor from './Editor.vue'

const formularios = ref([])
const dialogoActivo = ref(false)
const dialogoBorrar = ref(false)
const formularioSeleccionado = ref(null)

import axios from 'axios'
const cargarFormularios = async () => {
  try {
    const res = await axios.get('/api/formularios-evaluacion')
    console.log('Respuesta real:', res.data)
    formularios.value = res.data
  } catch (error) {
    console.error('Error cargando formularios', error)
  }
}
/*const cargarFormularios = async () => {
  const res = await $api('/formularios-evaluacion')
  //console.log(res.data)
  console.log(res.data?.data)
  formularios.value = res.data
}*/

const abrirDialogoEdicion = (formulario) => {
  formularioSeleccionado.value = { ...formulario }
  dialogoActivo.value = true
}

const confirmarEliminacion = (formulario) => {
  formularioSeleccionado.value = formulario
  dialogoBorrar.value = true
}

const eliminarFormulario = async () => {
  try {
    await $api(`/formularios-evaluacion/${formularioSeleccionado.value.id}`, {
      method: 'DELETE'
    })
    dialogoBorrar.value = false
    await cargarFormularios()
  } catch (error) {
    console.error('Error al eliminar:', error)
  }
}

const recargarFormularios = () => {
  dialogoActivo.value = false
  cargarFormularios()
}

onMounted(cargarFormularios)
</script>
