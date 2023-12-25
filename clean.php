<?php

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

// Limpia archivos más antiguos que una hora (3600 segundos)
clearOldFiles('uploads', 3600);
?>