// Almacena los archivos seleccionados por el usuario
let selectedFiles = [];

/**
 * Actualiza la lista de archivos mostrada al usuario.
 * Crea elementos DOM para cada archivo seleccionado y los agrega al contenedor de información de archivos.
 */
function updateFileList() {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    fileInfosContainer.innerHTML = '';

    selectedFiles.forEach(file => {
        // Crea y configura el elemento div para mostrar la información del archivo
        const fileElement = document.createElement('div');
        fileElement.classList.add('file-info');
        fileElement.innerHTML = `
            <div class="file-info-content">
                <span class="file-icon">📄</span>
                <span class="file-name">${file.name}</span>
            </div>
            <div class="file-buttons">
                <button class="change-file-btn" onclick="removeFile(event, '${file.name}')">Eliminar</button>
                <button class="change-file-btn" onclick="uploadFile('${file.name}')">Subir</button>
            </div>
        `;
        fileInfosContainer.appendChild(fileElement);
    });
}

/**
 * Elimina un archivo de la lista de archivos seleccionados.
 * @param {Event} event El evento del clic que desencadenó la función.
 * @param {string} fileName El nombre del archivo a eliminar.
 */
function removeFile(event, fileName) {
    event.preventDefault(); // Previene el envío del formulario

    // Filtrar el archivo a eliminar del arreglo selectedFiles
    selectedFiles = selectedFiles.filter(file => file.name !== fileName);

    // Actualizar la lista de archivos mostrada
    updateFileList();

    // Actualiza el input con los archivos restantes
    updateFileInput();
}


/**
 * Actualiza el input de archivos con los archivos actualmente seleccionados.
 * Crea un nuevo objeto DataTransfer para gestionar los archivos seleccionados y lo asigna al input.
 */
function updateFileInput() {
    const fileInput = document.getElementById('fileData');
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

/**
 * Inicia la subida de un archivo seleccionado al servidor.
 * @param {string} fileName El nombre del archivo a subir.
 */
function uploadFile(fileName) {
    const file = selectedFiles.find(file => file.name === fileName);
    if (!file) return; // Si no se encuentra el archivo, termina la función

    // Prepara el formulario para la subida del archivo
    const formData = new FormData();
    formData.append('fileData', file);

    // Obtiene el formato seleccionado por el usuario y lo añade al formulario
    const selectedFormat = document.getElementById('format-to').value;
    formData.append('toFormat', selectedFormat);

    // Encuentra el índice del archivo para actualizar la UI durante la subida
    const fileIndex = selectedFiles.indexOf(file);
    updateUIForUploadProgress(fileIndex);

    // Realiza la petición para subir el archivo
    fetch('convertFile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        updateUIForDownloadLink(data, fileIndex);
    })
    .catch(error => {
        console.error('Error:', error);
        updateUIForUploadError(fileIndex);
    });
}

/**
 * Actualiza la interfaz de usuario para mostrar el progreso de la subida de un archivo.
 * @param {number} index El índice del archivo en la lista de archivos seleccionados.
 */
function updateUIForUploadProgress(index) {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    const btnContainer = fileInfosContainer.querySelectorAll('.file-buttons')[index];
    btnContainer.innerHTML = '<div class="progress-bar"><div class="progress"></div></div>';
}

/**
 * Actualiza la interfaz de usuario para proporcionar un enlace de descarga después de una subida exitosa.
 * @param {Object} fileData Los datos del archivo devueltos por el servidor.
 * @param {number} index El índice del archivo en la lista de archivos seleccionados.
 */
function updateUIForDownloadLink(fileData, index) {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    const btnContainer = fileInfosContainer.querySelectorAll('.file-buttons')[index];
    if (fileData.success) {
        btnContainer.innerHTML = `<button class="download-btn" onclick="downloadFile('${fileData.filePath}', event)">Descargar</button>`;
    } else {
        btnContainer.innerHTML = `<p>Error: ${fileData.message}</p>`;
    }
}

/**
 * Muestra un mensaje de error en la interfaz de usuario si la subida del archivo falla.
 * @param {number} index El índice del archivo en la lista de archivos seleccionados.
 */
function updateUIForUploadError(index) {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    const btnContainer = fileInfosContainer.querySelectorAll('.file-buttons')[index];
    btnContainer.innerHTML = '<p>Error al subir el archivo.</p>';
}

/**
 * Inicia la descarga de un archivo desde el servidor.
 * @param {string} filePath La ruta del archivo para descargar.
 * @param {Event} event El evento del clic que desencadenó la función.
 */
function downloadFile(filePath, event) {
    event.preventDefault(); // Previene el comportamiento predeterminado del evento
    event.stopPropagation(); // Detiene la propagación del evento
    window.open(filePath, '_blank'); // Abre el archivo en una nueva pestaña
}

// Agrega un oyente de eventos para gestionar la selección de archivos
document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById('fileData');

    fileInput.addEventListener('change', () => {
        // Añade los archivos seleccionados a la lista y actualiza la UI
        selectedFiles.push(...Array.from(fileInput.files));
        updateFileList();
    });
});
