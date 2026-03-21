const toggle = document.getElementById('navbar-toggle');
const menu = document.getElementById('navbar-menu');

if (toggle && menu) {
    toggle.addEventListener('click', () => {
        menu.classList.toggle('active');
    });
}

window.addEventListener('resize', () => {
    if (window.innerWidth > 768 && menu) {
        menu.classList.remove('active');
    }
});

function updateActiveNav() {
    const links = document.querySelectorAll('.navbar-menu li a');
    const hash = window.location.hash || '#infos';
    links.forEach(link => {
        const href = link.getAttribute('href');
        const hrefHash = href ? (href.includes('#') ? href.substr(href.indexOf('#')) : '') : '';
        if (hrefHash === hash) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

window.addEventListener('hashchange', updateActiveNav);
window.addEventListener('load', updateActiveNav);

const filterButtons = document.querySelectorAll('.filter-btn');
const pendingView = document.querySelector('.pending-view');
const approvedView = document.querySelector('.approved-view');
const rejectedView = document.querySelector('.rejected-view');

function setFilter(filter) {
    filterButtons.forEach(b => b.classList.toggle('active', b.getAttribute('data-filter') === filter));
    pendingView?.classList.toggle('hidden', filter !== 'pending');
    approvedView?.classList.toggle('hidden', filter !== 'approved');
    rejectedView?.classList.toggle('hidden', filter !== 'rejected');
}

if (filterButtons.length) {
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.getAttribute('data-filter');
            setFilter(filter);
        });
    });
    setFilter('pending');
}

const settingsOpenBtn = document.getElementById('settings-open');
const passwordModal = document.getElementById('password-modal');
const closeModalBtn = document.getElementById('close-modal');

if (settingsOpenBtn && passwordModal) {
    settingsOpenBtn.addEventListener('click', (e) => {
        e.preventDefault();
        passwordModal.classList.remove('hidden');
        window.location.hash = '#settings';
        updateActiveNav();
    });
}

if (closeModalBtn && passwordModal) {
    closeModalBtn.addEventListener('click', () => {
        passwordModal.classList.add('hidden');
    });
}

passwordModal?.addEventListener('click', (e) => {
    if (e.target === passwordModal) {
        passwordModal.classList.add('hidden');
    }
});

const approvedEditModal = document.getElementById('approved-edit-modal');
const approvedEditForm = document.getElementById('approved-edit-form');
const approvedEditCancel = document.getElementById('approved-edit-cancel');
const confirmDeleteModal = document.getElementById('confirm-delete-modal');
const cancelDeleteButton = document.getElementById('cancel-delete');
const confirmDeleteButton = document.getElementById('confirm-delete');
const confirmDeleteTitle = document.getElementById('confirm-delete-title');
const confirmDeleteText = document.getElementById('confirm-delete-text');

let activeApprovedMenu = null;
let pendingDeleteAction = null;

function closeApprovedMenus() {
    document.querySelectorAll('.approved-image-menu').forEach((menuEl) => {
        menuEl.classList.remove('open');
    });
    activeApprovedMenu = null;
}

function closeApprovedEditModal() {
    approvedEditModal?.classList.add('hidden');
}

function openDeleteModal(title, text, onConfirm) {
    if (!confirmDeleteModal || !confirmDeleteButton || !cancelDeleteButton) {
        return;
    }

    if (confirmDeleteTitle) {
        confirmDeleteTitle.textContent = title;
    }
    if (confirmDeleteText) {
        confirmDeleteText.textContent = text;
    }

    pendingDeleteAction = onConfirm;
    confirmDeleteModal.classList.remove('hidden');
}

function closeDeleteModal() {
    pendingDeleteAction = null;
    confirmDeleteModal?.classList.add('hidden');
}

