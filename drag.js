const dropArea = document.querySelector('.drop-area');
const fileInput = document.getElementById('file-input');
const resultDiv = document.getElementById('result');
const convertBtn = document.getElementById('convert-btn');
const formatSelectorTo = document.getElementById('format-to');

let loadedFile;
let fileType;

dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('active');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('active');
});

dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('active');
    const file = e.dataTransfer.files[0];
    handleFile(file);
});

fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    handleFile(file);
});

function handleFile(file) {
    if (file) {
        fileType = file.type.split('/')[0];
        if (['image', 'audio', 'video'].indexOf(fileType) === -1) {
            fileType = 'document';
        }

        loadedFile = file; // Guarda directamente el objeto File
        resultDiv.innerHTML = `Archivo ${fileType} cargado: ${file.name}`;
    } else {
        resultDiv.innerHTML = 'Por favor, carga un archivo válido.';
    }
}

convertBtn.addEventListener('click', () => {
    if (!loadedFile) {
        resultDiv.innerHTML = 'Primero carga un archivo.';
        return;
    }

    convertFile(loadedFile, fileType, formatSelectorTo.value);
});

function convertFile(file, type, toFormat) {
    const formData = new FormData();
    formData.append('fileData', file); // Agrega el objeto File directamente
    formData.append('fileType', type);
    formData.append('toFormat', toFormat);

    fetch('convertFile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();  // Solo intenta analizar como JSON si la respuesta fue exitosa
    })
    .then(result => {
        if (result.success) {
            resultDiv.innerHTML = `Archivo convertido exitosamente a ${toFormat.toUpperCase()}. <a href="${result.filePath}" download>Descargar aquí</a>`;
        } else {
            resultDiv.innerHTML = `Hubo un error al convertir el archivo: ${result.message}`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = 'Hubo un error al procesar la respuesta del servidor.';
    });
}