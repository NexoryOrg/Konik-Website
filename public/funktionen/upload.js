document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    const uploadMessage = document.getElementById('uploadMessage');

    if (!uploadForm) return;

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(uploadForm);
        
        try {
            const response = await fetch('upload-handler.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                uploadMessage.textContent = data.message;
                uploadMessage.className = 'success';
                uploadMessage.style.display = 'block';
                uploadForm.reset();

                setTimeout(() => {
                    uploadMessage.style.display = 'none';
                }, 5000);
            } else {
                uploadMessage.textContent = 'Fehler: ' + data.message;
                uploadMessage.className = 'error';
                uploadMessage.style.display = 'block';
            }
        } catch (error) {
            uploadMessage.textContent = 'Fehler: ' + error.message;
            uploadMessage.className = 'error';
            uploadMessage.style.display = 'block';
            console.error('Upload error:', error);
        }
    });
});
