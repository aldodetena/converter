# converter

## Descripción

Nombre de la Aplicación es una herramienta de conversión de formatos de archivo diseñada para facilitar la transformación de archivos entre diferentes formatos directamente desde tu navegador. Utilizando tecnologías de vanguardia, esta aplicación ofrece una solución intuitiva y eficiente para las necesidades de conversión de archivos.

## Características

- **Conversión de Imágenes**: Soporte para formatos como JPEG, PNG, BMP, GIF, y más.
- **Conversión de Documentos**: Transforma documentos en formatos como DOCX, PDF, ODT, y TXT.
- **Conversión de Audio**: Convierte entre formatos de audio populares como MP3, WAV, OGG, y FLAC.
- **Conversión de Vídeo**: Soporta la conversión entre MP4, AVI, MKV, y otros formatos de vídeo.

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

