-- ============================================================
-- PHUYU COMERCIAL
-- SCRIPT DE DATOS INICIALES / LIMPIEZA
-- ============================================================
-- Objetivo:
--   Reiniciar tablas operativas y recrear datos base del sistema.
--
-- Incluye:
--   * Limpieza de tablas operativas
--   * Reinicio de secuencias
--   * Datos base de almacen
--   * Datos base de hotel
--   * Configuracion inicial
-- ============================================================


-- ============================================================
-- LIMPIEZA GENERAL DE TABLAS
-- ============================================================

TRUNCATE TABLE 
/*
almacen.almacenes, 
*/
almacen.atenciones, 
almacen.clase, 
almacen.familias, 
almacen.guiasr, 
almacen.guiasrdetalle, 
almacen.guiast, 
almacen.guiastdetalle, 
almacen.inventariodetalle, 
almacen.inventarios, 
almacen.kardexguiasr, 
almacen.kardexguiasrdetalle, 
almacen.lineas, 
almacen.lineasxsucursales, 
almacen.marcas, 
/*
almacen.modalidadtraslado, 
almacen.motivotraslado, 
almacen.movimientotipos, 
*/
almacen.presentacion, 
almacen.principioactivo, 
almacen.productos, 
almacen.productoubicacion, 
almacen.productounidades, 
/*
almacen.unidades, 
*/
almacen.vehiculos, 
almacen.vehiculoscategorias, 
almacen.vehiculosmarcas, 
almacen.vehiculosmodelos, 
/*
caja.bancos, 
caja.cajas, 
caja.centrocostos, 
caja.comprobantes, 
caja.comprobantetipos, 
caja.conceptos, 
*/
caja.controldiario, 
caja.ctasctes, 
/*
caja.monedas, 
*/
caja.movimientos, 
caja.movimientosdetalle, 
caja.tipocambios,

/*
Modulo hotel: reinicio completo del modulo.
No truncar hotel.caracteristicas porque es catalogo base reutilizable.
*/
hotel.consumos_habitacion,
hotel.estadia_huespedes,
hotel.estadia_habitaciones,
hotel.estadias,
hotel.reserva_habitaciones,
hotel.reservas,
hotel.limpieza_habitaciones,
hotel.mantenimiento_habitaciones,
hotel.habitacion_caracteristicas,
hotel.habitaciones,
hotel.habitacion_tipos,
hotel.ambientes,
hotel.temporadas,
hotel.tarifas,
hotel.configuraciones,

/*
caja.tipopagos, 
kardex.creditoconceptos, 
*/
kardex.creditos,
kardex.creditospedidos,
kardex.creditosproformas, 
kardex.creditosanulados, 
kardex.cuotas, 
kardex.cuotaspedidos, 
kardex.cuotasproformas, 
kardex.cuotaspagos, 
kardex.kardex, 
kardex.kardexalmacen, 
kardex.kardexalmacenanulado, 
kardex.kardexalmacendetalle, 
kardex.kardexanulados, 
kardex.kardexdetalle, 
kardex.kardexpedido, 
kardex.kardexproforma, 
/*
kardex.motivonotas, 
*/
kardex.pedidos, 
kardex.pedidosdetalle, 
kardex.proformas, 
kardex.proformasdetalle, 
/*
public.areas, 
public.cargos, 
public.documentotipos, 
public.empleados, 
public.empresas,
*/ 
public.lotes, 
/*public.personas, 
public.rubros, 
public.socios, 
public.sociotipos, 
public.sucursales, 

public.ubigeo, 
public.usuariozonas, 
public.webservice, 
*/
public.zonas, 


restaurante.ambientes, 
/*
restaurante.atendidos, 
*/
restaurante.mesas, 
restaurante.mesaskardex, 
restaurante.mesaspedido, 
restaurante.recetas, 
/*
seguridad.moduloperfiles, 
seguridad.modulos, 
seguridad.perfiles, 
seguridad.sucursalusuarios, 
seguridad.usuarios, 
*/
sunat.guiasunat, 
sunat.kardexsunat, 
sunat.kardexsunatanulados, 
sunat.kardexsunatdetalle, 
sunat.resumenes/*, 

sunat.resumentipos 
*/
RESTART IDENTITY CASCADE;


-- ============================================================
-- REINICIO DE CORRELATIVOS
-- ============================================================

UPDATE caja.comprobantes SET nrocorrelativo = 0, nroinicial = 1;


-- ============================================================
-- LIMPIEZA DE USUARIOS Y PERSONAS
-- ============================================================

DELETE FROM seguridad.sucursalusuarios WHERE codusuario > 1;

DELETE FROM seguridad.usuarios WHERE codusuario > 1;
ALTER SEQUENCE seguridad.usuarios_codusuario_seq RESTART WITH 2;
--SELECT setval('seguridad.usuarios_codusuario_seq', ai_codusuario, true);

DELETE FROM public.empleados WHERE codpersona > 2;

DELETE FROM public.socios WHERE codpersona > 2;

