# Cómo continuar Citrus Zone

## Implementado

- Frontend completo de cliente y administración conectado al cliente HTTP común.
- Autenticación JWT sin conflicto de nombres entre la clase de la aplicación y `firebase/php-jwt`.
- Alta y edición de servicios; listado de profesionales filtrado por servicio.
- Horarios laborales y bloqueos: listado, alta y eliminación de bloqueos.
- Reserva de turnos, cancelación con control de dueño/rol, y protección de concurrencia mediante la restricción `EXCLUDE` de PostgreSQL.
- Disponibilidad en intervalos de 30 minutos: considera profesional habilitado, horario laboral, bloqueos, reservas existentes y fecha/hora pasada.
- Dependencias instaladas y fijadas en `backend/composer.lock`.

## Para levantarlo localmente

1. Copiar `backend/.env.example` como `backend/.env` y cargar credenciales reales de PostgreSQL.
2. Crear la base `citruszone` y ejecutar, en orden, los SQL de `backend/database/migrations/` y luego `database/seeds/seed_demo.sql`.
3. Levantar la API desde `backend` con `composer run serve`.
4. Servir `frontend/` en el origen indicado por `CORS_ALLOWED_ORIGIN` y abrir `login.html`.

## Validación pendiente

No hay `.env` ni base de datos local configurada en este equipo; por eso queda pendiente una prueba HTTP de punta a punta contra PostgreSQL.

PHPUnit se ejecuta, pero las 10 pruebas existentes siguen marcadas como `Incomplete` porque el proyecto las traía como esqueletos. El siguiente trabajo recomendado es reemplazarlas por pruebas reales de reglas de negocio y preparar una base de datos aislada para las pruebas de integración.
