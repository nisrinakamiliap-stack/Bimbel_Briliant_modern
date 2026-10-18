/**
 * Interaksi Portal Guru: dropdown navbar, menu mobile, dan status "sedang menyimpan" pada formulir.
 *
 * Dropdown dibuka dengan klik/tap, Enter, atau Spasi pada tombolnya, ditutup dengan Escape atau klik
 * di luar menu. Tanpa JavaScript, seluruh tujuan menu tetap tersedia melalui halaman masing-masing.
 */
(function () {
    'use strict';

    var DESKTOP_BREAKPOINT = 1024;
    var toggles = Array.prototype.slice.call(document.querySelectorAll('[data-dropdown-toggle]'));

    function menuFor(toggle) {
        return document.getElementById(toggle.getAttribute('aria-controls'));
    }

    function closeDropdown(toggle, returnFocus) {
        var menu = menuFor(toggle);
        if (!menu || menu.hidden) return;
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (returnFocus) toggle.focus();
    }

    function closeAll(except) {
        toggles.forEach(function (toggle) {
            if (toggle !== except) closeDropdown(toggle, false);
        });
    }

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var menu = menuFor(toggle);
            if (!menu) return;
            closeAll(toggle);
            var willOpen = menu.hidden;
            menu.hidden = !willOpen;
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen && event.detail === 0) {
                var first = menu.querySelector('a, button');
                if (first) first.focus();
            }
        });
    });

    document.addEventListener('click', function (event) {
        toggles.forEach(function (toggle) {
            var menu = menuFor(toggle);
            if (menu && !menu.hidden && !menu.contains(event.target)) closeDropdown(toggle, false);
        });
    });

    document.addEventListener('focusin', function (event) {
        toggles.forEach(function (toggle) {
            var menu = menuFor(toggle);
            var wrap = toggle.parentElement;
            if (menu && !menu.hidden && wrap && !wrap.contains(event.target)) closeDropdown(toggle, false);
        });
    });

    var menuToggle = document.getElementById('teacher-menu-toggle');
    var mobileMenu = document.getElementById('teacher-mobile-menu');

    function setMobileMenu(open, returnFocus) {
        if (!menuToggle || !mobileMenu) return;
        mobileMenu.hidden = !open;
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuToggle.querySelector('[data-menu-label]').textContent = open ? 'Tutup' : 'Menu';
        document.body.classList.toggle('menu-open', open);
        if (open) {
            var first = mobileMenu.querySelector('a, button');
            if (first) first.focus();
        } else if (returnFocus) {
            menuToggle.focus();
        }
    }

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function () {
            setMobileMenu(mobileMenu.hidden, true);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= DESKTOP_BREAKPOINT && !mobileMenu.hidden) setMobileMenu(false, false);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        toggles.forEach(function (toggle) {
            var menu = menuFor(toggle);
            if (menu && !menu.hidden) closeDropdown(toggle, true);
        });
        if (mobileMenu && !mobileMenu.hidden) setMobileMenu(false, true);
    });

    /**
     * Menonaktifkan tombol kirim selama formulir diproses agar tidak terkirim dua kali.
     * Formulir dengan konfirmasi (data-confirm) ditangani dialog konfirmasi admin.js.
     */
    document.querySelectorAll('form:not([data-confirm])').forEach(function (form) {
        if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return;
        form.addEventListener('submit', function () {
            form.setAttribute('aria-busy', 'true');
            form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                button.disabled = true;
                if (button.dataset.loadingLabel) button.textContent = button.dataset.loadingLabel;
            });
        });
    });
}());