async function approvedImageActionRequest(payload) {
    const csrfToken = window.ADMIN_CSRF_TOKEN || '';
    const response = await fetch('/admin-panel/dashboard/manage-gallery-image.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(payload)
    });

    let data = null;
    try {
        data = await response.json();
    } catch (_error) {
        data = null;
    }

    if (!response.ok || !data || data.success !== true) {
        throw new Error(data?.message || 'Action failed');
    }

    return data;
}

function fillApprovedEditForm(menuEl) {
    const filenameInput = document.getElementById('approved-edit-filename');
    const titleInput = document.getElementById('approved-edit-title');
    const dateInput = document.getElementById('approved-edit-date');
    const descriptionInput = document.getElementById('approved-edit-description');

    if (!filenameInput || !titleInput || !dateInput || !descriptionInput || !menuEl) {
        return;
    }

    filenameInput.value = menuEl.dataset.filename || '';
    titleInput.value = menuEl.dataset.title || '';
    dateInput.value = menuEl.dataset.date || '';
    descriptionInput.value = menuEl.dataset.description || '';
}

window.adminToggleApprovedMenu = function adminToggleApprovedMenu(toggleButton, maybeEvent) {
    const eventObject = maybeEvent || window.event;
    eventObject?.preventDefault?.();
    eventObject?.stopPropagation?.();

    const menuEl = toggleButton?.closest('.approved-image-menu');
    if (!menuEl) {
        return false;
    }

    const shouldOpen = !menuEl.classList.contains('open');
    closeApprovedMenus();
    if (shouldOpen) {
        menuEl.classList.add('open');
        activeApprovedMenu = menuEl;
    }

    return false;
};

window.adminApprovedMenuEdit = function adminApprovedMenuEdit(actionButton, maybeEvent) {
    const eventObject = maybeEvent || window.event;
    eventObject?.preventDefault?.();
    eventObject?.stopPropagation?.();

    const menuEl = actionButton?.closest('.approved-image-menu');
    if (!menuEl) {
        return false;
    }

    fillApprovedEditForm(menuEl);
    closeApprovedMenus();
    approvedEditModal?.classList.remove('hidden');
    return false;
};

window.adminApprovedMenuDelete = async function adminApprovedMenuDelete(actionButton, maybeEvent) {
    const eventObject = maybeEvent || window.event;
    eventObject?.preventDefault?.();
    eventObject?.stopPropagation?.();

    const menuEl = actionButton?.closest('.approved-image-menu');
    const filename = menuEl?.dataset?.filename || '';
    closeApprovedMenus();

    if (!menuEl || filename === '') {
        return false;
    }

    openDeleteModal(
        'Delete Approved Image',
        'Are you sure you want to delete this approved image?',
        async () => {
            try {
                await approvedImageActionRequest({ action: 'delete', filename });
                const card = menuEl.closest('.pending-item');
                card?.remove();

                const approvedGrid = document.querySelector('.approved-view');
                if (approvedGrid && !approvedGrid.querySelector('.pending-item')) {
                    approvedGrid.innerHTML = '<div class="empty"><p>No approved requests.</p></div>';
                }

                showInfo('Deleted!', 'Approved image has been deleted.');
            } catch (error) {
                showError(error?.message || 'Delete failed.');
            }
        }
    );

    return false;
};

document.addEventListener('click', (event) => {
    if (activeApprovedMenu && !event.target.closest('.approved-image-menu')) {
        closeApprovedMenus();
    }
}, true);

approvedEditCancel?.addEventListener('click', () => {
    closeApprovedEditModal();
});

approvedEditModal?.addEventListener('click', (event) => {
    if (event.target === approvedEditModal) {
        closeApprovedEditModal();
    }
});

approvedEditForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const filename = document.getElementById('approved-edit-filename')?.value?.trim() || '';
    const title = document.getElementById('approved-edit-title')?.value?.trim() || '';
    const date = document.getElementById('approved-edit-date')?.value?.trim() || '';
    const descriptionValue = document.getElementById('approved-edit-description')?.value?.trim() || '';

    if (filename === '' || title === '' || date === '' || descriptionValue === '') {
        showError('Bitte alle Felder ausfuellen.');
        return;
    }

    try {
        await approvedImageActionRequest({
            action: 'edit',
            filename,
            title,
            date,
            description: descriptionValue
        });

        let menuEl = null;
        document.querySelectorAll('.approved-image-menu').forEach((entry) => {
            if (!menuEl && (entry.dataset.filename || '') === filename) {
                menuEl = entry;
            }
        });
        if (menuEl) {
            menuEl.dataset.title = title;
            menuEl.dataset.date = date;
            menuEl.dataset.description = descriptionValue;

            const card = menuEl.closest('.pending-item');
            const titleNode = card?.querySelector('.approved-title');
            const dateNode = card?.querySelector('.center-date');
            const paragraphs = card?.querySelectorAll('p');

            if (titleNode) {
                titleNode.textContent = title;
            }
            if (dateNode) {
                dateNode.innerHTML = `<strong>Date:</strong> ${escapeHtml(date)}`;
            }
            if (paragraphs && paragraphs.length > 1) {
                const shortDescription = descriptionValue.length > 50 ? `${descriptionValue.slice(0, 50)}...` : descriptionValue;
                paragraphs[1].textContent = shortDescription;
            }
        }

        closeApprovedEditModal();
        showInfo('Saved!', 'Approved image has been updated.');
    } catch (error) {
        showError(error?.message || 'Speichern fehlgeschlagen.');
    }
});


const list = document.getElementById("history-list");
const addBtn = document.getElementById("add-event");
const addEventModal = document.getElementById("add-event-modal");
const addEventForm = document.getElementById("add-event-form");
const eventImageInput = document.getElementById("event-image");

let events = [];

function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function safeImageSource(value) {
    const src = String(value ?? "").trim();
    if (src === "") {
        return "";
    }
    if (src.startsWith("/") || src.startsWith("../") || src.startsWith("data:image/")) {
        return src;
    }
    return "";
}

async function loadEvents() {
    const res = await fetch("/database/json/history.json");
    if (!res.ok) {
        console.error('Failed to load history', res.statusText);
        events = [];
    } else {
        const loaded = await res.json();
        events = Array.isArray(loaded) ? loaded : [];
    }

    render();
}

function render() {

    list.innerHTML = "";

    events.forEach(event => {

        const li = document.createElement("li");

        li.dataset.id = event.id;

        const title = escapeHtml(event.title);
        const date = escapeHtml(event.date);
        const description = escapeHtml(event.description);
        const imageSource = escapeHtml(safeImageSource(event.src));

        li.innerHTML = `
            <div class="event">

            <div>

            <input class="title" value="${title}" placeholder="Title">

            <input class="date" type="date" value="${date}">

            <textarea class="desc" placeholder="Description">${description}</textarea>

            <input class="image-input" type="file">

            <div class="event-actions">

            <button class="save">save</button>

            <button class="delete">Delete</button>
            </div>

            </div>

            <div>

            <img class="preview" src="${imageSource}">

            </div>

            </div>
            `;

        list.appendChild(li);

    });

}

function openAddEventModal() {
    if (!addEventModal) {
        return;
    }
    addEventModal.classList.remove("hidden");
}

function closeAddEventModal() {
    if (!addEventModal) {
        return;
    }
    addEventModal.classList.add("hidden");
}

function showInfo(title, text) {
    const infoMsg = document.getElementById("info-msg");
    if (!infoMsg) {
        return;
    }
    document.getElementById("succes-h1").textContent = title;
    document.getElementById("succes-text").textContent = text;
    infoMsg.hidden = false;
    setTimeout(() => {
        infoMsg.hidden = true;
    }, 3000);
}

