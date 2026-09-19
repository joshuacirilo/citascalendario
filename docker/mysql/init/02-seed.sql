INSERT INTO pacientes (nombres, apellidos, telefono, email, fecha_nacimiento) VALUES
    ('Juan', 'Perez', '5555-0101', 'juan.perez@example.test', '1988-04-12'),
    ('Maria', 'Lopez', '5555-0102', 'maria.lopez@example.test', '1993-09-23'),
    ('Carlos', 'Ramirez', NULL, 'carlos.ramirez@example.test', '1979-12-05');

INSERT INTO doctores (nombres, apellidos, especialidad, telefono, email) VALUES
    ('Andrea', 'Garcia', 'Medicina General', '5555-0201', 'andrea.garcia@example.test'),
    ('Luis', 'Morales', 'Cardiologia', '5555-0202', 'luis.morales@example.test'),
    ('Sofia', 'Herrera', 'Pediatria', NULL, 'sofia.herrera@example.test');

INSERT INTO citas (
    paciente_id,
    doctor_id,
    fecha_hora_inicio,
    fecha_hora_fin,
    motivo,
    estado
) VALUES
    (1, 1, '2026-10-05 09:00:00', '2026-10-05 09:30:00', 'Consulta general', 'pendiente'),
    (2, 2, '2026-10-05 10:00:00', '2026-10-05 10:45:00', 'Control cardiologico', 'confirmada'),
    (3, 3, '2026-10-05 11:00:00', '2026-10-05 11:30:00', 'Consulta pediatrica', 'pendiente');
