# Citrus Zone — Sistema de reservas

Backend PHP (OOP, sin framework) + PostgreSQL, frontend HTML/CSS/JS plano.
Ver `ESTRUCTURA-PROYECTO.md` para el detalle de la arquitectura por capas.

## Estado actual

- ✅ Frontend: `login.html` y `registro.html` ya conectados al backend.
- ✅ Backend: infraestructura completa (router, PDO, JWT, middlewares, excepciones) y `AuthService`/`AuthController` funcionales de punta a punta.
- 🚧 Pendiente (marcado con `TODO` en el código): CRUD de servicios/horarios/bloqueos, cálculo de disponibilidad, cancelación, y el resto de las pantallas del frontend.
- ✅ Tests: 11 tests reales (9 unitarios con repositorios falsos y 2 de integración contra PostgreSQL). Ver la sección "Tests".

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

Los tests unitarios (`tests/Unit`) no necesitan base de datos: los Services reciben sus repositorios por constructor (interfaces en `src/Repositories`), y los tests les pasan repositorios falsos.

`tests/Feature/ReservaRepositoryTest` es de integración y corre contra una base PostgreSQL real, **separada de la de desarrollo**. Vacía las tablas antes de cada caso, por eso se niega a correr si `DB_NAME` no termina en `_test`. Cada persona prepara su base una sola vez:

1. Crear la base y cargar las migraciones:

```bash
   createdb citruszone_test
   for f in database/migrations/*.sql; do
     psql -d citruszone_test -f "$f"
   done
```

En Windows (PowerShell), ajustando la versión de PostgreSQL y la contraseña:

```powershell
   $env:PGPASSWORD = "TU_PASSWORD"
   $psql = "C:\Program Files\PostgreSQL\18\bin\psql.exe"
   & $psql -U postgres -h 127.0.0.1 -c "CREATE DATABASE citruszone_test;"
   Get-ChildItem database\migrations\*.sql | Sort-Object Name | ForEach-Object {
       & $psql -U postgres -h 127.0.0.1 -d citruszone_test -f $_.FullName
   }
```

2. Crear `backend/.env.testing` (está en `.gitignore`, no se sube):

```
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_NAME=citruszone_test
   DB_USER=postgres
   DB_PASS=TU_PASSWORD
```

3. Ejecutar los tests. Tiene que terminar en `OK`, sin tests incompletos.

Si además de `pdo_pgsql` falta la extensión `zip` en PHP, `composer install` falla al descomprimir las librerías: activala en `php.ini`.

## 2. Frontend

Es HTML/CSS/JS plano — no necesita build. Basta con servirlo con cualquier servidor estático (por ejemplo la extensión "Live Server", o `php -S localhost:5500 -t frontend`) y abrir `login.html`.

Si cambiás el puerto o el host del backend, actualizá `frontend/assets/js/config.js` (`API_BASE_URL`) y `CORS_ALLOWED_ORIGIN` en `backend/.env`.

## 3. Próximos pasos sugeridos

1. Completar los métodos `TODO` de `ServicioRepository`, `HorarioRepository`, `BloqueoRepository` y `ReservaRepository::cancelar`.
2. Sumar el endpoint de disponibilidad (`ReservaController::disponibilidad`) para pintar los slots en `reservar.html`.
3. Conectar el resto de las pantallas del frontend (inicio, reservar, confirmación, mis reservas, panel admin).
