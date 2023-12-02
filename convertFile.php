<?php

$response = [];

// Verifica si hay archivos subidos
if (!isset($_FILES['fileData']) || !is_array($_FILES['fileData']['name']) || count($_FILES['fileData']['name']) == 0) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron archivos.']);
    exit();
}

echo '<pre>'; print_r($_FILES['fileData']); echo '</pre>';

$numberOfFiles = count($_FILES['fileData']['name']);

// Asegurarse de que los datos están en formato array
$fileTypes = isset($_POST['fileType']) && is_array($_POST['fileType']) ? $_POST['fileType'] : [];
$toFormats = isset($_POST['toFormat']) && is_array($_POST['toFormat']) ? $_POST['toFormat'] : [];
$fileExtensions = isset($_POST['fileExtension']) && is_array($_POST['fileExtension']) ? $_POST['fileExtension'] : [];

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
    $fileType = $fileTypes[$i] ?? null;
    $toFormat = $toFormats[$i] ?? null;
    $fileExtension = $fileExtensions[$i] ?? null;

    $targetDirectory = "uploads/";
    $convertedFileName = time() . "_{$i}." . $toFormat;
    $convertedFilePath = $targetDirectory . $convertedFileName;

    // Verificación de MIME type
    $allowedMimes = [
        'image' => [
            'image/jpeg',
            'image/png',
            'image/bmp',
            'image/gif',
            'image/tiff',
            'image/webp',
            'image/svg+xml',
            'image/pdf',
            'image/eps',
            'image/ico',
            'image/cur'
        ],
        'document' => [
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/pdf', // .pdf
            'application/vnd.oasis.opendocument.text', // .odt
            'text/plain', // .txt
            'text/csv', // .csv
            'text/tab-separated-values', // .tsv
            'application/x-ipynb+json', // .ipynb (formato común para Jupyter Notebooks)
            'application/json', // .json
            'application/rtf', // .rtf
            'text/html' // .html
        ],
        'audio' => [
            'audio/mpeg', // MP3
            'audio/wav', // WAV
            'audio/ogg', // OGG
            'audio/flac', // FLAC
            'audio/x-m4a', // M4A
            'audio/aac', // AAC
            'audio/opus', // Opus
            'audio/x-ms-wma', // WMA
            'audio/vnd.wave', // Alternativa para WAV
            'audio/webm' // WebM audio
        ],
        'video' => [
            'video/mp4', // MP4
            'video/x-matroska', // MKV
            'video/x-msvideo', // AVI
            'video/webm', // WebM
            'video/quicktime', // MOV
            'video/x-flv', // FLV
            'video/x-ms-wmv', // WMV
            'video/3gpp', // 3GP
            'video/mpeg', // MPEG
            'video/vob' // VOB
        ]
    ];

    switch ($fileType) {
        case 'image':
            $result = convertImage($fileTmpPath, $toFormat, $convertedFilePath);
            break;
        case 'document':
            $result = convertDocument($fileTmpPath, $toFormat, $convertedFilePath);
            break;
        case 'audio':
            $result = convertAudio($fileTmpPath, $toFormat, $convertedFilePath);
            break;
        case 'video':
            $result = convertVideo($fileTmpPath, $toFormat, $convertedFilePath);
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
        sendFile($destinationPath, $toFormat);
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

function convertDocument($sourcePath, $toFormat, $destinationPath) {
    global $response, $fromFormats, $toFormats;

    // Mapeo de la extensión del archivo de origen al formato de Pandoc
    $fromFormats = [
        'docx' => 'docx',
        'odt' => 'odt',
        'txt' => 'markdown',
        'csv' => 'csv',
        'tsv' => 'tsv',
        'ipynb' => 'ipynb',
        'json' => 'json',
        'rtf' => 'rtf',
        'html' => 'html'
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

    $fileExtension = $_POST['fileExtension'];
    $fromFormat = isset($fromFormats[$fileExtension]) ? $fromFormats[$fileExtension] : null;
    $toFormatPandoc = isset($toFormats[$toFormat]) ? $toFormats[$toFormat] : null;

    if (!$fromFormat || !$toFormatPandoc || !$fileExtension) {
        $response['message'] = 'Formato de archivo no soportado para la conversión.';
        return;
    }

    $command = "pandoc --from=$fromFormat --to=$toFormatPandoc -o " . escapeshellarg($destinationPath) . " " . escapeshellarg($sourcePath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response['message'] = 'Error al convertir el documento: ' . implode("\n", $output);
        return;
    }

    // if (file_exists($destinationPath)) {
    //     $response['success'] = true;
    //     $response['message'] = 'Documento convertido con éxito.';
    //     sendFile($destinationPath, $toFormat);
    // } else {
    //     $response['message'] = 'Error al convertir el documento. Archivo no encontrado.';
    // }
    if (file_exists($destinationPath)) {
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
        $response['message'] = 'Formato de audio no soportado.';
        return;
    }

    $outputExt = $audioExtensions[$toFormat];
    $destinationPath = preg_replace('/\.[^.]+$/', '.' . $outputExt, $destinationPath);

    // Comando para convertir el audio
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response['message'] = 'Error al convertir el audio: ' . implode("\n", $output);
        return;
    }

    // if (file_exists($destinationPath)) {
    //     $response['success'] = true;
    //     $response['message'] = 'Audio convertido con éxito.';
    //     sendFile($destinationPath, $toFormat);
    // } else {
    //     $response['message'] = 'Error al convertir el audio.';
    // }

    if (file_exists($destinationPath)) {
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
        $response['message'] = 'Formato de video no soportado.';
        return;
    }

    $outputExt = $videoExtensions[$toFormat];
    $destinationPath = preg_replace('/\.[^.]+$/', '.' . $outputExt, $destinationPath);

    // Comando para convertir el video
    $command = "ffmpeg -i " . escapeshellarg($sourcePath) . " " . escapeshellarg($destinationPath);

    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $response['message'] = 'Error al convertir el video: ' . implode("\n", $output);
        return;
    }

    // if (file_exists($destinationPath)) {
    //     $response['success'] = true;
    //     $response['message'] = 'Video convertido con éxito.';
    //     sendFile($destinationPath, $toFormat);
    // } else {
    //     $response['message'] = 'Error al convertir el video.';
    // }
    if (file_exists($destinationPath)) {
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