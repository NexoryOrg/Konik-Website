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


const list = document.getElementById("timeline-list");
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
    const res = await fetch("/datenbank/json/history.json");
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

async function uploadTimelineImage(file) {
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
            const src = await uploadTimelineImage(imageFile);
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

const confirmDeleteModal = document.getElementById('confirm-delete-modal');
const cancelDeleteButton = document.getElementById('cancel-delete');
const confirmDeleteButton = document.getElementById('confirm-delete');
let deleteTargetId = null;

list.addEventListener('click', e => {
    if (e.target.classList.contains('delete')) {
        e.preventDefault();

        const li = e.target.closest('li');
        deleteTargetId = li?.dataset?.id || null;

        if (!deleteTargetId) {
            return;
        }

        if (confirmDeleteModal) {
            confirmDeleteModal.classList.remove('hidden');
        }
    }
});

if (cancelDeleteButton && confirmDeleteModal) {
    cancelDeleteButton.addEventListener('click', () => {
        deleteTargetId = null;
        confirmDeleteModal.classList.add('hidden');
    });
}

if (confirmDeleteButton && confirmDeleteModal) {
    confirmDeleteButton.addEventListener('click', async () => {
        if (!deleteTargetId) {
            return;
        }

        events = events.filter(ev => ev.id != deleteTargetId);
        deleteTargetId = null;

        render();
        await save();

        if (confirmDeleteModal) {
            confirmDeleteModal.classList.add('hidden');
        }

        const infoMsg = document.getElementById('info-msg');
        if (infoMsg) {
            document.getElementById('succes-h1').textContent = 'Deleted!';
            document.getElementById('succes-text').textContent = 'The event has been deleted.';
            infoMsg.hidden = false;
            setTimeout(() => { infoMsg.hidden = true; }, 3000);
        }
    });
}

if (confirmDeleteModal) {
    confirmDeleteModal.addEventListener('click', (e) => {
        if (e.target === confirmDeleteModal) {
            deleteTargetId = null;
            confirmDeleteModal.classList.add('hidden');
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
            const uploadedSrc = await uploadTimelineImage(file);
            const id = li.dataset.id;
            const event = events.find(item => item.id == id);
            if (event) {
                event.src = uploadedSrc;
                event.alt = event.title || "Timeline image";
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

        document.querySelectorAll("#timeline-list li").forEach(li => {

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

loadEvents();
