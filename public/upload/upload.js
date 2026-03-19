document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    const uploadMessage = document.getElementById('uploadMessage');

    if (!uploadForm) return;

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(uploadForm);
        if (!formData.get('csrf_token')) {
            uploadMessage.textContent = 'Error: Security token is missing. Please reload the page.';
            uploadMessage.className = 'error';
            uploadMessage.style.display = 'block';
            return;
        }
        
        try {
            const scriptElements = Array.from(document.getElementsByTagName('script'));
            const uploadScript = scriptElements.find(el => el.src && el.src.endsWith('/upload/upload.js'));
            const uploadHandlerUrl = uploadScript ? new URL('upload-handler.php', uploadScript.src).toString() : '/upload/upload-handler.php';

            const response = await fetch(uploadHandlerUrl, {
                method: 'POST',
                credentials: 'same-origin',
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
                uploadMessage.textContent = 'Error: ' + data.message;
                uploadMessage.className = 'error';
                uploadMessage.style.display = 'block';
            }
        } catch (error) {
            uploadMessage.textContent = 'Error: ' + error.message;
            uploadMessage.className = 'error';
            uploadMessage.style.display = 'block';
            console.error('Upload error:', error);
        }
    });
});
