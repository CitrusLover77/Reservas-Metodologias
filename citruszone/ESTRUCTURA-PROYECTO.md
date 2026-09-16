# Citrus Zone — Guía de estructura del proyecto

Carpeta raíz única para todo el proyecto. Backend y frontend son dos mitades independientes que se comunican por HTTP/JSON — nada de mezclar PHP con el HTML del cliente.

```
citruszone/
├── backend/
│   ├── public/                     ← DocumentRoot del servidor (lo único expuesto a internet)
│   │   └── index.php               ← front controller: único punto de entrada
│   │
│   ├── src/                        ← todo el código PHP, con autoload PSR-4 (namespace App\)
│   │   ├── Http/
│   │   │   ├── Router.php          ← mapea método+ruta → controlador
│   │   │   ├── Request.php
│   │   │   ├── Response.php        ← helpers para responder JSON con status code
│   │   │   └── Middleware/
│   │   │       ├── AuthMiddleware.php   ← valida el JWT, cuelga el usuario en el Request
│   │   │       └── RoleMiddleware.php   ← exige rol admin/superadmin en ciertas rutas
│   │   │
│   │   ├── Controllers/            ← una clase por recurso, métodos FINITOS (index/show/store/...)
│   │   │   ├── AuthController.php
│   │   │   ├── ServicioController.php
│   │   │   ├── ProfesionalController.php
│   │   │   ├── ReservaController.php
│   │   │   ├── HorarioController.php
│   │   │   └── BloqueoController.php
│   │   │
│   │   ├── Services/                ← ACÁ VIVE LA LÓGICA DE NEGOCIO (lo que testea PHPUnit)
│   │   │   ├── AuthService.php          ← registro, login, hash de password, emisión de JWT
│   │   │   ├── DisponibilidadService.php← valida franja laboral, bloqueos, fecha pasada, servicio activo
│   │   │   └── ReservaService.php       ← orquesta: valida disponibilidad + conflictos + persiste en transacción
│   │   │
│   │   ├── Repositories/            ← ÚNICO lugar con SQL/PDO. Sin lógica de negocio acá.
│   │   │   ├── UsuarioRepository.php
│   │   │   ├── ServicioRepository.php
│   │   │   ├── ProfesionalRepository.php
│   │   │   ├── ReservaRepository.php
│   │   │   ├── HorarioRepository.php
│   │   │   └── BloqueoRepository.php
│   │   │
│   │   ├── Models/                  ← objetos de datos simples (DTO), sin comportamiento
│   │   │   ├── Usuario.php
│   │   │   ├── Servicio.php
│   │   │   ├── Profesional.php
│   │   │   ├── Reserva.php
│   │   │   ├── HorarioLaboral.php
│   │   │   └── Bloqueo.php
│   │   │
│   │   ├── Security/
│   │   │   └── Jwt.php              ← firma/verifica tokens (usa firebase/php-jwt)
│   │   │
│   │   ├── Config/
│   │   │   └── Database.php         ← única conexión PDO (singleton), lee credenciales de .env
│   │   │
│   │   └── Support/
│   │       ├── Validator.php        ← validación de payloads de entrada
│   │       └── Exceptions/          ← ValidationException, ConflictException, ForbiddenException...
│   │
│   ├── database/
│   │   ├── migrations/              ← SQL numerado y ordenado, uno por tabla o cambio
│   │   │   ├── 001_create_usuarios.sql
│   │   │   ├── 002_create_servicios.sql
│   │   │   ├── 003_create_profesionales.sql
│   │   │   ├── 004_create_servicios_profesionales.sql   ← tabla puente (N a N)
│   │   │   ├── 005_create_horarios_laborales.sql
│   │   │   ├── 006_create_bloqueos.sql
│   │   │   └── 007_create_reservas.sql                  ← acá va el EXCLUDE constraint anti-solapamiento
│   │   └── seeds/
│   │       └── seed_demo.sql        ← datos de prueba (servicios, profesionales, horarios)
│   │
│   ├── tests/
│   │   ├── Unit/                    ← testean Services con repositorios FAKE/mock, sin tocar la DB real
│   │   │   ├── DisponibilidadServiceTest.php
│   │   │   ├── ReservaServiceTest.php
│   │   │   └── AuthServiceTest.php
│   │   └── Feature/                 ← integración: contra una DB de test real (constraints, transacciones)
│   │       └── ReservaRepositoryTest.php
│   │
│   ├── composer.json                ← autoload PSR-4 + dependencias (firebase/php-jwt, vlucas/phpdotenv, phpunit)
│   ├── phpunit.xml
│   ├── .env.example
│   └── .env                         ← NO se commitea (va en .gitignore)
│
├── frontend/                        ← ya armado, HTML/CSS/JS plano por pantalla
│   ├── assets/
│   │   ├── css/styles.css
│   │   └── js/
│   │       ├── config.js            ← URL del backend
│   │       ├── api.js               ← cliente fetch + JWT
│   │       └── ui.js                ← guards de auth/rol, helpers de UI
│   ├── login.html
│   ├── registro.html
│   ├── inicio.html
│   ├── reservar.html
│   ├── confirmacion.html
│   ├── mis-reservas.html
│   └── admin/
│       ├── panel.html
│       ├── servicios.html
│       ├── horarios.html
│       └── bloqueos.html
│
├── .gitignore
└── README.md                        ← instrucciones para levantar todo (paso 6 de tu pedido original)
```

