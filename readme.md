# Sistema de Reservas para Salon de Estetica

## Introduccion

Este proyecto consiste en el desarrollo de un sistema web de reservas para un salon de estetica. La aplicacion permitira que los clientes consulten servicios disponibles, vean horarios reales y soliciten turnos online. A su vez, el salon podra administrar servicios, horarios, profesionales, bloqueos de agenda y reservas desde un panel interno.

El objetivo principal es aprender y demostrar la implementacion de logica de negocio en backend, validada mediante testing automatizado. El proyecto servira tanto como trabajo academico para metodologia de pruebas como pieza de portfolio.

---

## Enfoque del Proyecto

El proyecto prioriza el backend, las reglas de negocio y los tests. La interfaz sera simple en una primera etapa, suficiente para consumir la API y demostrar los flujos principales.

La idea no es construir solamente un CRUD, sino un sistema con reglas reales de agenda:

- servicios con duracion y precio
- horarios laborales reales
- profesionales disponibles
- reservas con estado
- cancelaciones
- bloqueos de horarios
- validacion de conflictos
- roles de usuario

---

## Modelo de Negocio

El sistema esta orientado a un salon de estetica que ofrece servicios como manicura, pedicura, cejas, pestanas, depilacion, tratamientos faciales u otros servicios similares.

Los clientes pueden elegir un servicio, consultar horarios disponibles y reservar un turno. El salon puede gestionar la disponibilidad, revisar reservas y bloquear horarios cuando sea necesario.

### Valor del Sistema

- Reduce la gestion manual de turnos.
- Evita reservas duplicadas o solapadas.
- Permite organizar la agenda por profesional.
- Mejora la experiencia del cliente.
- Centraliza servicios, horarios y reservas.
- Permite demostrar reglas de negocio testeadas con PHPUnit.

---

## Stack Tecnico

### Frontend

- React
- TypeScript
- Vite
- CSS simple o CSS Modules
- React Router
- TanStack Query para consumir la API
- Zod opcional para validar formularios y respuestas

### Backend

- PHP nativo 8.2+
- Composer
- PostgreSQL
- PDO para acceso a base de datos
- PHPUnit para testing
- Dotenv para variables de entorno
- API REST con respuestas JSON
- Arquitectura por capas

### Herramientas Recomendadas

- Git y GitHub
- Docker Compose para levantar PostgreSQL de forma local
- Postman, Insomnia o Bruno para probar endpoints
- PHPStan o Psalm mas adelante para analisis estatico
- GitHub Actions mas adelante para ejecutar tests automaticamente

### Base de Datos

Se usara PostgreSQL. Encaja bien con el enfoque del proyecto porque es una base de datos relacional robusta, muy usada en proyectos reales y adecuada para practicar reglas de negocio, integridad referencial y pruebas de integracion.

---

## Roles del Sistema

### Superadmin

Representa al administrador general del sistema. En este proyecto seria el rol del desarrollador o propietario tecnico.

Puede:

- gestionar usuarios
- gestionar admins
- gestionar servicios
- gestionar profesionales
- ver todas las reservas
- modificar configuraciones generales

### Admin

Representa al encargado o dueno del salon.

Puede:

- crear, editar y desactivar servicios
- definir horarios laborales
- bloquear horarios
- ver reservas del salon
- cambiar estados de reservas

### User

Representa al cliente final.

Puede:

- registrarse e iniciar sesion
- ver servicios disponibles
- consultar turnos disponibles
- reservar un turno
- ver sus propias reservas
- cancelar sus propias reservas

---

## Entidades Principales

### Usuario

- id
- name
- email
- password_hash
- role
- created_at

### Servicio

- id
- name
- description
- duration_minutes
- price
- active

### Profesional

- id
- name
- active

### Horario Laboral

- id
- professional_id
- day_of_week
- start_time
- end_time

### Reserva

- id
- user_id
- service_id
- professional_id
- start_datetime
- end_datetime
- status
- created_at
- cancelled_at

Estados posibles:

- pending
- confirmed
- cancelled
- completed

### Bloqueo de Horario

- id
- professional_id
- start_datetime
- end_datetime
- reason

---

## Reglas de Negocio

Estas reglas constituyen el motor del sistema y deben ser validadas mediante testing automatizado.

- No se pueden reservar turnos en el pasado.
- No se puede reservar fuera del horario laboral.
- No se puede reservar un servicio inactivo.
- La duracion del turno depende del servicio elegido.
- No se puede reservar si el profesional ya tiene otra reserva en ese rango horario.
- No se puede reservar si el horario esta bloqueado.
- Una reserva activa puede cancelarse.
- Una reserva cancelada no puede volver a cancelarse.
- Cancelar una reserva libera el horario.
- Un usuario solo puede ver y cancelar sus propias reservas.
- Un admin puede ver todas las reservas del salon.
- Solo admin o superadmin pueden crear, editar o desactivar servicios.
- Solo admin o superadmin pueden definir horarios laborales.
- Solo admin o superadmin pueden bloquear horarios.

