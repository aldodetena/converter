<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

clearUploadsDirectory('uploads');

$response = [];

// Verifica si hay archivos subidos
if (!isset($_FILES['fileData']) || !is_array($_FILES['fileData']['name']) || count($_FILES['fileData']['name']) == 0) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron archivos.']);
    exit();
}

$numberOfFiles = count($_FILES['fileData']['name']);

// Asegurarse de que los datos están en formato array
$fileTypes = isset($_FILES['fileData']['type']) && $_FILES['fileData']['type'] != null ? $_FILES['fileData']['type'] : '';
$toFormats = isset($_POST['toFormat']) && $_POST['toFormat'] != null ? $_POST['toFormat'] : '';

for ($i = 0; $i < $numberOfFiles; $i++) {
    if ($_FILES['fileData']['error'][$i] != 0) {
        // Manejar error de subida para este archivo específico
        $response[] = [
            'success' => false,
            'message' => 'Error al subir el archivo: ' . $_FILES['fileData']['error'][$i],
            'fileName' => $_FILES['fileData']['name'][$i]
        ];
        continue;
    }

    $fileTmpPath = $_FILES['fileData']['tmp_name'][$i];
    $fileTmpType = $_FILES['fileData']['type'][$i];
    $fileType = $fileTypes[$i] ?? null;

    $targetDirectory = "uploads/";
    $convertedFileName = time() . "_{$i}." . $toFormats;
    $convertedFilePath = $targetDirectory . $convertedFileName;

    switch ($fileType) {
        case 'image/jpeg': //jpeg
        case 'image/png': //png
        case 'image/bmp': //bmp
        case 'image/gif': //gif
        case 'image/tiff': //tiff
        case 'image/webp': //webp
        case 'image/svg+xml': // svg
        case 'image/pdf': //pdf
        case 'image/eps': //eps
        case 'image/ico': //ico
        case 'image/cur': //cur
            $result = convertImage($fileTmpPath, $toFormats, $convertedFilePath);
            break;
        case 'application/msword': // doc
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // docx
        case 'application/pdf': // pdf
        case 'application/vnd.oasis.opendocument.text': // odt
        case 'text/plain': // txt
        case 'text/csv': // csv
        case 'text/tab-separated-values': // tsv
        case 'application/x-ipynb+json': // ipynb (formato común para Jupyter Notebooks)
        case 'application/json': // json
        case 'application/rtf': // rtf
        case 'text/html': // html
            $result = convertDocument($fileTmpPath, $fileTmpType, $toFormats, $convertedFilePath);
            break;
        case 'audio/mpeg': // MP3
        case 'audio/wav': // WAV
        case 'audio/ogg': // OGG
        case 'audio/flac': // FLAC
        case 'audio/x-m4a': // M4A
        case 'audio/aac': // AAC
        case 'audio/opus': // Opus
        case 'audio/x-ms-wma': // WMA
        case 'audio/vnd.wave': // Alternativa para WAV
        case 'audio/webm': // WebM audio
            $result = convertAudio($fileTmpPath, $toFormats, $convertedFilePath);
            break;
        case 'video/mp4': // MP4
        case 'video/x-matroska': // MKV
        case 'video/x-msvideo': // AVI
        case 'video/webm': // WebM
        case 'video/quicktime': // MOV
        case 'video/x-flv': // FLV
        case 'video/x-ms-wmv': // WMV
        case 'video/3gpp': // 3GP
        case 'video/mpeg': // MPEG
        case 'video/vob': // VOB
            $result = convertVideo($fileTmpPath, $toFormats, $convertedFilePath);
            break;
        default:
            $result = ['success' => false, 'message' => 'Tipo de archivo no soportado.'];
            break;
    }

    $response[] = array_merge($result, ['fileName' => $_FILES['fileData']['name'][$i]]);
}

echo json_encode($response);

function convertImage($sourcePath, $toFormat, $destinationPath) {
    global $response;

    try {
        $image = new Imagick($sourcePath);

        // Revisa el tamaño de la imagen y ajusta si es necesario
        if ($toFormat === 'ico' || $toFormat === 'cur') {
            // Define el tamaño máximo permitido
            $maxSize = 256;
            $image->thumbnailImage($maxSize, $maxSize, true);
        }

        switch ($toFormat) {
            case 'jpg':
                $image->setImageFormat('jpeg');
                $image->setImageCompression(Imagick::COMPRESSION_JPEG);
                $image->setImageCompressionQuality(90);
                break;
            case 'png':
                $image->setImageFormat('png');
                break;
            case 'bmp':
                $image->setImageFormat('bmp');
                break;
            case 'gif':
                $image->setImageFormat('gif');
                break;
            case 'tiff':
                $image->setImageFormat('tiff');
                break;
            case 'webp':
                $image->setImageFormat('webp');
                break;
            case 'svg':
                // Convertir a PNM usando ImageMagick para preparar la imagen para potrace
                $pnmPath = tempnam(sys_get_temp_dir(), 'convert') . '.pnm';
                $image->writeImage($pnmPath);

                // Ejecutar potrace para convertir de PNM a SVG
                $potraceCommand = "potrace $pnmPath -s -o $destinationPath";
                exec($potraceCommand, $output, $returnVar);
                break;
            case 'pdf':
                $image->setImageFormat('pdf');
                break;
            case 'eps':
                $image->setImageFormat('eps');
                break;
            case 'ico':
                $image->setImageFormat('ico');
                break;
            case 'cur':
                $image->setImageFormat('cur');
                break;
            default:
                return [
                    'success' => false,
                    'message' => 'Formato de imagen no soportado.'
                ];
        }

        if ($toFormat != 'svg'){
            $image->writeImage($destinationPath);
        }

        sendFile($destinationPath);
        return [
            'success' => true,
            'message' => 'Imagen convertida con éxito.',
            'filePath' => $destinationPath
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al convertir la imagen: ' . $e->getMessage()
        ];
    }
}

