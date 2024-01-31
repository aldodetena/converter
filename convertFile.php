<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$response = [];

// Verifica si hay archivo subido
if (!isset($_FILES['fileData']['name']) || $_FILES['fileData']['name'] == null) {
    echo json_encode(['success' => false, 'message' => 'No se recibió archivo.']);
    exit();
}

// Almacenar los datos del archivo
$fileTmpPath = $_FILES['fileData']['tmp_name'];
$fileTmpType = $_FILES['fileData']['type'];
$fileName = pathinfo($_FILES['fileData']['name']);
$fileType = $fileTmpType ?? null;
$toFormat = isset($_POST['toFormat']) ? $_POST['toFormat'] : '';

// Nombre y ruta del archivo de destino
$targetDirectory = "uploads/";
$convertedFileName = $fileName['filename'] . "." . $toFormat;
$convertedFilePath = $targetDirectory . $convertedFileName;

// Procesar la conversión según el tipo de archivo
$result = [];
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

$response[] = $result;

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
        $response = [
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
        $response = [
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