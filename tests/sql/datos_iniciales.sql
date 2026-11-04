-- Ejecutar SOLO en una copia aislada de la base con datos de prueba.
-- La funcion limpia toda la base. Nunca usar este archivo en produccion.
\set ON_ERROR_STOP on

DO $$
BEGIN
    IF current_database() <> 'reset_validacion' THEN
        RAISE EXCEPTION 'Estas pruebas solo se permiten en la copia aislada reset_validacion';
    END IF;
END $$;

BEGIN;
DO $$
DECLARE
    v_siguiente BIGINT;
BEGIN
    PERFORM public.f_datosiniciales(1);
    IF EXISTS (SELECT 1 FROM seguridad.usuarios WHERE codusuario > 1)
       OR EXISTS (SELECT 1 FROM seguridad.cajausuarios WHERE codusuario > 1)
       OR EXISTS (SELECT 1 FROM seguridad.sucursalusuarios WHERE codusuario > 1) THEN
        RAISE EXCEPTION 'Quedaron usuarios o permisos antiguos';
    END IF;
    IF (SELECT COUNT(*) FROM almacen.productos WHERE codigo = 'HOTEL-ALOJ') <> 1 THEN
        RAISE EXCEPTION 'No se creo un unico producto base';
    END IF;
    IF EXISTS (SELECT 1 FROM caja.controldiario)
       OR EXISTS (SELECT 1 FROM caja.movimientos)
       OR EXISTS (SELECT 1 FROM kardex.kardex)
       OR EXISTS (SELECT 1 FROM kardex.pedidos)
       OR EXISTS (SELECT 1 FROM public.usuariozonas)
       OR EXISTS (SELECT 1 FROM restaurante.atendidos) THEN
        RAISE EXCEPTION 'Quedaron operaciones o asignaciones antiguas';
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM seguridad.cajausuarios cu
        JOIN caja.cajas c ON c.codcaja=cu.codcaja AND c.codsucursal=cu.codsucursal
        WHERE cu.codusuario=1 AND cu.codsucursal=1 AND cu.estado=1 AND c.estado=1
    ) THEN
        RAISE EXCEPTION 'El administrador no tiene acceso a la caja inicial';
    END IF;
    PERFORM public.f_datosiniciales(1);
    IF (SELECT COUNT(*) FROM almacen.productos) <> 1
       OR (SELECT COUNT(*) FROM hotel.configuraciones) <> 1
       OR (SELECT COUNT(*) FROM hotel.habitacion_tipos) <> 3 THEN
        RAISE EXCEPTION 'El reinicio repetido no es consistente';
    END IF;
    SELECT nextval(pg_get_serial_sequence('seguridad.usuarios','codusuario')) INTO v_siguiente;
    IF v_siguiente <= (SELECT MAX(codusuario) FROM seguridad.usuarios) THEN
        RAISE EXCEPTION 'Secuencia de usuarios incorrecta';
    END IF;
    RAISE NOTICE 'OK: limpieza completa, permisos de caja, datos iniciales y reinicio repetido';
END $$;
SET CONSTRAINTS ALL IMMEDIATE;
ROLLBACK;

BEGIN;
DO $$
DECLARE
    v_usuarios BIGINT;
    v_productos BIGINT;
    v_seq BIGINT;
    v_seq_despues BIGINT;
BEGIN
    SELECT COUNT(*) INTO v_usuarios FROM seguridad.usuarios;
    SELECT COUNT(*) INTO v_productos FROM almacen.productos;
    EXECUTE format('SELECT last_value FROM %s', pg_get_serial_sequence('public.personas','codpersona')) INTO v_seq;
    UPDATE caja.comprobantes SET estado=0 WHERE codsucursal=1;
    BEGIN
        PERFORM public.f_datosiniciales(1);
        RAISE EXCEPTION 'Se acepto una sucursal sin comprobantes';
    EXCEPTION WHEN raise_exception THEN
        IF SQLERRM NOT LIKE 'No existen comprobantes activos%' THEN RAISE; END IF;
    END;
    IF (SELECT COUNT(*) FROM seguridad.usuarios) <> v_usuarios
       OR (SELECT COUNT(*) FROM almacen.productos) <> v_productos THEN
        RAISE EXCEPTION 'Un fallo de validacion elimino datos';
    END IF;
    EXECUTE format('SELECT last_value FROM %s', pg_get_serial_sequence('public.personas','codpersona')) INTO v_seq_despues;
    IF v_seq_despues <> v_seq THEN RAISE EXCEPTION 'Un fallo de validacion cambio las secuencias'; END IF;
    RAISE NOTICE 'OK: validacion previa sin modificar datos ni secuencias';
END $$;
ROLLBACK;

BEGIN;
-- Fixture: empresa y administrador asociados a personas con IDs mayores a 2.
INSERT INTO public.personas
SELECT (jsonb_populate_record(NULL::public.personas,
    to_jsonb(p) || jsonb_build_object('codpersona',900001,'documento','99000001'))).*
FROM public.personas p WHERE codpersona=1;
INSERT INTO public.personas
SELECT (jsonb_populate_record(NULL::public.personas,
    to_jsonb(p) || jsonb_build_object('codpersona',900002,'documento','99000002'))).*
FROM public.personas p WHERE codpersona=2;
INSERT INTO public.empleados
SELECT (jsonb_populate_record(NULL::public.empleados,
    to_jsonb(e) || jsonb_build_object('codpersona',900002))).*
FROM public.empleados e WHERE codpersona=2;
UPDATE seguridad.usuarios SET codempleado=900002 WHERE codusuario=1;
INSERT INTO public.empresas
SELECT (jsonb_populate_record(NULL::public.empresas,
    to_jsonb(e) || jsonb_build_object('codempresa',900001,'codpersona',900001))).*
FROM public.empresas e WHERE codempresa=1;
UPDATE public.sucursales SET codempresa=900001 WHERE codsucursal=1;
UPDATE caja.cajas SET estado=0 WHERE codsucursal=1;
DO $$
DECLARE v_persona BIGINT;
BEGIN
    PERFORM public.f_datosiniciales(1);
    IF NOT EXISTS (SELECT 1 FROM public.personas WHERE codpersona=900001)
       OR NOT EXISTS (SELECT 1 FROM public.empleados WHERE codpersona=900002)
       OR NOT EXISTS (SELECT 1 FROM public.personas WHERE codpersona=900002) THEN
        RAISE EXCEPTION 'No se conservaron las personas de empresa/administrador';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM almacen.productos WHERE codigo='HOTEL-ALOJ' AND codempresa=900001) THEN
        RAISE EXCEPTION 'El producto base no pertenece a la empresa de la sucursal';
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM caja.cajas c JOIN seguridad.cajausuarios cu ON cu.codcaja=c.codcaja
        WHERE c.codsucursal=1 AND c.estado=1 AND cu.codusuario=1 AND cu.estado=1
    ) THEN RAISE EXCEPTION 'No se creo la caja activa con acceso para administrador'; END IF;
    SELECT nextval(pg_get_serial_sequence('public.personas','codpersona')) INTO v_persona;
    IF v_persona <= 900002 THEN RAISE EXCEPTION 'La secuencia no respeta las personas conservadas'; END IF;
    RAISE NOTICE 'OK: IDs altos, empresa de sucursal y creacion de caja con acceso';
END $$;
SET CONSTRAINTS ALL IMMEDIATE;
ROLLBACK;