function convertDocument($sourcePath, $fileTmpType, $toFormat, $destinationPath) {
    global $response;

    // Mapeo de tipos MIME al formato de Pandoc
    $mimeToFormat = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.oasis.opendocument.text' => 'odt',
        'text/plain' => 'markdown',
        'text/csv' => 'csv',
        'text/tab-separated-values' => 'tsv',
        'application/x-ipynb+json' => 'ipynb',
        'application/json' => 'json',
        'application/rtf' => 'rtf',
        'text/html' => 'html'
    ];

    // Mapeo del formato de salida deseado al formato de Pandoc
    $toFormats = [
        'pdf' => 'pdf',
        'docx' => 'docx',
        'html' => 'html',
        'plain' => 'plain',
        'markdown' => 'markdown',
        'pptx' => 'pptx',
        'rtf' => 'rtf',
        'ipynb' => 'ipynb',
        'json' => 'json'
    ];

    $fromFormat = isset($mimeToFormat[$fileTmpType]) ? $mimeToFormat[$fileTmpType] : null;
    $toFormatPandoc = isset($toFormats[$toFormat]) ? $toFormats[$toFormat] : null;

    if (!$fromFormat || !$toFormatPandoc) {
        return [
            'success' => false,
            'message' => 'Formato de archivo no soportado para la conversión.'
        ];
    }

    $command = "pandoc --from=$fromFormat --to=$toFormatPandoc -o " . escapeshellarg($destinationPath) . " " . escapeshellarg($sourcePath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        return [
            'success' => false,
            'message' => 'Error al convertir el documento: ' . implode("\n", $output)
        ];
    }

    if (file_exists($destinationPath)) {
        sendFile($destinationPath);
        return [
            'success' => true,
            'message' => 'Documento convertido con éxito.',
            'filePath' => $destinationPath
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error al convertir el documento.'
        ];
    }
}

function convertAudio($sourcePath, $toFormat, $destinationPath) {
    global $response;

    // Ampliar el mapeo para incluir más formatos de audio
    $audioExtensions = [
        'mp3' => 'mp3',
        'wav' => 'wav',
        'ogg' => 'ogg',
        'flac' => 'flac',
        'm4a' => 'm4a',
        'aac' => 'aac', // Advanced Audio Coding
        'opus' => 'opus', // Opus audio format
        'alac' => 'm4a', // Apple Lossless Audio Codec (usualmente usa la extensión m4a)
        'speex' => 'spx', // Speex audio format
        'wma' => 'wma' // Windows Media Audio
    ];

    if (!isset($audioExtensions[$toFormat])) {
        return [
            'success' => false,
            'message' => 'Formato de audio no soportado.'
        ];
    }

    $outputExt = $audioExtensions[$toFormat];
    $destinationPath = preg_replace('/\.[^.]+$/', '.' . $outputExt, $destinationPath);

    // Comando para convertir el audio
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        return [
            'success' => false,
            'message' => 'Error al convertir el audio: ' . implode("\n", $output)
        ];
    }

    if (file_exists($destinationPath)) {
        sendFile($destinationPath);
        return [
            'success' => true,
            'message' => 'Audio convertido con éxito.',
            'filePath' => $destinationPath
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error al convertir el audio.'
        ];
    }
}

function convertVideo($sourcePath, $toFormat, $destinationPath) {
    global $response;

    // Ampliar el mapeo para incluir más formatos de video
    $videoExtensions = [
        'mp4' => 'mp4',
        'mkv' => 'mkv',
        'avi' => 'avi',
        'webm' => 'webm',
        'mov' => 'mov',
        'flv' => 'flv',
        'wmv' => 'wmv',
        'm4v' => 'm4v',
        '3gp' => '3gp',
        'mpg' => 'mpg',
        'mpeg' => 'mpeg',
        'vob' => 'vob'
    ];

    if (!isset($videoExtensions[$toFormat])) {
        return [
            'success' => false,
            'message' => 'Formato de video no soportado.'
        ];
    }

    $outputExt = $videoExtensions[$toFormat];
    $destinationPath = preg_replace('/\.[^.]+$/', '.' . $outputExt, $destinationPath);

    // Comando para convertir el video
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        return [
            'success' => false,
            'message' => 'Error al convertir el video: ' . implode("\n", $output)
        ];
    }

    if (file_exists($destinationPath)) {
        sendFile($destinationPath);
        return [
            'success' => true,
            'message' => 'Video convertido con éxito.',
            'filePath' => $destinationPath
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error al convertir el video.'
        ];
    }
}

function sendFile($filePath) {
    // Asegurarse de que el archivo existe
    if (file_exists($filePath)) {
        // Establecer los encabezados para la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'. basename($filePath) .'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        flush(); // Vaciar buffers del sistema
        readfile($filePath);
        exit;
    }
}

function clearUploadsDirectory($directory) {
    // Abre el directorio
    $files = glob($directory . '/*');

    // Recorre los archivos
    foreach ($files as $file) {
        // Asegúrate de que es un archivo y no un directorio
        if (is_file($file)) {
            // Elimina el archivo
            unlink($file);
        }
    }
}
?>