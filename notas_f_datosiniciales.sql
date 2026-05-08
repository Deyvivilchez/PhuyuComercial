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
/*
public.personas, 
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

UPDATE caja.comprobantes SET nrocorrelativo = 0, nroinicial = 1;

DELETE FROM seguridad.sucursalusuarios WHERE codusuario > 1;

DELETE FROM seguridad.usuarios WHERE codusuario > 1;
ALTER SEQUENCE seguridad.usuarios_codusuario_seq RESTART WITH 2;
-- SELECT setval('seguridad.usuarios_codusuario_seq', ai_codusuario, true);

DELETE FROM public.empleados WHERE codpersona > 2;

DELETE FROM public.socios WHERE codpersona > 2;

DELETE FROM public.personas WHERE codpersona > 2;
ALTER SEQUENCE public.personas_codpersona_seq RESTART WITH 3;
-- SELECT setval('public.personas_codpersona_seq', ai_codpersona, true);

INSERT INTO almacen.lineas(codlinea, descripcion, abreviatura)
VALUES (0, 'SIN LINEA', 'S/L');

INSERT INTO almacen.lineasxsucursales(codlinea, codsucursal)
VALUES (0, 1);

INSERT INTO almacen.marcas(codmarca, descripcion)
VALUES (0, 'SIN MARCA');

INSERT INTO almacen.familias(codfamilia, descripcion, abreviatura, codlinea)
VALUES (0, 'SIN FAMILIA', 'S/F', 0);

INSERT INTO almacen.clase(codclase, descripcion, abreviatura, codlinea, codfamilia)
VALUES (0, 'SIN CLASE', 'S/C', 0, 0);

INSERT INTO public.zonas(descripcion, codubigeo)
SELECT ub.distrito, su.codubigeo
FROM public.sucursales su
INNER JOIN public.ubigeo ub ON (su.codubigeo = ub.codubigeo AND su.codsucursal = @ai_codsucursal);


INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
VALUES ('SIMPLE', 1, 0, 1);

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
VALUES ('DOBLE', 2, 0, 1);

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
VALUES ('MATRIMONIAL', 2, 0, 1);




SELECT 'BASE DE DATOS CON DATOS INICIALES'::character varying;


/*
Hotel queda reiniciado.
Las habitaciones, ambientes, tipos, tarifas y configuracion se vuelven a crear desde migraciones/configuracion.
Se conserva hotel.caracteristicas.
*/



