# converter

## Descripción

Nombre de la Aplicación es una herramienta de conversión de formatos de archivo diseñada para facilitar la transformación de archivos entre diferentes formatos directamente desde tu navegador. Utilizando tecnologías de vanguardia, esta aplicación ofrece una solución intuitiva y eficiente para las necesidades de conversión de archivos.

## Características

### Conversión de Imágenes
Soporte para la conversión entre los siguientes formatos:
- **Formatos de Entrada**: JPEG, PNG, BMP, GIF, TIFF, WEBP, SVG, PDF, EPS, ICO, CUR
- **Formatos de Salida**: JPEG, PNG, BMP, GIF, TIFF, WEBP, SVG, PDF, EPS, ICO, CUR

### Conversión de Documentos
Transforma documentos entre los siguientes formatos:
- **Formatos de Entrada**: DOCX, ODT, Markdown (TXT), CSV, TSV, IPYNB, JSON, RTF, HTML
- **Formatos de Salida**: PDF, DOCX, HTML, Plain Text (TXT), Markdown, PPTX, RTF, IPYNB, JSON

### Conversión de Audio
Convierte entre formatos de audio populares:
- **Formatos de Entrada/Salida**: MP3, WAV, OGG, FLAC, M4A, AAC, OPUS, ALAC, SPEEX (SPX), WMA

### Conversión de Vídeo
Soporta la conversión entre los siguientes formatos de vídeo:
- **Formatos de Entrada/Salida**: MP4, MKV, AVI, WEBM, MOV, FLV, WMV, M4V, 3GP, MPG, MPEG, VOB

## Cómo Empezar

Este proyecto está contenerizado con Docker, lo que facilita su despliegue y ejecución en cualquier entorno que soporte Docker.

### Pre-requisitos

- Docker instalado en tu sistema.

### Construir la Imagen Docker

Para construir la imagen Docker de la aplicación, navega al directorio del proyecto y ejecuta:

```bash
docker build -t nombre-de-tu-imagen .
```

Donde **`nombre-de-tu-imagen`** es el nombre que deseas asignar a tu imagen Docker.

### Crear y Ejecutar el Contenedor
Una vez construida la imagen, puedes crear y ejecutar un contenedor usando:

```bash
docker run -d -p 80:80 --name nombre-del-contenedor nombre-de-tu-imagen
```
Esto iniciará el contenedor en modo "detached" y mapeará el puerto 80 del contenedor al puerto 80 de tu host, permitiéndote acceder a la aplicación a través de **`http://localhost`.**

### Generar Documentación con PHPDocumentor
Si has incluido PHPDocumentor en tu imagen Docker, puedes generar la documentación de tu código PHP ejecutando:

```bash
docker exec nombre-del-contenedor /usr/local/bin/phpDocumentor -d /var/www/html/ -t /var/www/html/docs
```

Esto generará la documentación en el directorio **`/var/www/html/docs`** dentro del contenedor.

