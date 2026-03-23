document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    const uploadMessage = document.getElementById('uploadMessage');

    if (!uploadForm) return;

    const errorPrefix = uploadForm.dataset.errorPrefix || 'Error:';
    const tokenMissingText = uploadForm.dataset.tokenMissing || 'Error: Security token is missing. Please reload the page.';

    async function loadUploadEmailConfig() {
        try {
            const response = await fetch('/database/data/user.json', { cache: 'no-store' });
            if (!response.ok) return null;
            const data = await response.json();
            const cfg = data?.emailjs?.emailjs_data?.[0];
            if (!cfg?.service_id || !cfg?.public_key || !cfg?.upload_template_id) return null;
            return cfg;
        } catch (_) {
            return null;
        }
    }

    async function sendUploadConfirmation(email, title, date) {
        if (!email || !window.emailjs) return;
        try {
            const cfg = await loadUploadEmailConfig();
            if (!cfg) return;
            emailjs.init(cfg.public_key);
            await emailjs.send(cfg.service_id, cfg.upload_template_id, {
                to_email: email,
                title: title,
                date: date,
                icon: '📸',
                header_color: '#3b82f6',
                heading: 'Photo Submitted',
                body_text: 'We have received your photo submission. Our team will review it shortly and notify you once a decision has been made.'
            });
        } catch (_) { }
    }

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(uploadForm);
        if (!formData.get('csrf_token')) {
            uploadMessage.textContent = tokenMissingText;
            uploadMessage.className = 'error';
            uploadMessage.style.display = 'block';
            return;
        }

        const uploaderEmail = (formData.get('uploaderEmail') || '').trim();
        const eventTitle   = (formData.get('eventTitle')   || '').trim();
        const eventDate    = (formData.get('eventDate')    || '').trim();

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

                sendUploadConfirmation(uploaderEmail, eventTitle, eventDate);

                setTimeout(() => {
                    uploadMessage.style.display = 'none';
                }, 5000);
            } else {
                uploadMessage.textContent = errorPrefix + ' ' + data.message;
                uploadMessage.className = 'error';
                uploadMessage.style.display = 'block';
            }
        } catch (error) {
            uploadMessage.textContent = errorPrefix + ' ' + error.message;
            uploadMessage.className = 'error';
            uploadMessage.style.display = 'block';
            console.error('Upload error:', error);
        }
    });
});
