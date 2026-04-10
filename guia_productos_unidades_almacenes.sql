
/*
========================================================
GUÍA SQL — PRODUCTOS, UNIDADES Y ALMACENES
Propósito:
- Diagnosticar productos/unidades faltantes en almacenes
- Saber en qué almacén faltan registros
- Replicar registros faltantes
- Respetar lógica de líneas por sucursal
========================================================
*/

/*
========================================================
1) UNIDADES DE PRODUCTOS QUE NO ESTÁN EN AMBOS ALMACENES
========================================================
*/
SELECT 
    pu.codproducto,
    p.descripcion AS producto,
    pu.codunidad,
    u.descripcion AS unidad,
    COUNT(DISTINCT pub.codalmacen) AS almacenes_registrados
FROM almacen.productounidades pu
INNER JOIN almacen.productos p 
    ON p.codproducto = pu.codproducto
INNER JOIN almacen.unidades u
    ON u.codunidad = pu.codunidad
LEFT JOIN almacen.productoubicacion pub
    ON pub.codproducto = pu.codproducto
   AND pub.codunidad = pu.codunidad
   AND pub.codalmacen IN (1, 13)
   AND pub.estado = 1
WHERE pu.estado = 1
GROUP BY pu.codproducto, p.descripcion, pu.codunidad, u.descripcion
HAVING COUNT(DISTINCT pub.codalmacen) < 2
ORDER BY p.descripcion, pu.codunidad;


/*
========================================================
2) SABER EN QUÉ ALMACÉN FALTA REGISTRAR
========================================================
*/
SELECT 
    pu.codproducto,
    p.descripcion AS producto,
    pu.codunidad,
    u.descripcion AS unidad,
    a.codalmacen,
    a.descripcion AS almacen_faltante
FROM almacen.productounidades pu
INNER JOIN almacen.productos p
    ON p.codproducto = pu.codproducto
INNER JOIN almacen.unidades u
    ON u.codunidad = pu.codunidad
CROSS JOIN almacen.almacenes a
LEFT JOIN almacen.productoubicacion pub
    ON pub.codproducto = pu.codproducto
   AND pub.codunidad = pu.codunidad
   AND pub.codalmacen = a.codalmacen
   AND pub.estado = 1
WHERE pu.estado = 1
  AND a.codalmacen IN (1, 13)
  AND pub.codproducto IS NULL
ORDER BY p.descripcion, a.codalmacen;


/*
========================================================
3) REPLICAR PRODUCTOS/UNIDADES A TODOS LOS ALMACENES FALTANTES
(NO respeta líneas por sucursal)
========================================================
*/
INSERT INTO almacen.productoubicacion (
    codalmacen,
    codproducto,
    codunidad,
    codsucursal,
    stockactual,
    stockactualreal,
    preciostockvalorizado,
    ventarecogo,
    comprarecogo,
    stockminimo,
    stockmaximo,
    estado,
    stockactualconvertido,
    factor,
    preciocompra,
    pventapublico,
    pventamin,
    pventacredito,
    pventaxmayor,
    pventaadicional,
    preciocosto,
    gastos,
    codigobarra,
    stockpedido,
    stockproveedor,
    codafectacionigvcompra,
    codafectacionigvventa
)
SELECT
    a.codalmacen,
    pu.codproducto,
    pu.codunidad,
    a.codsucursal,
    0,0,0,0,0,0,0,1,0,
    COALESCE(pu.factor,1),
    COALESCE(pu.preciocompra,0),
    COALESCE(pu.pventapublico,0),
    COALESCE(pu.pventamin,0),
    COALESCE(pu.pventacredito,0),
    COALESCE(pu.pventaxmayor,0),
    COALESCE(pu.pventaadicional,0),
    COALESCE(pu.preciocosto,0),
    COALESCE(pu.gastos,0),
    pu.codigobarra,
    0,0,
    a.codafectacionigv,
    a.codafectacionigv
FROM almacen.productounidades pu
INNER JOIN almacen.productos p
    ON p.codproducto = pu.codproducto
INNER JOIN almacen.almacenes a
    ON a.estado = 1
LEFT JOIN almacen.productoubicacion pub
    ON pub.codalmacen = a.codalmacen
   AND pub.codproducto = pu.codproducto
   AND pub.codunidad = pu.codunidad
WHERE pu.estado = 1
  AND p.estado = 1
  AND pub.codproducto IS NULL;


/*
========================================================
4) CREAR UNIDADES POR SUCURSAL RESPETANDO LÍNEAS
========================================================
*/
/*
============================================================
REGISTRAR PRODUCTO + UNIDAD EN ALMACENES PERMITIDOS
SEGÚN LA LÍNEA Y LA SUCURSAL
============================================================

IMPORTANTE:
- NO inserta en almacen.productounidades
- Solo usa las unidades ya existentes
- Inserta en almacen.productoubicacion
- Respeta lineasxsucursales
- No duplica registros
============================================================
*/