function showError(text) {
    const errorMsg = document.getElementById("error-msg");
    if (!errorMsg) {
        return;
    }
    document.getElementById("error-text").textContent = text;
    errorMsg.hidden = false;
    setTimeout(() => {
        errorMsg.hidden = true;
    }, 3000);
}

async function uploadhistoryImage(file) {
    const csrfToken = window.ADMIN_CSRF_TOKEN || "";
    const payload = new FormData();
    payload.append("image", file);

    const res = await fetch("/admin-panel/dashboard/upload-history-image.php", {
        method: "POST",
        headers: {
            "X-CSRF-Token": csrfToken
        },
        body: payload
    });

    let data = null;
    try {
        data = await res.json();
    } catch (_err) {
        data = null;
    }

    if (!res.ok || !data?.success || typeof data.src !== "string" || data.src.trim() === "") {
        throw new Error(data?.message || "Image upload failed");
    }

    return data.src;
}

if (addBtn) {
    addBtn.onclick = (e) => {
        e.preventDefault();
        openAddEventModal();
    };
}

if (addEventModal) {
    addEventModal.addEventListener("click", (e) => {
        if (e.target === addEventModal) {
            closeAddEventModal();
        }
    });
}

if (addEventForm) {
    addEventForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const titleInput = document.getElementById("event-title");
        const dateInput = document.getElementById("event-date");
        const descriptionInput = document.getElementById("event-description");

        const title = titleInput?.value?.trim() || "New Event";
        const date = dateInput?.value?.trim() || "";
        const description = descriptionInput?.value?.trim() || "";
        const imageFile = eventImageInput?.files?.[0] || null;

        if (!imageFile) {
            showError("Bitte ein Bild auswaehlen.");
            return;
        }

        const submitButton = addEventForm.querySelector("button[type='submit']");
        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const src = await uploadhistoryImage(imageFile);
            const id = getNextEventId();

            events.push({
                id,
                title,
                date,
                description,
                src,
                alt: title
            });

            render();
            await save();
            addEventForm.reset();
            closeAddEventModal();
            showInfo("Added!", "Dein Event wurde hinzugefuegt.");
        } catch (error) {
            console.error("Add event failed:", error);
            showError(error?.message || "Event konnte nicht hinzugefuegt werden.");
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
}

function getNextEventId() {
    if (!Array.isArray(events) || events.length === 0) {
        return 1;
    }

    const lastEvent = events[events.length - 1];
    const lastId = Number(lastEvent?.id);

    if (Number.isFinite(lastId)) {
        return lastId + 1;
    }

    const maxId = events.reduce((max, event) => {
        const id = Number(event?.id);
        return Number.isFinite(id) ? Math.max(max, id) : max;
    }, 0);

    return maxId + 1;
}

list.addEventListener("click", async e => {

    if (e.target.classList.contains("save")) {

        try {
            const li = e.target.closest("li");
            const id = li.dataset.id;

            const event = events.find(ev => ev.id == id);

            event.title = li.querySelector(".title").value;
            event.date = li.querySelector(".date").value;
            event.description = li.querySelector(".desc").value;

            save();

            const infoMsg = document.getElementById("info-msg");
            if (infoMsg) {
                document.getElementById("succes-h1").textContent = "Saved!";
                document.getElementById("succes-text").textContent = "Your event has been saved.";
                infoMsg.hidden = false;
                await new Promise(resolve => setTimeout(resolve, 3000));
                infoMsg.hidden = true;
            }

        } catch (error) {
            console.error("Error:", error);

            const errorMsg = document.getElementById("error-msg");
            if (errorMsg) {
                document.getElementById("error-text").textContent = "Your event could not be saved. Please try again.";
                errorMsg.hidden = false;
                await new Promise(resolve => setTimeout(resolve, 3000));
                errorMsg.hidden = true;
            }
        }
    }
});

let deleteTargetId = null;

