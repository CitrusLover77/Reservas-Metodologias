-- Datos mínimos para poder probar el flujo completo de reserva localmente.

INSERT INTO servicios (nombre, duracion_minutos, precio, activo) VALUES
    ('Corte de cabello', 45, 8000, true),
    ('Coloración', 120, 22000, true),
    ('Manicura', 40, 6000, true),
    ('Depilación facial', 30, 5000, true);

INSERT INTO profesionales (nombre, activo) VALUES
    ('Valentina Ríos', true),
    ('Martín Gómez', true),
    ('Lucía Fernández', true);

-- Cada profesional habilitado para algunos servicios (no todos hacen todo).
INSERT INTO servicios_profesionales (servicio_id, profesional_id) VALUES
    (1, 1), (1, 2),           -- Corte: Valentina y Martín
    (2, 1),                   -- Coloración: solo Valentina
    (3, 3),                   -- Manicura: solo Lucía
    (4, 2), (4, 3);           -- Depilación facial: Martín y Lucía

-- Horario laboral de lunes (1) a viernes (5), 9 a 18, para los 3 profesionales.
INSERT INTO horarios_laborales (profesional_id, dia_semana, hora_inicio, hora_fin)
SELECT p.id, dia, '09:00', '18:00'
FROM profesionales p, generate_series(1, 5) AS dia;

-- Un bloqueo general de ejemplo (cierre por capacitación del salón).
INSERT INTO bloqueos (profesional_id, fecha, hora_inicio, hora_fin, motivo) VALUES
    (NULL, CURRENT_DATE + INTERVAL '7 days', '09:00', '13:00', 'Capacitación del equipo');

-- Usuario admin de ejemplo. El hash de abajo es un PLACEHOLDER, no es válido:
-- generá el real corriendo esto una vez (por ejemplo con `php -a`) y pegando el resultado:
--   echo password_hash('admin1234', PASSWORD_BCRYPT);
-- INSERT INTO usuarios (nombre, email, password_hash, telefono, role) VALUES
--     ('Admin Citrus Zone', 'admin@citruszone.test', '<pegar_hash_generado_aca>', NULL, 'admin');
