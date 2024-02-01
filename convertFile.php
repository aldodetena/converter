<?php

// Permite el acceso a todos los orígenes mediante CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Inicializa la respuesta como un arreglo vacío
$response = [];

/**
 * Verifica si se ha subido un archivo correctamente.
 * Si no hay archivo, finaliza la ejecución y devuelve un mensaje de error.
 */
if (!isset($_FILES['fileData']['name']) || $_FILES['fileData']['name'] == null) {
    echo json_encode(['success' => false, 'message' => 'No se recibió archivo.']);
    exit();
}

// Almacena los datos del archivo subido para su procesamiento
$fileTmpPath = $_FILES['fileData']['tmp_name'];
$fileTmpType = $_FILES['fileData']['type'];
$fileName = pathinfo($_FILES['fileData']['name']);
$fileType = $fileTmpType ?? null; // Utiliza el tipo de archivo temporal si está disponible
$toFormat = isset($_POST['toFormat']) ? $_POST['toFormat'] : ''; // Formato de destino deseado

// Prepara el nombre y la ruta del archivo de destino
$targetDirectory = "uploads/";
$convertedFileName = $fileName['filename'] . "." . $toFormat;
$convertedFilePath = $targetDirectory . $convertedFileName;

/**
 * Procesa la conversión del archivo subido según su tipo.
 * Utiliza diferentes funciones de conversión basadas en el tipo de archivo.
 */
$result = [];
switch ($fileType) {
    // Lista de casos para diferentes tipos de archivo (imagen, documento, audio, video)
    // Cada caso llama a una función específica de conversión y almacena el resultado.
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
        $result = convertImage($fileTmpPath, $toFormat, $convertedFilePath);
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
        $result = convertDocument($fileTmpPath, $fileTmpType, $toFormat, $convertedFilePath);
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
        $result = convertAudio($fileTmpPath, $toFormat, $convertedFilePath);
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
        $result = convertVideo($fileTmpPath, $toFormat, $convertedFilePath);
        break;
    default:
        $result = ['success' => false, 'message' => 'Tipo de archivo no soportado.'];
        break;
}

// Agrega el resultado de la conversión a la respuesta y la devuelve
$response[] = $result;

echo json_encode($response);

/**
 * Convierte imágenes a diferentes formatos.
 * 
 * @param string $sourcePath Ruta del archivo fuente.
 * @param string $toFormat Formato de destino para la conversión.
 * @param string $destinationPath Ruta del archivo de destino.
 * @return array Resultado de la conversión.
 */
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
            case 'tiff':
                $image->setImageFormat('tiff');
                break;
            case 'webp':
                $image->setImageFormat('webp');
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
                $response = [
                    'success' => false,
                    'message' => 'Formato de imagen no soportado.'
                ];
        }

        if ($toFormat != 'svg'){
            $image->writeImage($destinationPath);
        }
        
        $response = [
            'success' => true,
            'message' => 'Imagen convertida con éxito.',
            'filePath' => $destinationPath
        ];

    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error al convertir la imagen: ' . $e->getMessage()
        ];
    }
}

/**
 * Convierte documentos a diferentes formatos.
 * 
 * @param string $sourcePath Ruta del archivo fuente.
 * @param string $fileTmpType Tipo MIME del archivo fuente.
 * @param string $toFormat Formato de destino para la conversión.
 * @param string $destinationPath Ruta del archivo de destino.
 * @return array Resultado de la conversión.
 */
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
        $response = [
            'success' => false,
            'message' => 'Formato de archivo no soportado para la conversión.'
        ];
    }

    $command = "pandoc --from=$fromFormat --to=$toFormatPandoc -o " . escapeshellarg($destinationPath) . " " . escapeshellarg($sourcePath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response = [
            'success' => false,
            'message' => 'Error al convertir el documento: ' . implode("\n", $output)
        ];
    }

    if (file_exists($destinationPath)) {
        $response = [
            'success' => true,
            'message' => 'Documento convertido con éxito.',
            'filePath' => $destinationPath
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error al convertir el documento.'
        ];
    }
}

/**
 * Convierte audio a diferentes formatos.
 * 
 * @param string $sourcePath Ruta del archivo fuente.
 * @param string $toFormat Formato de destino para la conversión.
 * @param string $destinationPath Ruta del archivo de destino.
 * @return array Resultado de la conversión.
 */
function convertAudio($sourcePath, $toFormat, $destinationPath) {
    global $response;

    $audioExtensions = [
        'mp3' => 'mp3',
        'wav' => 'wav',
        'ogg' => 'ogg',
        'flac' => 'flac',
        'm4a' => 'm4a',
        'aac' => 'aac',
        'opus' => 'opus',
        'alac' => 'm4a',
        'speex' => 'spx',
        'wma' => 'wma'
    ];

    if (!isset($audioExtensions[$toFormat])) {
        $response = [
            'success' => false,
            'message' => 'Formato de audio no soportado.'
        ];
    }

    $outputExt = $audioExtensions[$toFormat];
    $destinationPath = preg_replace('/\.[^.]+$/', '.' . $outputExt, $destinationPath);
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);
    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response = [
            'success' => false,
            'message' => 'Error al convertir el audio: ' . implode("\n", $output)
        ];
    }

    if (file_exists($destinationPath)) {
        $response = [
            'success' => true,
            'message' => 'Audio convertido con éxito.',
            'filePath' => $destinationPath
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error al convertir el audio.'
        ];
    }
}

/**
 * Convierte vídeos a diferentes formatos.
 * 
 * @param string $sourcePath Ruta del archivo fuente.
 * @param string $toFormat Formato de destino para la conversión.
 * @param string $destinationPath Ruta del archivo de destino.
 * @return array Resultado de la conversión.
 */
function convertVideo($sourcePath, $toFormat, $destinationPath) {
    global $response;

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
        $response = [
            'success' => false,
            'message' => 'Formato de video no soportado.'
        ];
    }

    $outputExt = $videoExtensions[$toFormat];
    $destinationPath = preg_replace('/\.[^.]+$/', '.' . $outputExt, $destinationPath);
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);
    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response = [
            'success' => false,
            'message' => 'Error al convertir el video: ' . implode("\n", $output)
        ];
    }

    if (file_exists($destinationPath)) {
        $response = [
            'success' => true,
            'message' => 'Video convertido con éxito.',
            'filePath' => $destinationPath
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error al convertir el video.'
        ];
    }
}

?>