list.addEventListener('click', e => {
    if (e.target.classList.contains('delete')) {
        e.preventDefault();

        const li = e.target.closest('li');
        deleteTargetId = li?.dataset?.id || null;

        if (!deleteTargetId) {
            return;
        }

        openDeleteModal(
            'Delete Entry',
            'Are you sure you want to delete this event?',
            async () => {
                if (!deleteTargetId) {
                    return;
                }

                events = events.filter(ev => ev.id != deleteTargetId);
                deleteTargetId = null;

                render();
                await save();

                const infoMsg = document.getElementById('info-msg');
                if (infoMsg) {
                    document.getElementById('succes-h1').textContent = 'Deleted!';
                    document.getElementById('succes-text').textContent = 'The event has been deleted.';
                    infoMsg.hidden = false;
                    setTimeout(() => { infoMsg.hidden = true; }, 3000);
                }
            }
        );
    }
});

if (cancelDeleteButton && confirmDeleteModal) {
    cancelDeleteButton.addEventListener('click', () => {
        deleteTargetId = null;
        closeDeleteModal();
    });
}

if (confirmDeleteButton && confirmDeleteModal) {
    confirmDeleteButton.addEventListener('click', async () => {
        if (!pendingDeleteAction) {
            return;
        }

        const action = pendingDeleteAction;
        closeDeleteModal();
        await action();
    });
}

if (confirmDeleteModal) {
    confirmDeleteModal.addEventListener('click', (e) => {
        if (e.target === confirmDeleteModal) {
            deleteTargetId = null;
            closeDeleteModal();
        }
    });
}

list.addEventListener("change", async e => {

    if (e.target.classList.contains("image-input")) {

        const file = e.target.files[0];
        if (!file) {
            return;
        }

        const li = e.target.closest("li");
        const img = li.querySelector(".preview");

        const localPreview = URL.createObjectURL(file);
        img.src = localPreview;

        try {
            const uploadedSrc = await uploadhistoryImage(file);
            const id = li.dataset.id;
            const event = events.find(item => item.id == id);
            if (event) {
                event.src = uploadedSrc;
                event.alt = event.title || "history image";
            }
            URL.revokeObjectURL(localPreview);
            img.src = uploadedSrc;
        } catch (error) {
            console.error("Image upload failed:", error);
            URL.revokeObjectURL(localPreview);
            showError(error?.message || "Bild konnte nicht hochgeladen werden.");
        }

    }

});

new Sortable(list, {

    animation: 150,
    handle: ".title",

    onEnd() {

        const newOrder = [];

        document.querySelectorAll("#history-list li").forEach(li => {

            const id = li.dataset.id;

            const event = events.find(e => e.id == id);

            newOrder.push(event);

        });

        events = newOrder;

    }

});

async function save() {
    console.log("Saving:", events);

    const csrfToken = window.ADMIN_CSRF_TOKEN || '';

    const res = await fetch("/admin-panel/dashboard/save-history.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": csrfToken
        },
        body: JSON.stringify(events)
    });

    if (!res.ok) {
        console.error('Save-history failed', res.statusText);
    }
}

async function processAdminEmailQueue() {
    const queue = Array.isArray(window.ADMIN_EMAIL_QUEUE) ? window.ADMIN_EMAIL_QUEUE : [];
    if (!queue.length || !window.emailjs) {
        return;
    }

    try {
        const response = await fetch('/database/data/user.json', { cache: 'no-store' });
        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const cfg = data?.emailjs?.emailjs_data?.[0];
        if (!cfg?.service_id || !cfg?.public_key || !cfg?.upload_template_id) {
            return;
        }

        emailjs.init(cfg.public_key);
        for (const params of queue) {
            const toEmail = String(params?.to_email || '').trim();
            if (!toEmail) {
                continue;
            }

            await emailjs.send(cfg.service_id, cfg.upload_template_id, params);
        }
    } catch (_error) {
    }
}

processAdminEmailQueue();
loadEvents();
