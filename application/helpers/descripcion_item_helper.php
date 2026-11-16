<?php
// Dividir texto de servicio en filas pequeñas: Dompdf no divide celdas entre paginas.
// El contenido sigue siendo texto plano; el escape se realiza en cada plantilla.
if (!function_exists('phuyu_descripcion_bloques')) {
    function phuyu_descripcion_bloques($texto, $limite = 450) {
        $texto = str_replace(["\r\n", "\r"], "\n", trim((string)$texto));
        $bloques = [];
        while (mb_strlen($texto, 'UTF-8') > $limite || substr_count($texto, "\n") >= 8) {
            $parte = mb_substr($texto, 0, $limite, 'UTF-8');
            $corte = max((int)mb_strrpos($parte, ' ', 0, 'UTF-8'), (int)mb_strrpos($parte, "\n", 0, 'UTF-8'));
            if ($corte < $limite / 2) $corte = $limite;
            // Las listas de muchas lineas tambien deben caber en una fila A5.
            if (preg_match('/^(?:[^\n]*\n){8}/u', $parte, $lineas)) {
                $corte = mb_strlen(rtrim($lineas[0], "\n"), 'UTF-8');
                if ($corte === 0) $corte = mb_strlen($lineas[0], 'UTF-8');
            }
            $bloques[] = mb_substr($texto, 0, $corte, 'UTF-8');
            $texto = ltrim(mb_substr($texto, $corte, null, 'UTF-8'));
        }
        if ($texto !== '') $bloques[] = $texto;
        return $bloques;
    }
}