---

## Casos de Uso

### Registrar Usuario

Permite que un cliente cree una cuenta dentro del sistema antes de realizar reservas.

Flujo esperado:

1. El cliente ingresa nombre, email, password y telefono opcional.
2. El backend valida que el email tenga formato correcto y no exista previamente.
3. El password se guarda como hash, nunca como texto plano.
4. El usuario se crea con rol `user`.
5. El sistema devuelve una respuesta JSON indicando registro exitoso.

Este flujo permite que cada reserva quede asociada a un usuario real y que luego pueda consultar o cancelar solamente sus propios turnos.

### Iniciar Sesion

Permite autenticar usuarios y diferenciar permisos segun el rol.

Flujo esperado:

1. El usuario ingresa email y password.
2. El backend busca el usuario por email.
3. Se compara el password ingresado contra el hash guardado.
4. Si las credenciales son validas, el sistema identifica el rol del usuario.
5. La API permite o rechaza acciones segun el rol.

En la primera etapa del backend se puede implementar autenticacion simple o simulada para no bloquear el avance del sistema. Mas adelante puede reemplazarse por sesiones o tokens.

### Flujo de Autenticacion en el Mockup

El mockup incluye un bloque de acceso visible antes de confirmar una reserva.

La idea de UX es:

- Un visitante puede ver servicios, profesionales y horarios disponibles.
- Para confirmar una reserva debe iniciar sesion o registrarse.
- Un usuario logueado puede crear reservas y cancelar sus propias reservas.
- Un admin o superadmin accede a la gestion interna del salon.

Esto refleja una regla importante del sistema: consultar disponibilidad puede ser publico, pero reservar, cancelar o administrar requiere identidad y permisos.

### Ver Servicios Disponibles

Permite consultar los servicios activos del salon.

### Consultar Horarios Disponibles

Permite ver horarios libres para un servicio y profesional determinados.

### Reservar Turno

Permite crear una reserva validando disponibilidad, horario laboral, bloqueos y conflictos.

### Cancelar Reserva

Permite cancelar una reserva existente y liberar el horario correspondiente.

odex### Ver Reservas del Usuario

Permite que un cliente consulte su historial de reservas.

### Gestionar Servicios

Permite que admin o superadmin creen, editen o desactiven servicios.

### Gestionar Horarios

Permite que admin o superadmin definan horarios laborales y bloqueos de agenda.

### Ver Reservas del Salon

Permite que admin o superadmin consulten las reservas generales.

---

## MVP

La primera version debe ser acotada y enfocada en backend/testing.

1. Registro e inicio de sesion.
2. Roles: superadmin, admin y user.
3. CRUD basico de servicios para admin.
4. Definicion simple de horarios laborales.
5. Consulta de turnos disponibles.
6. Creacion de reservas.
7. Cancelacion de reservas.
8. Panel admin simple para ver reservas.
9. Tests unitarios e integracion con PHPUnit.

---

## Arquitectura del Backend

El backend usara PHP nativo organizado por capas para aprender las bases sin depender de un framework.

Estructura recomendada:

```text
backend/
  public/
    index.php
  src/
    Controllers/
    Services/
    Repositories/
    Models/
    Database/
    Middleware/
    Exceptions/
  tests/
    Unit/
    Integration/
  config/
  composer.json
  phpunit.xml
```

### Responsabilidades

#### Controllers

Reciben la request, validan datos basicos, llaman a los services y devuelven respuestas JSON.

#### Services

Contienen la logica de negocio. Esta capa debe concentrar las reglas importantes y ser la mas testeada.

#### Repositories

Se encargan de leer y escribir datos en PostgreSQL mediante PDO.

#### Models

Representan las entidades principales del sistema.

#### Middleware

Se encarga de autenticacion, autorizacion y validaciones transversales.

#### Exceptions

Centralizan errores de negocio, errores de validacion y errores de permisos.

---

## Estrategia de Testing

El sistema sera validado mediante PHPUnit.

### Pruebas Unitarias

Se enfocan en services y reglas de negocio sin depender directamente de la base de datos real.

Clases sugeridas:

- ReservationServiceTest
- AvailabilityServiceTest
- CancelReservationTest
- AuthorizationServiceTest

### Pruebas de Integracion

Validan flujos completos con repositorios y persistencia.

Flujos sugeridos:

1. Crear servicio.
2. Definir horario laboral.
3. Consultar disponibilidad.
4. Reservar turno.
5. Intentar reservar el mismo horario.
6. Cancelar reserva.
7. Confirmar que el horario vuelve a estar disponible.

