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

let events = [];

async function loadEvents() {
    const res = await fetch("/datenbank/json/history.json");
    if (!res.ok) {
        console.error('Failed to load history', res.statusText);
        events = [];
    } else {
        events = await res.json();
    }

    render();
}

function render() {

    list.innerHTML = "";

    events.forEach(event => {

        const li = document.createElement("li");

        li.dataset.id = event.id;

        li.innerHTML = `
            <div class="event">

            <div>

            <input class="title" value="${event.title}" placeholder="Title">

            <input class="date" type="date" value="${event.date}">

            <textarea class="desc" placeholder="Description">${event.description}</textarea>

            <input class="image-input" type="file">

            <div class="event-actions">

            <button class="save">save</button>

            <button class="delete">Delete</button>
            </div>

            </div>

            <div>

            <img class="preview" src="${event.src || ""}">

            </div>

            </div>
            `;

        list.appendChild(li);

    });

}

addBtn.onclick = () => {
    const id = Date.now();
    events.push({
        id,
        title: "New Event",
        date: "2024-01-01",
        description: "",
        src: "",
        alt: ""
    });
    render();
};

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

list.addEventListener("change", e => {

    if (e.target.classList.contains("image-input")) {

        const file = e.target.files[0];

        const li = e.target.closest("li");
        const img = li.querySelector(".preview");

        const reader = new FileReader();

        reader.onload = function(ev) {

            img.src = ev.target.result;

            const id = li.dataset.id;
            const event = events.find(e => e.id == id);

            event.src = ev.target.result;

        };

        reader.readAsDataURL(file);

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

    const res = await fetch("/admin-panel/dashboard/save-history.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(events)
    });

    if (!res.ok) {
        console.error('Save-history failed', res.statusText);
    }
}

loadEvents();
