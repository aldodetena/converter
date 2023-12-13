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
    event.preventDefault(); 
    selectedFiles = selectedFiles.filter(file => file.name !== fileName);
    updateFileList();
}

// Función para subir un archivo
function uploadFile(fileName) {
    const file = selectedFiles.find(file => file.name === fileName);
    if (!file) return;

    const formData = new FormData();
    formData.append('fileData', file);

    fetch('convertFile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        // Aquí puedes manejar la respuesta del servidor, como mostrar un enlace de descarga o mensajes
    })
    .catch(error => console.error('Error:', error));
}

document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById('fileData');

    fileInput.addEventListener('change', () => {
        selectedFiles.push(...Array.from(fileInput.files));
        updateFileList();
    });
});
