<template>
    <v-container>
      <h2 class="text-xl font-bold mb-4">Nuevo Formulario de Evaluación</h2>
  
      <v-text-field v-model="formulario.nombre" label="Nombre del formulario" outlined dense class="mb-4" />
  
      <div v-for="(seccion, sIndex) in formulario.secciones" :key="sIndex" class="mb-6">
        <v-text-field
          v-model="seccion.nombre"
          label="Nombre de la sección"
          outlined dense class="mb-2"
        />
  
        <div v-for="(criterio, cIndex) in seccion.criterios" :key="cIndex" class="pl-4">
          <v-text-field
            v-model="criterio.descripcion"
            label="Criterio"
            outlined dense class="mb-1"
          />
          <v-text-field
            v-model="criterio.puntaje_maximo"
            label="Puntaje máximo"
            type="number"
            outlined dense class="mb-1"
          />
          <v-btn icon @click="eliminarCriterio(sIndex, cIndex)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </div>
  
        <v-btn text color="primary" @click="agregarCriterio(sIndex)" class="mt-2">
          Añadir criterio
        </v-btn>
      </div>
  
      <v-btn color="primary" @click="agregarSeccion" class="mb-4">
        Añadir sección
      </v-btn>
  
      <v-btn color="success" @click="guardarFormulario">
        Guardar formulario
      </v-btn>
    </v-container>
  </template>
  
  <script>
  export default {
    data() {
      return {
        formulario: {
          nombre: '',
          secciones: [],
        },
      };
    },
    methods: {
      agregarSeccion() {
        this.formulario.secciones.push({
          nombre: '',
          criterios: [],
        });
      },
      agregarCriterio(seccionIndex) {
        this.formulario.secciones[seccionIndex].criterios.push({
          descripcion: '',
          puntaje_maximo: 0,
        });
      },
      eliminarCriterio(seccionIndex, criterioIndex) {
        this.formulario.secciones[seccionIndex].criterios.splice(criterioIndex, 1);
      },
      async guardarFormulario() {
  try {
    const response = await fetch('/api/formularios-evaluacion', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        // Si usás autenticación tipo JWT, deberías agregar algo como:
        // 'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(this.formulario),
    });

    if (!response.ok) throw new Error('Error en la respuesta del servidor');

    const data = await response.json();
    alert('Formulario guardado correctamente');
    this.$router.push('/formularios-evaluacion'); // redirección
  } catch (error) {
    console.error(error);
    alert('Error al guardar el formulario');
  }
},

    },
  };
  </script>
  