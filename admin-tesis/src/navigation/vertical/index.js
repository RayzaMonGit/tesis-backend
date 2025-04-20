export default [
  {
    title: 'Dashboard',
    to: { name: 'dashboard' },
    icon: { icon: 'ri-bar-chart-2-line' },
  },
  { heading: 'Accesos' },
  {
    title: 'Usuarios',
    to: { name: 'second-page' },
    icon: { icon: 'ri-user-3-line' },
  },
  {
    title: 'Roles y Permisos',
    to: { name: 'roles-permisos' },
    icon: { icon: 'ri-lock-password-line' },
  },

  { heading: 'Gestión Académica' },
  {
    title: 'Postulantes',
    icon: { icon: 'ri-file-user-line' },
    children: [
      {
        title: 'Registrar',
        to: { name: 'postulantes-registrar' },
        icon: { icon: 'ri-user-add-line' },
      },
      {
        title: 'Listado',
        to: { name: 'postulantes-listado' },
        icon: { icon: 'ri-list-unordered' },
      },
    ],
  },
  {
    title: 'Documentos',
    icon: { icon: 'ri-file-upload-line' },
    children: [
      {
        title: 'Subir Documento',
        to: { name: 'documentos-subir' },
        icon: { icon: 'ri-upload-cloud-line' },
      },
      {
        title: 'Ver Documentos',
        to: { name: 'documentos-listado' },
        icon: { icon: 'ri-folder-line' },
      },
    ],
  },
  {
    title: 'Convocatorias',
    icon: { icon: 'ri-megaphone-line' },
    children: [
      {
        title: 'Crear Convocatoria',
        to: { name: 'convocatorias-crear' },
        icon: { icon: 'ri-add-circle-line' },
      },
      {
        title: 'Ver Convocatorias',
        to: { name: 'convocatorias-listado' },
        icon: { icon: 'ri-calendar-event-line' },
      },
    ],
  },
  {
    title: 'Evaluaciones',
    icon: { icon: 'ri-clipboard-line' },
    children: [
      {
        title: 'Registrar Evaluación',
        to: { name: 'evaluaciones-registrar' },
        icon: { icon: 'ri-clipboard-fill' },
      },
      {
        title: 'Listado',
        to: { name: 'evaluaciones-listado' },
        icon: { icon: 'ri-list-check' },
      },
    ],
  },
  {
    title: 'Comisiones',
    icon: { icon: 'ri-team-line' },
    children: [
      {
        title: 'Crear Comisión',
        to: { name: 'comisiones-crear' },
        icon: { icon: 'ri-user-add-line' },
      },
      {
        title: 'Ver Comisiones',
        to: { name: 'comisiones-listado' },
        icon: { icon: 'ri-group-line' },
      },
    ],
  },
  {
    title: 'Instituciones',
    icon: { icon: 'ri-building-line' },
    children: [
      {
        title: 'Registrar',
        to: { name: 'instituciones-registrar' },
        icon: { icon: 'ri-add-line' },
      },
      {
        title: 'Listado',
        to: { name: 'instituciones-listado' },
        icon: { icon: 'ri-bank-line' },
      },
    ],
  },

  { heading: 'Configuración' },
  {
    title: 'Módulos y Operaciones',
    to: { name: 'modulos-operaciones' },
    icon: { icon: 'ri-settings-3-line' },
  },

  // {
  //   title: 'second-page',
  //   icon: { icon: 'ri-home-smile-line' },
  //   children: [
  //     {
  //       title: 'CRM',
  //       to: 'second-page',
  //       icon: { icon: 'ri-computer-line' },
  //     },
  //     {
  //       title: 'Analytics',
  //       to: 'second-page',
  //       icon: { icon: 'ri-bar-chart-line' },
  //     },
  //     {
  //       title: 'eCommerce',
  //       to: 'second-page',
  //       icon: { icon: 'ri-shopping-cart-2-line' },
  //     },
  //     {
  //       title: 'Academy',
  //       to: 'second-page',
  //       icon: { icon: 'ri-book-open-line' },
  //     },
  //     {
  //       title: 'Logistics',
  //       to: 'second-page',
  //       icon: { icon: 'ri-truck-line' },
  //     },
  //   ],
  // },
]