INSERT INTO almacen.productoubicacion (
    codalmacen,
    codproducto,
    codunidad,
    codsucursal,
    stockactual,
    stockactualreal,
    preciostockvalorizado,
    ventarecogo,
    comprarecogo,
    stockminimo,
    stockmaximo,
    estado,
    stockactualconvertido,
    factor,
    preciocompra,
    pventapublico,
    pventamin,
    pventacredito,
    pventaxmayor,
    pventaadicional,
    preciocosto,
    gastos,
    codigobarra,
    stockpedido,
    stockproveedor,
    codafectacionigvcompra,
    codafectacionigvventa
)
SELECT
    a.codalmacen,
    pu.codproducto,
    pu.codunidad,
    a.codsucursal,
    0 AS stockactual,
    0 AS stockactualreal,
    0 AS preciostockvalorizado,
    0 AS ventarecogo,
    0 AS comprarecogo,
    0 AS stockminimo,
    0 AS stockmaximo,
    1 AS estado,
    0 AS stockactualconvertido,
    COALESCE(pu.factor,1) AS factor,
    COALESCE(pu.preciocompra,0) AS preciocompra,
    COALESCE(pu.pventapublico,0) AS pventapublico,
    COALESCE(pu.pventamin,0) AS pventamin,
    COALESCE(pu.pventacredito,0) AS pventacredito,
    COALESCE(pu.pventaxmayor,0) AS pventaxmayor,
    COALESCE(pu.pventaadicional,0) AS pventaadicional,
    COALESCE(pu.preciocosto,0) AS preciocosto,
    COALESCE(pu.gastos,0) AS gastos,
    pu.codigobarra,
    0 AS stockpedido,
    0 AS stockproveedor,
    a.codafectacionigv AS codafectacionigvcompra,
    a.codafectacionigv AS codafectacionigvventa
FROM almacen.productounidades pu
INNER JOIN almacen.productos p
    ON p.codproducto = pu.codproducto
INNER JOIN almacen.lineasxsucursales ls
    ON ls.codlinea = p.codlinea
INNER JOIN almacen.almacenes a
    ON a.codsucursal = ls.codsucursal
   AND a.estado = 1
LEFT JOIN almacen.productoubicacion pub
    ON pub.codalmacen = a.codalmacen
   AND pub.codproducto = pu.codproducto
   AND pub.codunidad = pu.codunidad
WHERE pu.estado = 1
  AND p.estado = 1
  AND pub.codproducto IS NULL;
/*
============================================================
5) REGISTRAR PRODUCTO + UNIDAD EN LOS ALMACENES PERMITIDOS
   SEGÚN LA LÍNEA Y LA SUCURSAL
============================================================

Este script inserta registros en la tabla:

    almacen.productoubicacion

Lo que significa que registra que un:

    producto + unidad

existe dentro de un:

    almacén

Solo se insertan registros cuando:

1) El producto pertenece a una línea
2) Esa línea está habilitada en la sucursal
3) El almacén pertenece a esa sucursal
4) El registro aún NO existe en productoubicacion

Esto evita duplicados y mantiene coherencia entre:

producto → línea → sucursal → almacenes
*/
INSERT INTO almacen.productoubicacion (
    codalmacen,
    codproducto,
    codunidad,
    codsucursal,
    stockactual,
    stockactualreal,
    preciostockvalorizado,
    ventarecogo,
    comprarecogo,
    stockminimo,
    stockmaximo,
    estado,
    stockactualconvertido,
    factor,
    preciocompra,
    pventapublico,
    pventamin,
    pventacredito,
    pventaxmayor,
    pventaadicional,
    preciocosto,
    gastos,
    codigobarra,
    stockpedido,
    stockproveedor,
    codafectacionigvcompra,
    codafectacionigvventa
)
SELECT
    a.codalmacen,
    pu.codproducto,
    pu.codunidad,
    a.codsucursal,
    0,0,0,0,0,0,0,1,0,
    COALESCE(pu.factor,1),
    COALESCE(pu.preciocompra,0),
    COALESCE(pu.pventapublico,0),
    COALESCE(pu.pventamin,0),
    COALESCE(pu.pventacredito,0),
    COALESCE(pu.pventaxmayor,0),
    COALESCE(pu.pventaadicional,0),
    COALESCE(pu.preciocosto,0),
    COALESCE(pu.gastos,0),
    pu.codigobarra,
    0,0,
    a.codafectacionigv,
    a.codafectacionigv
FROM almacen.productounidades pu
JOIN almacen.productos p
    ON p.codproducto = pu.codproducto
JOIN almacen.lineasxsucursales ls
    ON ls.codlinea = p.codlinea
   AND ls.codsucursal = pu.codsucursal
JOIN almacen.almacenes a
    ON a.codsucursal = pu.codsucursal
LEFT JOIN almacen.productoubicacion pub
    ON pub.codproducto = pu.codproducto
   AND pub.codunidad = pu.codunidad
   AND pub.codalmacen = a.codalmacen
WHERE pub.codproducto IS NULL;


/*
========================================================
RESPALDOS RECOMENDADOS
========================================================
*/
-- CREATE TABLE backup_productounidades AS SELECT * FROM almacen.productounidades;
-- CREATE TABLE backup_productoubicacion AS SELECT * FROM almacen.productoubicacion;