---

## Casos de Prueba Iniciales

### Caso 1 - Reservar Turno Disponible

Un usuario reserva un turno libre dentro del horario laboral.

Resultado esperado: la reserva se crea correctamente y el horario deja de estar disponible.

### Caso 2 - Reservar Turno Ocupado

Un usuario intenta reservar un horario ya ocupado.

Resultado esperado: el sistema rechaza la reserva.

### Caso 3 - Reservar Fuera del Horario Laboral

Un usuario intenta reservar un turno fuera del horario definido para el profesional.

Resultado esperado: el sistema rechaza la reserva.

### Caso 4 - Reservar en el Pasado

Un usuario intenta reservar una fecha u hora anterior al momento actual.

Resultado esperado: el sistema rechaza la reserva.

### Caso 5 - Cancelar Reserva Activa

Un usuario cancela una reserva vigente propia.

Resultado esperado: la reserva cambia a estado cancelado y el horario vuelve a quedar disponible.

### Caso 6 - Cancelar Reserva Cancelada

Un usuario intenta cancelar una reserva que ya estaba cancelada.

Resultado esperado: el sistema rechaza la operacion.

### Caso 7 - Cancelar Reserva Ajena

Un usuario intenta cancelar una reserva de otro usuario.

Resultado esperado: el sistema rechaza la operacion por permisos.

### Caso 8 - Gestionar Servicios sin Permisos

Un user intenta crear o editar un servicio.

Resultado esperado: el sistema rechaza la operacion por permisos.

---

## Enfoque para Portfolio

El proyecto debe presentarse como un sistema de reservas para salon de estetica desarrollado con PHP nativo, PostgreSQL y React, enfocado en reglas de negocio testeadas con PHPUnit y arquitectura backend por capas.

Puntos a destacar:

- reglas de negocio claras
- arquitectura backend por capas
- uso de PHP nativo para aprender fundamentos
- testing automatizado con PHPUnit
- API REST
- roles y permisos
- horarios reales y validacion de disponibilidad
- frontend simple pero funcional

---

## Prompt Base para una IA de Codigo

Este prompt puede usarse mas adelante para pedirle a una IA que implemente el proyecto:

```text
Construir un sistema de reservas para salon de estetica usando PHP nativo 8.2, PostgreSQL, Composer, PDO y PHPUnit en backend, con arquitectura por capas: Controllers, Services, Repositories, Models, Middleware y Exceptions.

El frontend debe usar React, TypeScript y Vite, con una UI simple y funcional.

Priorizar backend y testing. Implementar roles superadmin, admin y user. El sistema debe permitir gestionar servicios, profesionales, horarios laborales, disponibilidad, reservas, cancelaciones y bloqueos de horario.

Las reglas de negocio deben estar en Services y cubiertas por tests unitarios e integracion con PHPUnit.

Usar PostgreSQL como base de datos. La API debe responder JSON y seguir un estilo REST.
```

---

## Conclusion

El sistema busca resolver una necesidad real de organizacion de turnos para un salon de estetica, pero tambien funcionar como proyecto pedagogico. La prioridad es entender como modelar reglas de negocio, separar responsabilidades en capas y validar el comportamiento mediante pruebas automatizadas.

Una primera version simple, bien testeada y bien documentada sera mas valiosa para portfolio que una aplicacion grande sin estructura clara.

---

## Estado Actual y Proximos Pasos

Fecha de ultima definicion: 13/05/2026.

Decision tomada:

- Se arranca por el backend API.
- No se implementa frontend en esta primera etapa.
- Se usara PHP nativo 8.2 o superior.
- Se usara PostgreSQL como base de datos.
- Se usara PDO para conectar PHP con PostgreSQL.
- Se usara Docker Compose para levantar PostgreSQL localmente.
- Se usara Composer para dependencias.
- Se usara PHPUnit para pruebas automatizadas.

Spec de diseno creada:

```text
docs/superpowers/specs/2026-05-13-backend-api-postgresql-design.md
```

Pendiente antes de implementar:

1. Inicializar git en esta carpeta.
2. Revisar la spec de diseno.
3. Confirmar que el alcance esta aprobado.
4. Crear el plan de implementacion.
5. Empezar a generar el backend.

Comandos sugeridos para iniciar git:

```bash
git init
git add .
git commit -m "Document initial backend API design"
```

Proxima tarea recomendada:

Crear la estructura inicial del backend:

```text
backend/
  public/
  src/
  database/
  tests/
  config/
```

Luego configurar:

- `composer.json`
- `phpunit.xml`
- `docker-compose.yml`
- conexion PDO a PostgreSQL
- migraciones iniciales
- endpoint `GET /health`
- primer test automatizado
