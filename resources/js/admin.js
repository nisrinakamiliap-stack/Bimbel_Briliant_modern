/**
 * Interaksi panel admin: sidebar mobile (off-canvas) dan dropdown profil.
 */
(function () {
    'use strict';

    var DESKTOP_BREAKPOINT = 1024;

    var body = document.body;
    var sidebar = document.getElementById('admin-sidebar');
    var overlay = document.getElementById('admin-overlay');
    var menuToggle = document.getElementById('admin-menu-toggle');
    var sidebarClose = document.getElementById('admin-sidebar-close');
    var profileToggle = document.getElementById('admin-profile-toggle');
    var profileDropdown = document.getElementById('admin-profile-dropdown');

    function isMobile() {
        return window.innerWidth < DESKTOP_BREAKPOINT;
    }

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('is-open');
        if (overlay) overlay.classList.add('is-visible');
        body.classList.add('sidebar-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'true');
        var firstLink = sidebar.querySelector('a.admin-nav-item');
        if (firstLink) firstLink.focus();
    }

    function closeSidebar(returnFocus) {
        if (!sidebar || !sidebar.classList.contains('is-open')) return;
        sidebar.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
        body.classList.remove('sidebar-open');
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
            if (returnFocus) menuToggle.focus();
        }
    }

    function openDropdown() {
        if (!profileDropdown || !profileToggle) return;
        profileDropdown.hidden = false;
        profileToggle.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown(returnFocus) {
        if (!profileDropdown || !profileToggle || profileDropdown.hidden) return;
        profileDropdown.hidden = true;
        profileToggle.setAttribute('aria-expanded', 'false');
        if (returnFocus) profileToggle.focus();
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            if (sidebar && sidebar.classList.contains('is-open')) {
                closeSidebar(true);
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', function () {
            closeSidebar(true);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            closeSidebar(true);
        });
    }

    if (profileToggle) {
        profileToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            if (profileDropdown && profileDropdown.hidden) {
                openDropdown();
            } else {
                closeDropdown(false);
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (profileDropdown && !profileDropdown.hidden && !profileDropdown.contains(event.target)) {
            closeDropdown(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        closeDropdown(true);
        if (isMobile()) closeSidebar(true);
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) closeSidebar(false);
    });
}());

/**
 * Dialog konfirmasi untuk form berbahaya (mis. hapus data).
 *
 * Form dengan atribut `data-confirm` akan menampilkan dialog terlebih dahulu.
 * Judul dan teks tombol dapat diganti lewat `data-confirm-title` dan `data-confirm-button`.
 * Jika browser tidak mendukung <dialog>, digunakan window.confirm sebagai cadangan.
 */
(function () {
    'use strict';

    var dialog = document.getElementById('admin-confirm-dialog');
    var title = document.getElementById('admin-confirm-title');
    var message = document.getElementById('admin-confirm-message');
    var confirmButton = document.getElementById('admin-confirm-submit');
    var cancelButton = document.getElementById('admin-confirm-cancel');
    var defaultTitle = title ? title.textContent : '';
    var defaultButtonText = confirmButton ? confirmButton.textContent : '';
    var pendingForm = null;
    var lastTrigger = null;

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var text = form.getAttribute('data-confirm');

            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm(text)) form.submit();
                return;
            }

            pendingForm = form;
            lastTrigger = document.activeElement;
            if (message) message.textContent = text;
            if (title) title.textContent = form.getAttribute('data-confirm-title') || defaultTitle;
            if (confirmButton) confirmButton.textContent = form.getAttribute('data-confirm-button') || defaultButtonText;
            dialog.showModal();
            if (cancelButton) cancelButton.focus();
        });
    });

    if (confirmButton) {
        confirmButton.addEventListener('click', function () {
            if (!pendingForm) return;
            confirmButton.disabled = true;
            pendingForm.submit();
        });
    }

    if (cancelButton && dialog) {
        cancelButton.addEventListener('click', function () {
            dialog.close();
        });
    }

    if (dialog) {
        dialog.addEventListener('close', function () {
            pendingForm = null;
            if (lastTrigger && typeof lastTrigger.focus === 'function') lastTrigger.focus();
        });

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) dialog.close();
        });
    }
}());
