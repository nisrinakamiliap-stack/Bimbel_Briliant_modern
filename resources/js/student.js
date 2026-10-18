/* Portal Murid — interaktivitas navbar, dropdown, mobile drawer */
(function () {
    'use strict';

    /* ── Utility ── */
    function $(sel, ctx) { return (ctx || document).querySelector(sel); }
    function $$(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

    /* ── Mobile drawer ── */
    var drawer  = $('#st-drawer');
    var hamBtn  = $('#st-hamburger');
    var closeBtn = $('#st-drawer-close');

    function openDrawer() {
        if (!drawer) return;
        drawer.classList.add('is-open');
        document.body.classList.add('menu-open');
        hamBtn && hamBtn.setAttribute('aria-expanded', 'true');
        var first = $('a, button', drawer);
        if (first) first.focus();
    }

    function closeDrawer() {
        if (!drawer) return;
        drawer.classList.remove('is-open');
        document.body.classList.remove('menu-open');
        hamBtn && hamBtn.setAttribute('aria-expanded', 'false');
    }

    if (hamBtn) hamBtn.addEventListener('click', function () {
        drawer && drawer.classList.contains('is-open') ? closeDrawer() : openDrawer();
    });

    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);

    $$('.st-mobile-nav-link, .st-mobile-sub-link').forEach(function (l) {
        l.addEventListener('click', closeDrawer);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });

    /* ── Generic dropdown ── */
    function initDropdown(toggleId, menuId) {
        var toggle = document.getElementById(toggleId);
        var menu   = document.getElementById(menuId);
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = !menu.hidden;
            $$('[data-st-dropdown]').forEach(function (m) { m.hidden = true; });
            $$('[data-st-toggle]').forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
            if (!open) {
                menu.hidden = false;
                toggle.setAttribute('aria-expanded', 'true');
                var firstItem = $('a, button', menu);
                if (firstItem) firstItem.focus();
            }
        });

        toggle.setAttribute('data-st-toggle', '');
        menu.setAttribute('data-st-dropdown', '');
    }

    initDropdown('st-learning-toggle', 'st-learning-menu');
    initDropdown('st-profile-toggle',   'st-profile-menu');

    document.addEventListener('click', function () {
        $$('[data-st-dropdown]').forEach(function (m) { m.hidden = true; });
        $$('[data-st-toggle]').forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            $$('[data-st-dropdown]').forEach(function (m) { m.hidden = true; });
            $$('[data-st-toggle]').forEach(function (t) {
                t.setAttribute('aria-expanded', 'false');
                t.focus();
            });
        }
    });

    /* ── Flash auto-dismiss ── */
    $$('.st-flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    /* ── Confirm dialog for delete/logout forms ── */
    $$('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm || 'Apakah Anda yakin?')) {
                e.preventDefault();
            }
        });
    });
}());
