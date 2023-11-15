<?php

$response = [
    'success' => false,
    'message' => '',
    'filePath' => ''
];

if (!isset($_FILES['fileData'])) {
    $response['message'] = 'No se recibió ningún archivo.';
    echo json_encode($response);
    exit();
}

if ($_FILES['fileData']['error'] != 0) {
    $response['message'] = 'Error al subir el archivo: ' . $_FILES['fileData']['error'];
    echo json_encode($response);
    exit();
}

$fileData = $_FILES['fileData'];
$fileType = $_POST['fileType'];
$toFormat = $_POST['toFormat'];

$targetDirectory = "uploads/";
$convertedFileName = time() . "." . $toFormat;
$convertedFilePath = $targetDirectory . $convertedFileName;

// Verificación de MIME type
$allowedMimes = [
    'image' => ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/tiff', 'image/webp', 'image/svg+xml', 'image/pdf', 'image/eps', 'image/ico', 'image/cur'],
    'document' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/vnd.oasis.opendocument.text', 'text/plain'],
    'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/flac', 'audio/x-m4a'],
    'video' => ['video/mp4', 'video/x-matroska', 'video/x-msvideo', 'video/webm', 'video/quicktime']
];

if (!in_array($fileData['type'], $allowedMimes[$fileType])) {
    $response['message'] = 'Tipo de archivo no permitido.';
    echo json_encode($response);
    exit();
}

switch ($fileType) {
    case 'image':
        convertImage($fileData['tmp_name'], $toFormat, $convertedFilePath);
        break;

    case 'document':
        convertDocument($fileData['tmp_name'], $toFormat, $convertedFilePath);
        break;

    case 'audio':
        convertAudio($fileData['tmp_name'], $toFormat, $convertedFilePath);
        break;

    case 'video':
        convertVideo($fileData['tmp_name'], $toFormat, $convertedFilePath);
        break;

    default:
        $response['message'] = 'Tipo de archivo no soportado.';
        break;
}

echo json_encode($response);


function convertImage($sourcePath, $toFormat, $destinationPath) {
    global $response;

    try {
        $image = new Imagick($sourcePath);

        switch ($toFormat) {
            case 'jpg':
                $image->setImageFormat('jpeg');
                $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(90);
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
                $image->setImageFormat('svg');
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
                $response['message'] = 'Formato de imagen no soportado.';
                return;
        }

        $image->writeImage($destinationPath);

        $response['success'] = true;
        $response['message'] = 'Imagen convertida con éxito.';
        sendFile($destinationPath, $toFormat);

    } catch (Exception $e) {
        $response['message'] = 'Error al convertir la imagen: ' . $e->getMessage();
    }
}

function convertDocument($sourcePath, $toFormat, $destinationPath) {
    global $response;

    $formatMappings = [
        'pdf' => 'pdf',
        'docx' => 'docx',
        'odt' => 'odt',
        'txt' => 'plain'
    ];

    if (!isset($formatMappings[$toFormat])) {
        $response['message'] = 'Formato de documento no soportado.';
        return;
    }

    $pandocFormat = $formatMappings[$toFormat];
    $command = "pandoc " . escapeshellarg($sourcePath) . " -o " . escapeshellarg($destinationPath) . " --to=" . escapeshellarg($pandocFormat);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response['message'] = 'Error al convertir el documento: ' . implode("\n", $output);
        return;
    }

    if (file_exists($destinationPath)) {
        $response['success'] = true;
        $response['message'] = 'Documento convertido con éxito.';
        sendFile($destinationPath, $toFormat);
    } else {
        $response['message'] = 'Error al convertir el documento. Archivo no encontrado.';
    }
}

function convertAudio($sourcePath, $toFormat, $destinationPath) {
    global $response;

    $audioExtensions = [
        'mp3' => 'mp3',
        'wav' => 'wav',
        'ogg' => 'ogg',
        'flac' => 'flac',
        'm4a' => 'm4a'
    ];

    if (!isset($audioExtensions[$toFormat])) {
        $response['message'] = 'Formato de audio no soportado.';
        return;
    }

    $outputExt = $audioExtensions[$toFormat];

    // Comando para convertir el audio
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response['message'] = 'Error al convertir el audio.';
        return;
    }

    if (file_exists($destinationPath)) {
        $response['success'] = true;
        $response['message'] = 'Audio convertido con éxito.';
        sendFile($destinationPath, $toFormat);
    } else {
        $response['message'] = 'Error al convertir el audio.';
    }
}

function convertVideo($sourcePath, $toFormat, $destinationPath) {
    global $response;

    $videoExtensions = [
        'mp4' => 'mp4',
        'mkv' => 'mkv',
        'avi' => 'avi',
        'webm' => 'webm',
        'mov' => 'mov'
    ];

    if (!isset($videoExtensions[$toFormat])) {
        $response['message'] = 'Formato de video no soportado.';
        return;
    }

    $outputExt = $videoExtensions[$toFormat];

    // Comando para convertir el video
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response['message'] = 'Error al convertir el video.';
        return;
    }

    if (file_exists($destinationPath)) {
        $response['success'] = true;
        $response['message'] = 'Video convertido con éxito.';
        sendFile($destinationPath, $toFormat);
    } else {
        $response['message'] = 'Error al convertir el video.';
    }
}

function sendFile($filePath, $format) {
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

?>