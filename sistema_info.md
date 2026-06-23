# Estructura del Sistema SIRPEF-OAC

Este documento provee una guía arquitectónica y un recorrido rápido de la estructura técnica del sistema **SIRPEF-OAC** (Sistema de Registro y Pago para la Oficina de Atención al Ciudadano).

---

## 1. Arquitectura General y Contenedores (Docker)

El sistema está configurado como un entorno multi-contenedor gestionado mediante Docker. En la raíz del repositorio se encuentra el script de arranque rápido `dev-up.sh` que levanta los servicios locales de desarrollo.

```
sirpef-oac/
├── dev-up.sh                # Script de automatización para arrancar el entorno
├── requerimientos.txt       # Requerimientos de negocio
├── sirpef_laravel/          # Directorio del Backend (Laravel)
└── sirpef_vue/              # Directorio del Frontend (Vue.js + Vite)
```

### Servicios en el Backend (`sirpef_laravel/docker-compose.yml`)
*   **`oac_laravel_dev`**: Contenedor principal que ejecuta PHP y Laravel. Mapea el código local al directorio `/var/www/html/` en caliente.
*   **`oac_laravel_nginx_dev`**: Servidor Nginx que actúa como proxy inverso. Expone el puerto **443** (mapeado a port `80` internamente) sirviendo la API de Laravel de forma segura.

### Servicios en el Frontend (`sirpef_vue/docker-compose.dev.yml`)
*   **`oac_vue_mppef_dev`**: Contenedor con Node.js (versión 22) que compila y sirve la aplicación en caliente con Vite. Expone el puerto **5174** en la máquina host.

---

## 2. Estructura del Backend (Laravel)

El backend es una API REST tradicional construida en PHP usando Laravel. Utiliza una base de datos **PostgreSQL** (`OAC-last`) hospedada en la dirección IP `10.5.10.131`.

### Directorios Clave

```
sirpef_laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/             # Controladores que reciben las peticiones HTTP
│   │   │   ├── AtencionCiudadanoController.php
│   │   │   └── CuotaCompromisoController.php
│   │   └── Services/AtencionCiudadano/ # Lógica de negocio (Services Pattern)
│   │       ├── StorePagoService.php # Registra pagos, vincula proveedores, sube recaudos
│   │       └── IndexPagoService.php
│   └── Models/                      # Modelos Eloquent de la base de datos
│       ├── Pago.php
│       ├── Proveedor.php
│       ├── CuotaCompromiso.php
│       └── Registro.php
├── database/migrations/             # Estructura e historial de tablas SQL
└── routes/
    └── api.php                      # Declaración de rutas de la API (/api/oac/...)
```

### Flujo de Datos en el Backend (Ejemplo: Registrar Pago)
1.  **Ruta**: La petición llega a `POST /api/oac/registrar-pago/{id}` definida en `routes/api.php`.
2.  **Controlador**: `AtencionCiudadanoController@storePago` recibe la petición y delega la ejecución a un servicio específico.
3.  **Servicio**: `StorePagoService::crearPago` abre una transacción SQL:
    *   Verifica la existencia del registro/caso (`Registro`).
    *   Crea el registro de pago (`Pago`) asociando montos, saldos y cuota.
    *   Registra o asocia el proveedor en la tabla pivot `tbl_pago_proveedor` con su monto relacionado.
    *   Procesa y sube físicamente los soportes digitales (recaudos) a `storage/app/public/recaudos/pagos` y crea el registro de correspondencia en `Recaudo`.

---

## 3. Estructura del Frontend (Vue 3 + Vite)

El frontend está desarrollado bajo una arquitectura moderna utilizando **Vue 3** (Composition API con `<script setup>`), **TypeScript**, **Pinia** para manejo de estado, y **Tailwind CSS** para los estilos globales.

### Directorios Clave

```
sirpef_vue/
├── src/
│   ├── modules/                     # Módulos del negocio
│   │   ├── FeDeVida/                # Módulo principal para OAC y Administración
│   │   │   ├── components/
│   │   │   │   └── caseFormAdministracion/ # Asistente de pasos para registrar pago
│   │   │   │       ├── FirstPage.vue   # Datos iniciales del caso
│   │   │   │       ├── SecondPage.vue  # Carga de facturas y saldos
│   │   │   │       └── FormCaso.vue    # Subida de recaudos
│   │   │   ├── composables/
│   │   │   │   └── administracion/
│   │   │   │       └── useFormAdministracion.ts # Estado y lógica de envío del formulario
│   │   │   ├── routes/index.ts      # Enrutamiento de OAC y administración
│   │   │   ├── services/index.ts    # Peticiones Axios al backend
│   │   │   └── views/administracion/
│   │   │       ├── index.vue        # Tabla de control de pagos registrados
│   │   │       ├── form.vue         # Contenedor del asistente de pagos
│   │   │       └── cuotas.vue       # Panel de control de cuota mensual compromiso
│   └── components/                  # Componentes reutilizables compartidos
```

### Rutas Frontend Vinculadas (`FeDeVida/routes/index.ts`)
*   **`/casos/administracion`**: Listado general de casos listos o pagados.
*   **`/casos/administracion/form`**: Formulario dinámico por pasos para la carga de facturación y soportes de pago vinculados a un Punto de Cuenta.
*   **`/casos/administracion/cuotas`**: Dashboard administrativo que muestra los presupuestos otorgados mensualmente, el monto ejecutado (pagado) y el saldo disponible para compromisos de pago.

---

## 4. Entidades y Modelos Principales del Dominio

*   **`Registro` (`tbl_registros`)**: Representa la solicitud o caso de un ciudadano (ej. ayuda técnica, salud, asistencia social).
*   **`Pago` (`tbl_pagos`)**: Contiene la información financiera del desembolso: número de orden de pago, fecha, montos, saldo deudor y saldo acreedor.
*   **`Proveedor` (`tbl_proveedor` y `tbl_pago_proveedor`)**: Compañías o prestadores de servicio que proveen los bienes/servicios del caso de asistencia.
*   **`Recaudo` (`tbl_recaudos`)**: Documentos escaneados o comprobantes adjuntos en PDF/Imágenes que respaldan legalmente el pago.
*   **`CuotaCompromiso` (`tbl_cuotas_compromiso`)**: Presupuestos mensuales otorgados a la institución para gestionar compromisos de ayudas.
