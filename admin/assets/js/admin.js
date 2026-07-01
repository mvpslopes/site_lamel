document.addEventListener('DOMContentLoaded', () => {
    initLoginForm();
    initAdminSplash();
    initAdminModal();
    initImagePreviews();

    const deleteForms = document.querySelectorAll('[data-confirm-delete]');
    deleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm-delete') || 'Deseja realmente excluir este item?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});

function initLoginForm() {
    const form = document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', (event) => {
        if (form.dataset.submitting === 'true') return;

        event.preventDefault();
        form.dataset.submitting = 'true';

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Entrando...';
        }

        form.submit();
    });
}

function initAdminSplash() {
    const splash = document.getElementById('admin-splash');
    const percentEl = document.getElementById('admin-splash-percent');
    const fillEl = document.getElementById('admin-splash-fill');
    const loginContent = document.getElementById('login-content');
    const panelContent = document.getElementById('admin-panel-content');

    if (!splash || !percentEl || !fillEl) {
        if (loginContent) loginContent.classList.add('visible');
        if (panelContent) panelContent.classList.add('visible');
        return;
    }

    let progress = 0;
    const duration = 2200;
    const interval = 40;
    const step = 100 / (duration / interval);

    const timer = setInterval(() => {
        progress = Math.min(100, progress + step + Math.random() * 2);
        const display = Math.floor(progress);

        percentEl.textContent = display + '%';
        fillEl.style.width = display + '%';

        if (display >= 100) {
            clearInterval(timer);
            percentEl.textContent = '100%';
            fillEl.style.width = '100%';

            setTimeout(() => {
                splash.classList.add('hidden');
                if (loginContent) loginContent.classList.add('visible');
                if (panelContent) panelContent.classList.add('visible');
            }, 350);
        }
    }, interval);
}

function initAdminModal() {
    const modal = document.getElementById('admin-form-modal');
    if (!modal) return;

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const modalBody = document.getElementById('admin-form-modal-body');
    const modalTitle = document.getElementById('admin-form-modal-title');
    const openButtons = document.querySelectorAll('[data-admin-modal-open]');

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const url = button.getAttribute('data-modal-url');
            const title = button.getAttribute('data-modal-title') || 'Cadastro';
            if (url) openAdminModal(url, title);
        });
    });

    modal.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', closeAdminModal);
    });

    const params = new URLSearchParams(window.location.search);
    const openParam = params.get('open');
    if (openParam === 'new') {
        openAdminModal(getAdminFormUrl(), modal.dataset.titleNew || 'Novo cadastro');
        cleanAdminModalQuery();
    } else if (openParam === 'edit') {
        const id = params.get('id');
        if (id) {
            openAdminModal(getAdminFormUrl(id), modal.dataset.titleEdit || 'Editar cadastro');
            cleanAdminModalQuery();
        }
    }

    async function openAdminModal(url, title) {
        modalTitle.textContent = title;
        modalBody.innerHTML = '<div class="admin-modal-loading">Carregando formulário...</div>';
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('admin-modal-open');

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Não foi possível carregar o formulário.');

            modalBody.innerHTML = await response.text();
            initSlugFields(modalBody);
            initImagePreviews(modalBody);
            bindModalCloseButtons(modalBody);
        } catch (error) {
            modalBody.innerHTML = '<div class="alert alert-error">Erro ao carregar o formulário. Tente novamente.</div>';
        }
    }

    function closeAdminModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('admin-modal-open');
        modalBody.innerHTML = '';
    }

    function bindModalCloseButtons(container) {
        container.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', closeAdminModal);
        });
    }

    function getAdminFormUrl(id) {
        const base = modal.dataset.formBase || 'form.php';
        if (id) return `${base}?id=${encodeURIComponent(id)}&partial=1`;
        return `${base}?partial=1`;
    }

    function cleanAdminModalQuery() {
        const url = new URL(window.location.href);
        url.searchParams.delete('open');
        url.searchParams.delete('id');
        window.history.replaceState(null, '', url.pathname + url.search);
    }

    window.openAdminModal = openAdminModal;
    window.closeAdminModal = closeAdminModal;
}

function initSlugFields(root = document) {
    const slugSource = root.querySelector('[data-slug-source]');
    const slugTarget = root.querySelector('[data-slug-target]');
    if (slugSource && slugTarget && !slugTarget.value) {
        slugSource.addEventListener('input', () => {
            slugTarget.value = slugify(slugSource.value);
        });
    }
}

function initImagePreviews(root = document) {
    root.querySelectorAll('input[data-preview-target]').forEach((input) => {
        if (input.dataset.previewBound === 'true') return;
        input.dataset.previewBound = 'true';

        input.addEventListener('change', () => {
            const box = root.querySelector(`#${input.dataset.previewTarget}`);
            if (!box || !input.files?.[0]) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                box.innerHTML = `<img src="${event.target.result}" alt="Preview do produto" class="image-preview-main">`;
                box.classList.add('has-image');
            };
            reader.readAsDataURL(input.files[0]);
        });
    });

    root.querySelectorAll('input[data-preview-list-target]').forEach((input) => {
        if (input.dataset.previewListBound === 'true') return;
        input.dataset.previewListBound = 'true';

        input.addEventListener('change', () => {
            const list = root.querySelector(`#${input.dataset.previewListTarget}`);
            if (!list) return;

            list.querySelectorAll('.image-preview-new').forEach((img) => img.remove());

            Array.from(input.files || []).forEach((file) => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.alt = 'Nova imagem';
                    img.className = 'image-preview-new';
                    list.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    });
}

function slugify(text) {
    return text
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