## Por qué esta división en capas (backend)

El orden de dependencia es siempre el mismo: **Controller → Service → Repository → PDO**. Nunca al revés, y un Controller nunca le habla directo al PDO.

| Capa | Responsabilidad | Qué NO hace |
|---|---|---|
| **Controller** | Lee el `Request`, llama al Service, arma la `Response` JSON | No valida reglas de negocio, no arma SQL |
| **Service** | Reglas de negocio: disponibilidad, conflictos, permisos de cancelación | No sabe de HTTP, no arma SQL directo |
| **Repository** | Consultas preparadas con PDO, una tabla (o entidad) por clase | No decide si algo "está permitido" |
| **Model** | Solo transporta datos entre capas | No tiene lógica |

Esto es lo que hace testeable cada regla de negocio de tu lista sin depender de un servidor HTTP corriendo: en `tests/Unit` le inyectás al Service un repositorio falso (un array en memoria) y probás la regla pura. En `tests/Feature` sí levantás una Postgres de test para validar que el `EXCLUDE` constraint realmente rechaza el solapamiento.

## Mapeo reglas de negocio → dónde viven

- **Autenticación y permisos por rol** → `AuthService` + `AuthMiddleware`/`RoleMiddleware`
- **Disponibilidad** (fecha pasada, fuera de franja, bloqueado, servicio inactivo) → `DisponibilidadService`
- **Conflictos** (solapamiento) → constraint en la tabla `reservas` (DB) + validación previa en `ReservaService` para devolver un error claro antes de intentar el insert
- **Cancelación** (dueño o admin) → `ReservaService::cancelar()`, chequea `reserva.usuario_id === request.user.id || request.user.role !== 'user'`
- **Gestión de servicios/horarios/bloqueos solo admin/superadmin** → `RoleMiddleware` en las rutas de escritura
- **Persistencia transaccional** → `ReservaRepository::crear()` envuelve el insert en `beginTransaction()/commit()/rollBack()`

## Convenciones a mantener

- **Namespace único** `App\` mapeado a `backend/src/` vía PSR-4 en `composer.json` — así `require` desaparece y usás `use App\Services\ReservaService;`.
- **Un archivo = una clase**, incluso para las excepciones custom.
- **Nombres de tabla en español, plural, snake_case** (`horarios_laborales`, `bloqueos`) para que coincidan 1 a 1 con el dominio que ya definiste.
- **Todo el frontend pega solo contra `backend/public/index.php`** (vía `/api/...`), nunca a un archivo `.php` suelto — eso es lo que permite que el Router controle auth y CORS en un solo lugar.
- **`.env` fuera de git**, solo se commitea `.env.example` con las claves sin valores sensibles (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `JWT_SECRET`, `JWT_TTL`).

Con esta estructura ya podés crear las carpetas vacías localmente; a medida que sigamos con los segmentos, cada archivo que te pase va a tener indicada su ruta exacta dentro de este árbol.
