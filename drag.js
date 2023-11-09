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

        loadedFile = file;
        dropArea.innerHTML = `
            <div class="file-info">
                <span class="file-icon">📄</span>
                <span class="file-name">${file.name}</span>
                <button onclick="resetInput()" class="change-file-btn">Cambiar archivo</button>
            </div>`;
    } else {
        resetInput();
    }
}

function resetInput() {
    dropArea.innerHTML = 'Arrastra y suelta un archivo aquí o haz clic para seleccionar un archivo.';
    fileInput.value = ''; // Resetear el valor del input
    loadedFile = null;
}

dropArea.addEventListener('click', () => {
    if (!loadedFile) {
        fileInput.click();
    }
});

convertBtn.addEventListener('click', () => {
    if (!loadedFile) {
        resultDiv.innerHTML = 'Primero carga un archivo.';
        return;
    }

    convertFile(loadedFile, fileType, formatSelectorTo.value);
});

function convertFile(file, type, toFormat) {
    const formData = new FormData();
    formData.append('fileData', file);
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
        return response.blob(); // Manejar la respuesta como un blob
    })
    .then(blob => {
        // Crear un enlace para descargar el archivo
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'converted-file'; // Puedes ajustar el nombre del archivo de descarga aquí
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = 'Hubo un error al procesar la respuesta del servidor.';
    });
}