DELETE FROM public.personas WHERE codpersona > 2;
ALTER SEQUENCE public.personas_codpersona_seq RESTART WITH 3;
--SELECT setval('public.personas_codpersona_seq', ai_codpersona, true);


-- ============================================================
-- DATOS BASE DE ALMACEN
-- ============================================================

INSERT INTO almacen.lineas(codlinea, descripcion, abreviatura)  VALUES (0, 'SIN LINEA', 'S/L');

-- ============================================================
-- DATOS BASE DE ALMACEN
-- ============================================================

INSERT INTO almacen.lineasxsucursales( codlinea, codsucursal) VALUES (0, 1);

INSERT INTO almacen.marcas(codmarca, descripcion)    VALUES (0, 'SIN MARCA');

INSERT INTO almacen.familias( codfamilia, descripcion, abreviatura, codlinea) VALUES (0, 'SIN FAMILIA', 'S/F', 0);

INSERT INTO almacen.clase( codclase, descripcion, abreviatura, codlinea, codfamilia) VALUES (0, 'SIN CLASE', 'S/C', 0, 0);

INSERT INTO public.zonas( descripcion, codubigeo) 
SELECT ub.distrito, su.codubigeo FROM public.sucursales su INNER JOIN public.ubigeo ub ON (su.codubigeo=ub.codubigeo AND su.codsucursal = @ai_codsucursal);


-- ============================================================
-- DATOS BASE MODULO HOTEL
-- ============================================================

/*
Datos base modulo hotel.
Se conserva hotel.caracteristicas.
*/
INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
VALUES ('SIMPLE', 1, 0, 1);

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
VALUES ('DOBLE', 2, 0, 1);

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
VALUES ('MATRIMONIAL', 2, 0, 1);

/*
Producto base obligatorio para facturar alojamiento hotel.
*/
WITH unidad_base AS (
    SELECT codunidad
    FROM almacen.unidades
    WHERE estado = 1
    ORDER BY codunidad
    LIMIT 1
),
producto_alojamiento AS (
    INSERT INTO almacen.productos (
        descripcion,
        codlinea,
        codfamilia,
        codclase,
        codmarca,
        controlstock,
        controlarseries,
        afectoigvventa,
        afectoicbper,
        calcular,
        estado
    )
    SELECT
        'ALOJAMIENTO HOTEL',
        0,
        0,
        0,
        0,
        0,
        0,
        1,
        0,
        1,
        1
    FROM unidad_base
    RETURNING codproducto
),
producto_unidad AS (
    INSERT INTO almacen.productounidades (
        codproducto,
        codunidad,
        pventapublico,
        estado
    )
    SELECT
        pa.codproducto,
        ub.codunidad,
        0,
        1
    FROM producto_alojamiento pa
    CROSS JOIN unidad_base ub
    RETURNING codproducto, codunidad
)
INSERT INTO hotel.configuraciones (
    codsucursal,
    codproducto_alojamiento,
    codunidad_alojamiento,
    estado
)
SELECT
    s.codsucursal,
    pu.codproducto,
    pu.codunidad,
    1
FROM public.sucursales s
CROSS JOIN producto_unidad pu
WHERE s.estado = 1;

SELECT 'BASE DE DATOS CON DATOS INICIALES'::character varying;


/*
========================================
PRODUCTO BASE HOTEL: ALOJAMIENTO HOTEL
========================================
*/

WITH unidad_base AS (
    SELECT codunidad
    FROM almacen.unidades
    WHERE estado = 1
    ORDER BY codunidad
    LIMIT 1
),
producto_alojamiento AS (
    INSERT INTO almacen.productos (
        descripcion,
        codlinea,
        codfamilia,
        codclase,
        codmarca,
        controlstock,
        controlarseries,
        afectoigvventa,
        afectoicbper,
        calcular,
        estado
    )
    SELECT
        'ALOJAMIENTO HOTEL',
        0,
        0,
        0,
        0,
        0,
        0,
        1,
        0,
        1,
        1
    FROM unidad_base
    RETURNING codproducto
),
producto_unidad AS (
    INSERT INTO almacen.productounidades (
        codproducto,
        codunidad,
        pventapublico,
        estado
    )
    SELECT
        pa.codproducto,
        ub.codunidad,
        0,
        1
    FROM producto_alojamiento pa
    CROSS JOIN unidad_base ub
    RETURNING codproducto, codunidad
)

INSERT INTO hotel.configuraciones (
    codsucursal,
    codproducto_alojamiento,
    codunidad_alojamiento,
    estado
)
SELECT
    s.codsucursal,
    pu.codproducto,
    pu.codunidad,
    1
FROM public.sucursales s
CROSS JOIN producto_unidad pu
WHERE s.estado = 1;

/*
========================================
FIN PRODUCTO BASE HOTEL: ALOJAMIENTO HOTEL
========================================
*/

-- ============================================================
-- FIN SCRIPT DATOS INICIALES
-- ============================================================
