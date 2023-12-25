let selectedFiles = []; // Almacena los archivos seleccionados

// Función para actualizar la lista de archivos mostrada
function updateFileList() {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    fileInfosContainer.innerHTML = '';

    selectedFiles.forEach(file => {
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

// Función para eliminar un archivo de la lista
function removeFile(event, fileName) {
    event.preventDefault(); // Previene el envío del formulario

    // Filtrar el archivo a eliminar del arreglo selectedFiles
    selectedFiles = selectedFiles.filter(file => file.name !== fileName);

    // Actualizar la lista de archivos mostrada
    updateFileList();

    // Actualiza el input con los archivos restantes
    updateFileInput();
}

// Función para actualizar el input con los archivos seleccionados
function updateFileInput() {
    const fileInput = document.getElementById('fileData');
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

// Función para subir archivos
function uploadFile(fileName) {
    const file = selectedFiles.find(file => file.name === fileName);
    if (!file) return;

    const formData = new FormData();
    formData.append('fileData', file); // Cambia esto para enviar un solo archivo

    const selectedFormat = document.getElementById('format-to').value;
    formData.append('toFormat', selectedFormat);

    const fileIndex = selectedFiles.indexOf(file);
    updateUIForUploadProgress(fileIndex);

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

function updateUIForUploadProgress(index) {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    const btnContainer = fileInfosContainer.querySelectorAll('.file-buttons')[index];
    btnContainer.innerHTML = '<div class="progress-bar"><div class="progress"></div></div>';
}

function updateUIForDownloadLink(fileData, index) {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    const btnContainer = fileInfosContainer.querySelectorAll('.file-buttons')[index];
    if (fileData.success) {
        btnContainer.innerHTML = `<button class="download-btn" onclick="downloadFile('${fileData.filePath}', event)">Descargar</button>`;
    } else {
        btnContainer.innerHTML = `<p>Error: ${fileData.message}</p>`;
    }
}

function updateUIForUploadError(index) {
    const fileInfosContainer = document.getElementById('fileInfosContainer');
    const btnContainer = fileInfosContainer.querySelectorAll('.file-buttons')[index];
    btnContainer.innerHTML = '<p>Error al subir el archivo.</p>';
}

function downloadFile(filePath, event) {
    event.preventDefault();
    event.stopPropagation();
    window.open(filePath, '_blank');
}

document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById('fileData');

    fileInput.addEventListener('change', () => {
        selectedFiles.push(...Array.from(fileInput.files));
        updateFileList();
    });
});
