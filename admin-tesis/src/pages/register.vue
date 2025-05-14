<script>
export default {
  name: 'RegisterPage',
  // Define directamente aquí - esto es lo importante
  meta: {
    public: true,
    unauthenticatedOnly: true,
    layout: 'blank',
  }
}
</script>

<script setup>
// Aquí puedes definir la lógica de tu componente con composition API
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import registerMultiStepsIllustration from '@images/pages/register-multi-step-illustration.png'

const currentStep = ref(0)
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const router = useRouter()
const type_docs=[
    'CI',
    'PASAPORTE',
    'CARNET DE EXTRANJERO',
]
const type_grades=[
'Técnico Universitario Medio',
'Técnico Universitario Superior',
'Licenciatura',
'Maestría',
'Doctorado',
]
const items = [
  {
    title: 'Cuenta',
    subtitle: 'Detalles de la cuenta',
  },
  {
    title: 'Personal',
    subtitle: 'Detalles personales',
  },
  {
    title: 'Acadmico',
    subtitle: 'Detalles académicos',
  }
]
const form=ref({
  //usuario
  name: null,
    surname: null,
    email: null,
    telefono: null,
    password: null,
    confirmPassword: null,
    designacion: null,
    gender: null,
    role_id: null,
    tipo_doc: null,
    n_doc: null,
    //postulante
    user_id:null,
    convocatoria_id:null,
    grado_academico:null,
    fecha_nacimiento:null,
    experiencia_años:null
})

const error_exsist=ref(null);

const warning=ref(null);

const FILE_AVATAR=ref(null);
const IMAGEN_PREVIZUALIZA=ref(null);
const loadFile= ($event)=>{
    /*console.log(IMAGEN_PREVIZUALIZA.value);
    console.log(FILE_AVATAR.value);*/
    if($event.target.files[0].type.indexOf("image") < 0){
        FILE_AVATAR.value = null;
        IMAGEN_PREVIZUALIZA.value = null;
        warning.value = "SOLAMENTE PUEDEN SER ARCHIVOS DE TIPO IMAGEN";
      return;
    }
    warning.value = '';
    FILE_AVATAR.value = $event.target.files[0];
    let reader = new FileReader();
    reader.readAsDataURL(FILE_AVATAR.value);
    reader.onloadend = () => IMAGEN_PREVIZUALIZA.value = reader.result;
}
const onSubmit = () => {

// eslint-disable-next-line no-alert
alert('Submitted..!!')
}
</script>


<template>
  

  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="4"
      class="d-none d-md-flex align-center"
    >
      <!-- here your illustration -->
      <VImg
        :src="registerMultiStepsIllustration"
        class="auth-illustration"
        height="560px"
      />
    </VCol>

    <VCol
      cols="12"
      md="8"
      class="auth-card-v2 d-flex align-center justify-center pa-10"
      style="background-color: rgb(var(--v-theme-surface));"
    >
      <VCard
        flat
        class="mt-12"
      >
        <AppStepper
          v-model:current-step="currentStep"
          :items="items"
          :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
          class="mb-12"
        />

        <VWindow
          v-model="currentStep"
          class="disable-tab-transition"
          style="max-inline-size: 685px;"
        >
          <VForm>
            <VWindowItem>
              <h4 class="text-h4 mb-1">
                Información de la cuenta
              </h4>
              <p class="text-body-1 mb-5">
                Ingrese los detalles de su cuenta
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="12"
                >
                  <VTextField
                    v-model="form.email"
                    label="Email"
                    placeholder="juanmanuel@email.com"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.password"
                    label="Password"
                    placeholder="............"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.confirmPassword"
                    label="Confirm Password"
                    placeholder="Enter Confirm Password"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isConfirmPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  />
                </VCol>

                
              </VRow>
            </VWindowItem>

            <VWindowItem>
              <h4 class="text-h4 mb-1">
                Información personal
              </h4>
              <p class="text-body-1 mb-5">
                Ingrese los detalles de su información personal
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.name"
                    label="Nombre:"
                    placeholder="Maria"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.surname"
                    label="Apellido:"
                    placeholder="Doe"
                  />
                </VCol>


                <VCol
                  cols="12"
                  md="6"
                >
                <VRadioGroup
                        v-model="form.gender"
                        inline>

                        <VRadio
                        label="Femenino"
                        value="F"
                        />
                        <VRadio
                        label="Masculino"
                        value="M"
                        />
                        <VRadio
                        label="Otro"
                        value="O"
                        />

                     </VRadioGroup>
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField 
                        label="Telefono:" 
                        type="number"
                        v-model="form.telefono" 
                        placeholder="Ejemplo: 77777777" />
                </VCol>

                <VCol cols="12" md="6">
                  <VSelect
                        :items="type_docs"
                        v-model="form.tipo_doc"
                        label="Tipo de documento:"
                        placeholder="Select Item"
                        eager
                    />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                <VTextField 
                        label="Nº de documento:" 
                        v-model="form.n_doc" 
                        placeholder="Ejemplo: 19999991-X" />
                </VCol>
                <VCol
                  cols="12">
                  <VRow>
                            <VCol cols="12">
                                <VFileInput 
                                label="File input" 
                                @change="loadFile($event)" />
                            </VCol>
                            <VCol cols="12" v-if="IMAGEN_PREVIZUALIZA">
                                <VImg
                                    width="137"
                                    height="176"
                                    :src="IMAGEN_PREVIZUALIZA"
                                />
                            </VCol>
                        </VRow>
                </VCol>

                
              </VRow>
            </VWindowItem>
            <VWindowItem>
              <h4 class="text-h4 mb-1">
                Información de la cuenta
              </h4>
              <p class="text-body-1 mb-5">
                Ingrese los detalles de su cuenta
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  
                  <VSelect
                    :items="type_grades"
                    v-model="form.grado_academico"
                    label="Grado académico:"
                    placeholder="Seleccionar"
                    eager></VSelect>
                </VCol>

              

                <VCol
                  cols="12"
                  md="6"
                >
          
                  <VTextField
                    v-model="form.experiencia_años"
                    label="Años de experiencia:"
                    placeholder="Ejemplo: 2"
                    type="number"></VTextField>
                </VCol>

                
              </VRow>
            </VWindowItem>
            
          </VForm>
        </VWindow>

        <div class="d-flex flex-wrap justify-space-between justify-center gap-x-4 gap-y-2 my-6">
          <VBtn
            color="secondary"
            variant="outlined"
            :disabled="currentStep === 0"
            @click="currentStep--"
          >
            <VIcon
              icon="ri-arrow-left-line"
              start
              class="flip-in-rtl"
            />
            Anterior
          </VBtn>

          <VBtn
            v-if="items.length - 1 === currentStep"
            color="success"
            append-icon="ri-check-line"
            @click="onSubmit"
          >
            Registrarme
          </VBtn>

          <VBtn
            v-else
            @click="currentStep++"
          >
            Siguiente

            <VIcon
              icon="ri-arrow-right-line"
              end
              class="flip-in-rtl"
            />
          </VBtn>
        </div>
      </VCard>
    </VCol>
  </VRow>
</template>
<style lang="scss">
.refer-link-input {
    .v-field--appended {
        padding-inline-end: 0;
    }

    .v-field__append-inner {
        padding-block-start: 0.125rem;
    }
}
</style>