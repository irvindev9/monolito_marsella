# Monolito Marsella v2

Sistema de administracion de amenidades y reservaciones para la comunidad residencial "Marsella Etapa 1". Permite a residentes reservar terrazas, consultar directorio y a administradores gestionar usuarios, configuraciones y aprobaciones.

## Stack tecnologico

- **Backend**: Laravel 10.10 (PHP 8.1+) con Eloquent ORM y MySQL
- **Frontend**: Vue 3 (Composition API + `<script setup>`) con Inertia.js
- **Estado**: Pinia (`resources/js/Stores/adminStore.js`)
- **CSS**: Tailwind CSS 3.2 + Bootstrap 5.3 + Bootstrap Icons
- **Auth**: Laravel Sanctum (API) + Breeze (Web)
- **Build**: Vite 4
- **Testing**: Pest 2.16 / PHPUnit 10.1
- **Extras**: vue-final-modal, v-calendar, vue3-toastify, vuedraggable, date-fns, TinyPNG (tinify)

## Comandos principales

```bash
npm run dev          # Servidor de desarrollo Vite
npm run build        # Build de produccion
php artisan serve    # Servidor Laravel
php artisan migrate  # Ejecutar migraciones
php artisan test     # Ejecutar tests (Pest)
```

## Arquitectura

### Backend (Laravel MVC + Inertia)

- **Modelos** (`app/Models/`): User, House, Street, Role, Reservation, Config, Restriction, Directory, Password
- **Controladores** (`app/Http/Controllers/`): ReservationController, UserController, HouseController, ConfigController, RestrictionController, DirectoryController, PasswordController, ProfileController
- **Validaciones custom** (`app/Validations/`): ReservationsValidator con logica de negocio compleja (disponibilidad, temporadas, restricciones, pagos)
- **Middleware custom**: `Admin` para rutas de administrador
- **Comandos**: `app:cancel-reservations-expired` para cancelar reservaciones vencidas

### Frontend (Vue 3 + Inertia)

- **Entry point**: `resources/js/app.js`
- **Pages** (`resources/js/Pages/`): Welcome, Dashboard, Admin, Directory, Profile
- **Componentes admin** (`resources/js/Admin/`): 18 componentes para panel de administracion
- **Componentes reutilizables** (`resources/js/Components/`): Botones, modales, formularios
- **Modales** (`resources/js/Modals/`): Sistema de modales con vue-final-modal
- **Layouts** (`resources/js/Layouts/`): AuthenticatedLayout, GuestLayout

### Rutas

- **Web** (`routes/web.php`): Paginas renderizadas con Inertia (/, /dashboard, /directorio, /admin, /profile)
- **API** (`routes/api.php`): RESTful con Sanctum. Rutas publicas autenticadas y rutas admin protegidas con middleware `admin:sanctum`
- **Auth** (`routes/auth.php`): Login, registro, reset de contrasena (Breeze)
- **Named routes accesibles en frontend** via Ziggy

### Base de datos

- 13 migraciones en `database/migrations/`
- Relaciones clave: User -> House -> Street, Reservation -> House + User, Restriction -> House

## Convenciones de codigo

- **PHP**: PSR-4, PascalCase clases, camelCase metodos, type hints, Eloquent query builder con eager loading
- **Vue**: `<script setup>`, `ref()` para reactividad, PascalCase para componentes
- **API**: REST con JSON responses, rutas en plural (`/reservations`, `/users`)
- **BD**: Foreign keys con convencion `resource_id`, timestamps automaticos, propiedades fillable explicitas

## Logica de negocio clave

- **Reservaciones**: Flujo de aprobacion (pending -> approved/rejected), validacion de temporadas y disponibilidad, tracking de estado de pago, auto-cancelacion de reservaciones no pagadas
- **Configuraciones del sistema** (modelo Config con slugs): `ttps` (horarios por temporada), `sd` (duracion de temporada), `iro` (registro abierto/cerrado), `mdtpr` (dias para pagar)
- **Restricciones**: Bloqueo de fechas/periodos por casa
- **Directorio**: Entradas con fotos, validacion por casa del usuario
- **Passwords**: Sistema de gestion de contrasenas vinculado a reservaciones
