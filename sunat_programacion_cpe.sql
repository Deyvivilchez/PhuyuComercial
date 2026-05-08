-- ============================================================
-- PROGRAMACION AUTOMATICA SUNAT / CPE
-- ============================================================

CREATE SCHEMA IF NOT EXISTS sunat;

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe (
    codprogramacion SERIAL PRIMARY KEY,
    codempresa INTEGER NOT NULL,
    codsucursal INTEGER,
    descripcion VARCHAR(160) NOT NULL,
    modo_envio VARCHAR(20) NOT NULL DEFAULT 'programado'
        CHECK (modo_envio IN ('inmediato', 'programado', 'manual')),
    procesar_facturas INTEGER NOT NULL DEFAULT 1,
    procesar_boletas INTEGER NOT NULL DEFAULT 1,
    procesar_notas_credito INTEGER NOT NULL DEFAULT 1,
    procesar_notas_debito INTEGER NOT NULL DEFAULT 1,
    procesar_resumenes INTEGER NOT NULL DEFAULT 1,
    procesar_bajas INTEGER NOT NULL DEFAULT 1,
    max_intentos INTEGER NOT NULL DEFAULT 3,
    limite_por_ejecucion INTEGER NOT NULL DEFAULT 20,
    activo INTEGER NOT NULL DEFAULT 1,
    estado INTEGER NOT NULL DEFAULT 1,
    creado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    actualizado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_horarios (
    codhorario SERIAL PRIMARY KEY,
    codprogramacion INTEGER NOT NULL REFERENCES sunat.programacion_cpe(codprogramacion) ON DELETE CASCADE,
    hora TIME WITHOUT TIME ZONE NOT NULL,
    accion VARCHAR(30) NOT NULL DEFAULT 'todo'
        CHECK (accion IN ('todo', 'generar_resumen', 'reenviar')),
    estado INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_historial (
    codejecucion SERIAL PRIMARY KEY,
    codprogramacion INTEGER,
    codhorario INTEGER,
    codempresa INTEGER NOT NULL,
    codsucursal INTEGER,
    origen VARCHAR(20) NOT NULL DEFAULT 'auto',
    accion VARCHAR(30) NOT NULL DEFAULT 'todo',
    inicio TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now(),
    fin TIMESTAMP WITHOUT TIME ZONE,
    estado VARCHAR(20) NOT NULL DEFAULT 'procesando',
    cantidad_procesada INTEGER NOT NULL DEFAULT 0,
    cantidad_error INTEGER NOT NULL DEFAULT 0,
    respuesta_sunat TEXT,
    errores TEXT
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_cola (
    codcola SERIAL PRIMARY KEY,
    codprogramacion INTEGER,
    codempresa INTEGER NOT NULL,
    codsucursal INTEGER,
    tipo VARCHAR(30) NOT NULL,
    referencia VARCHAR(80) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    prioridad INTEGER NOT NULL DEFAULT 0,
    intentos INTEGER NOT NULL DEFAULT 0,
    siguiente_intento TIMESTAMP WITHOUT TIME ZONE,
    ultimo_estado_sunat INTEGER,
    ultimo_mensaje TEXT,
    creado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    actualizado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_logs (
    codlog SERIAL PRIMARY KEY,
    codejecucion INTEGER REFERENCES sunat.programacion_cpe_historial(codejecucion) ON DELETE CASCADE,
    nivel VARCHAR(20) NOT NULL DEFAULT 'info',
    referencia VARCHAR(100),
    mensaje TEXT,
    creado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_programacion_cpe_horarios_hora
    ON sunat.programacion_cpe_horarios (hora)
    WHERE estado = 1;

CREATE INDEX IF NOT EXISTS idx_programacion_cpe_cola_pendientes
    ON sunat.programacion_cpe_cola (codempresa, estado, siguiente_intento, prioridad);

CREATE UNIQUE INDEX IF NOT EXISTS uq_programacion_cpe_cola_pendiente
    ON sunat.programacion_cpe_cola (tipo, referencia)
    WHERE estado IN ('pendiente', 'procesando', 'error');

-- Menu CPE: Programacion de envios CPE
INSERT INTO seguridad.modulos (
    codmodulo, descripcion, icono, url, codpadre, orden, estado,
    nuevo, editar, anular, consultar,
    clavenuevo, clavemodificar, claveanular, claveconsultar, codsistema
)
SELECT
    126, 'Programacion envios CPE', 'ri-timer-flash-line',
    'facturacion/programacionsunat', 8, 99, 1,
    1, 1, 1, 1,
    '', '', '', '', 1
WHERE NOT EXISTS (
    SELECT 1 FROM seguridad.modulos WHERE url = 'facturacion/programacionsunat'
);

INSERT INTO seguridad.moduloperfiles (codmodulo, codperfil, nuevo, editar, anular)
SELECT 126, p.codperfil, 1, 1, 1
FROM seguridad.perfiles p
WHERE EXISTS (SELECT 1 FROM seguridad.modulos WHERE codmodulo = 126)
  AND NOT EXISTS (
    SELECT 1
    FROM seguridad.moduloperfiles mp
    WHERE mp.codmodulo = 126
      AND mp.codperfil = p.codperfil
  );
