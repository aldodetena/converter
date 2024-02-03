<?php

/**
 * Elimina archivos más antiguos que un límite de edad especificado dentro de un directorio.
 *
 * @param string $directory Ruta del directorio donde se buscarán los archivos a eliminar.
 * @param int $ageLimitSeconds Límite de edad de los archivos en segundos.
 */
function clearOldFiles($directory, $ageLimitSeconds) {
    $current_time = time();
    $files = glob($directory . '/*');

    foreach ($files as $file) {
        if (is_file($file)) {
            $file_age = $current_time - filemtime($file);
            if ($file_age > $ageLimitSeconds) {
                unlink($file);
            }
        }
    }
}

// Limpia archivos más antiguos que una hora (3600 segundos) en el directorio 'uploads'
clearOldFiles('uploads', 3600);