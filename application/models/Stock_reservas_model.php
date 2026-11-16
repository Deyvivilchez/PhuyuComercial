<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Reservas de pedidos activos de Restobar; nunca modifica existencias. */
class Stock_reservas_model extends CI_Model
{
    public function bloquear($productos)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $productos))));
        sort($ids, SORT_NUMERIC);
        if (!$ids) { return; }
        // Todas las unidades se bloquean en el mismo orden dentro de la transacción del llamador.
        $this->db->query('SELECT codproducto FROM almacen.productoubicacion
            WHERE codalmacen=? AND codproducto IN ('.implode(',', $ids).')
            ORDER BY codproducto, codunidad FOR UPDATE', [(int)$_SESSION['phuyu_codalmacen']]);
    }

    public function unidades($codproducto, $excluirPedido = 0)
    {
        return $this->db->query("SELECT pu.codunidad, u.descripcion AS unidad,
            p.descripcion AS producto, p.controlstock,
            COALESCE(NULLIF(pun.factor,0), NULLIF(pu.factor,0),1) AS factor,
            COALESCE(pu.stockactualconvertido,0) AS stock,
            COALESCE((SELECT SUM(
                GREATEST(pd.cantidad-COALESCE(pd.cantidadcomprobante,0),0)
                * COALESCE(NULLIF(ru.factor,0),1))
                FROM kardex.pedidos pedi
                JOIN kardex.pedidosdetalle pd ON pd.codpedido=pedi.codpedido AND pd.estado=1
                JOIN almacen.productounidades ru ON ru.codproducto=pd.codproducto AND ru.codunidad=pd.codunidad
                WHERE pedi.estado=1 AND pedi.codalmacen=pu.codalmacen
                  AND pedi.codsucursal=? AND pedi.codpedido<>?
                  AND pd.codproducto=pu.codproducto
                  AND EXISTS (SELECT 1 FROM restaurante.mesaspedido mp
                      WHERE mp.codpedido=pedi.codpedido AND mp.estado=1)
            ),0) / COALESCE(NULLIF(pun.factor,0),NULLIF(pu.factor,0),1) AS reservado
            FROM almacen.productoubicacion pu
            JOIN almacen.productos p ON p.codproducto=pu.codproducto
            JOIN almacen.unidades u ON u.codunidad=pu.codunidad
            LEFT JOIN almacen.productounidades pun ON pun.codproducto=pu.codproducto AND pun.codunidad=pu.codunidad
            WHERE pu.codalmacen=? AND pu.codproducto=? AND pu.estado=1",
            [(int)$_SESSION['phuyu_codsucursal'], (int)$excluirPedido,
             (int)$_SESSION['phuyu_codalmacen'], (int)$codproducto])->result_array();
    }

    public function enriquecer($producto)
    {
        $mapa = [];
        foreach ($this->unidades($producto['codproducto']) as $unidad) {
            $mapa[(int)$unidad['codunidad']] = $unidad;
        }
        $unidad = $mapa[(int)($producto['codunidad'] ?? 0)] ?? null;
        $producto['stock_fisico'] = (float)($producto['stock'] ?? 0);
        $producto['reservado'] = $unidad ? round((float)$unidad['reservado'], 3) : 0;
        $producto['stockdisponible'] = round($producto['stock_fisico'] - $producto['reservado'], 3);
        if (!empty($producto['unidades']) && is_string($producto['unidades'])) {
            $unidades = explode(';', $producto['unidades']);
            foreach ($unidades as &$texto) {
                $campos = explode('|', $texto);
                $u = $mapa[(int)$campos[0]] ?? null;
                if ($u && count($campos) > 3) {
                    $campos[3] = round((float)$u['stock'] - (float)$u['reservado'], 3);
                    $texto = implode('|', $campos);
                }
            }
            unset($texto);
            $producto['unidades_disponibles'] = implode(';', $unidades);
        }
        return $producto;
    }

    /** Valida el detalle completo, sumando unidades equivalentes y filas repetidas. */
    public function validar($detalle, $excluirPedido = 0)
    {
        $respuesta = ['success' => true, 'stock' => [], 'producto' => [], 'unidad' => []];
        if ((int)($_SESSION['phuyu_stockalmacen'] ?? 0) !== 1) { return $respuesta; }
        $agrupados = [];
        foreach ($detalle as $item) {
            $item = (object)$item;
            $agrupados[(int)$item->codproducto][] = $item;
        }
        $this->bloquear(array_keys($agrupados));
        foreach ($agrupados as $codproducto => $items) {
            $mapa = [];
            foreach ($this->unidades($codproducto, $excluirPedido) as $u) {
                $mapa[(int)$u['codunidad']] = $u;
            }
            $solicitadoBase = 0;
            foreach ($items as $item) {
                $u = $mapa[(int)$item->codunidad] ?? null;
                if (!$u) {
                    // Permite servicios sin inventario; el control se consulta en servidor.
                    $p = $this->db->query('SELECT controlstock FROM almacen.productos WHERE codproducto=?', [$codproducto])->row_array();
                    if ((int)($p['controlstock'] ?? 1) === 1) {
                        $respuesta['success'] = false;
                        $respuesta['stock'][] = 0;
                        $respuesta['producto'][] = $item->producto ?? 'PRODUCTO SIN EXISTENCIA';
                        $respuesta['unidad'][] = $item->unidad ?? '';
                    }
                    continue;
                }
                $solicitadoBase += (float)$item->cantidad * (float)$u['factor'];
            }
            foreach ($items as $item) {
                $u = $mapa[(int)$item->codunidad] ?? null;
                if (!$u || (int)$u['controlstock'] !== 1) { continue; }
                $disponible = (float)$u['stock'] - (float)$u['reservado'];
                if ($solicitadoBase > $disponible * (float)$u['factor'] + 0.000001) {
                    $respuesta['success'] = false;
                    $respuesta['stock'][] = round($disponible, 3);
                    $respuesta['producto'][] = $u['producto'];
                    $respuesta['unidad'][] = $u['unidad'];
                    break;
                }
            }
        }
        return $respuesta;
    }
}
