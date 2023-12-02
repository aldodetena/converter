document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById('fileData');
    const fileInfosContainer = document.getElementById('fileInfosContainer');

    // Manejador para el evento 'change' del input de archivo
    fileInput.addEventListener('change', () => {
        // Limpiar la lista anterior de archivos
        fileInfosContainer.innerHTML = '';
        handleFiles(fileInput.files);
    });

    // Función para manejar archivos seleccionados
    function handleFiles(files) {
        // Verificar si hay archivos
        if (files.length === 0) {
            fileInfosContainer.innerHTML = '<p>No hay archivos seleccionados.</p>';
            return;
        }

        // Procesar cada archivo
        Array.from(files).forEach(file => {
            displayFile(file);
        });
    }

    // Función para mostrar información del archivo en la página
    function displayFile(file) {
        const fileElement = document.createElement('div');
        fileElement.classList.add('file-info');
        fileElement.innerHTML = `
            <span class="file-icon">📄</span>
            <span class="file-name">${file.name}</span>
            <button class="change-file-btn" onclick="removeFile('${file.name}')">Eliminar</button>
        `;
        fileInfosContainer.appendChild(fileElement);
    }
});

// Función para eliminar un archivo de la lista
function removeFile(fileName) {
    const fileInput = document.getElementById('fileData');
    const files = Array.from(fileInput.files);
    const filteredFiles = files.filter(file => file.name !== fileName);
    const dataTransfer = new DataTransfer();

    // Agregar los archivos filtrados de nuevo al DataTransfer
    filteredFiles.forEach(file => dataTransfer.items.add(file));

    // Establecer los archivos del input a los archivos del DataTransfer
    fileInput.files = dataTransfer.files;

    // Actualizar visualización de archivos
    document.querySelector(`.file-info:contains('${fileName}')`).remove();
}
