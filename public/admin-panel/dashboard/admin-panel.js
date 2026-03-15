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


const form = document.getElementById('timeline-form');
const list = document.getElementById('timeline-list');

async function loadEvents() {
    const res = await fetch('admin-panel.php');
    const events = await res.json();
    list.innerHTML = '';

    events.forEach(event => {
        const li = document.createElement('li');
        li.innerHTML = `
            <strong>${event.date} - ${event.title}</strong>
            <p>${event.description}</p>
            ${event.src ? `<img src="${event.src}" alt="${event.alt}" width="100">` : ''}
            <button data-id="${event.id}" class="delete-btn">Delete</button>
        `;
        list.appendChild(li);
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            await fetch('admin-panel.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'delete', id })
            });
            loadEvents();
        });
    });
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.append('action', 'add');

    await fetch('admin-panel.php', {
        method: 'POST',
        body: formData
    });

    form.reset();
    loadEvents();
});

loadEvents();
