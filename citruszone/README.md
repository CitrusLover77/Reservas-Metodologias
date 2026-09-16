# Citrus Zone — Sistema de reservas

Backend PHP (OOP, sin framework) + PostgreSQL, frontend HTML/CSS/JS plano.
Ver `ESTRUCTURA-PROYECTO.md` para el detalle de la arquitectura por capas.

## Estado actual

- ✅ Frontend: `login.html` y `registro.html` ya conectados al backend.
- ✅ Backend: infraestructura completa (router, PDO, JWT, middlewares, excepciones) y `AuthService`/`AuthController` funcionales de punta a punta.
- 🚧 Pendiente (marcado con `TODO` en el código): CRUD de servicios/horarios/bloqueos, cálculo de disponibilidad, cancelación, y el resto de las pantallas del frontend.
- 🚧 Tests: los `tests/Unit` y `tests/Feature` están armados como esqueleto (`markTestIncomplete`), listos para completarse regla por regla.

## Requisitos

- PHP 8.1+ con extensiones `pdo` y `pdo_pgsql`
- PostgreSQL 14+ (por el `EXCLUDE` constraint con `btree_gist`)
- Composer

## 1. Backend

```bash
cd backend
composer install
cp .env.example .env
# Editar .env con tus credenciales de Postgres y un JWT_SECRET propio
```

### Base de datos

Crear la base y correr las migraciones en orden (no hay migrador todavía, son SQL planos):

```bash
createdb citruszone

for f in database/migrations/*.sql; do
  psql -d citruszone -f "$f"
done

# Opcional: datos de prueba (servicios, profesionales, horarios)
psql -d citruszone -f database/seeds/seed_demo.sql
```

### Levantar el servidor

```bash
composer run serve
# equivalente a: php -S localhost:8000 -t public
```

La API queda en `http://localhost:8000/api/...`.

### Tests

```bash
composer run test
```

## 2. Frontend

Es HTML/CSS/JS plano — no necesita build. Basta con servirlo con cualquier servidor estático (por ejemplo la extensión "Live Server", o `php -S localhost:5500 -t frontend`) y abrir `login.html`.

Si cambiás el puerto o el host del backend, actualizá `frontend/assets/js/config.js` (`API_BASE_URL`) y `CORS_ALLOWED_ORIGIN` en `backend/.env`.

## 3. Próximos pasos sugeridos

1. Completar los métodos `TODO` de `ServicioRepository`, `HorarioRepository`, `BloqueoRepository` y `ReservaRepository::cancelar`.
2. Implementar la validación real de franja laboral + bloqueos en `DisponibilidadService::validar`.
3. Sumar el endpoint de disponibilidad (`ReservaController::disponibilidad`) para pintar los slots en `reservar.html`.
4. Conectar el resto de las pantallas del frontend (inicio, reservar, confirmación, mis reservas, panel admin).
5. Completar los tests marcados como `markTestIncomplete`, inyectando repositorios fake en los Services.
