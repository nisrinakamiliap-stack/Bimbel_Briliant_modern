{{-- Tombol pesan mengambang + panel chat inline --}}
@php $unreadMessages = $navCounts['messages'] ?? 0; @endphp

{{-- Floating button --}}
<button type="button" id="st-chat-toggle"
        class="st-chat-fab"
        aria-label="Buka pesan{{ $unreadMessages > 0 ? ", {$unreadMessages} belum dibaca" : '' }}"
        aria-expanded="false"
        aria-controls="st-chat-panel">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    @if ($unreadMessages > 0)
        <span class="st-chat-badge" aria-hidden="true">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
    @endif
</button>

{{-- Panel chat --}}
<div id="st-chat-panel" class="st-chat-panel" hidden aria-label="Panel Pesan" role="dialog" aria-modal="true">
    {{-- Header --}}
    <div class="st-chat-header" id="st-chat-header-conversations">
        <span class="st-chat-title">Pesan</span>
        <div style="display:flex;align-items:center;gap:.5rem;">
            <a href="{{ route('messages.index') }}" class="st-chat-open-full" title="Buka halaman penuh">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <button type="button" id="st-chat-close" class="st-chat-close-btn" aria-label="Tutup panel pesan">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </div>
    <div class="st-chat-header st-chat-header--thread" id="st-chat-header-thread" hidden>
        <button type="button" id="st-chat-back" class="st-chat-back-btn" aria-label="Kembali ke daftar percakapan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <span class="st-chat-title" id="st-chat-other-name">Percakapan</span>
        <button type="button" id="st-chat-close-2" class="st-chat-close-btn" aria-label="Tutup panel pesan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    {{-- Daftar percakapan --}}
    <div id="st-chat-conversations" class="st-chat-body">
        <p class="st-chat-loading" id="st-chat-conv-loading">Memuat percakapan…</p>
        <ul id="st-chat-conv-list" class="st-chat-conv-list" hidden></ul>
        <p id="st-chat-conv-empty" class="st-chat-empty" hidden>Belum ada percakapan.
            <a href="{{ route('messages.create') }}">Mulai percakapan baru</a>.
        </p>
    </div>

    {{-- Thread pesan --}}
    <div id="st-chat-thread" class="st-chat-body st-chat-body--thread" hidden>
        <div id="st-chat-messages" class="st-chat-messages"></div>
    </div>

    {{-- Input reply --}}
    <form id="st-chat-reply-form" class="st-chat-reply" hidden>
        @csrf
        <input id="st-chat-reply-input" type="text" class="st-chat-reply-input"
               placeholder="Tulis pesan…" maxlength="2000" autocomplete="off"
               aria-label="Tulis pesan">
        <button type="submit" class="st-chat-send-btn" aria-label="Kirim">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </form>
</div>

<script>
(function () {
    const toggle    = document.getElementById('st-chat-toggle');
    const panel     = document.getElementById('st-chat-panel');
    const close1    = document.getElementById('st-chat-close');
    const close2    = document.getElementById('st-chat-close-2');
    const back      = document.getElementById('st-chat-back');
    const headerConv   = document.getElementById('st-chat-header-conversations');
    const headerThread = document.getElementById('st-chat-header-thread');
    const convLoading  = document.getElementById('st-chat-conv-loading');
    const convList     = document.getElementById('st-chat-conv-list');
    const convEmpty    = document.getElementById('st-chat-conv-empty');
    const convPane     = document.getElementById('st-chat-conversations');
    const threadPane   = document.getElementById('st-chat-thread');
    const messagesEl   = document.getElementById('st-chat-messages');
    const replyForm    = document.getElementById('st-chat-reply-form');
    const replyInput   = document.getElementById('st-chat-reply-input');
    const otherName    = document.getElementById('st-chat-other-name');

    let activeConvId   = null;
    let canReply       = false;
    let loaded         = false;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function openPanel() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        panel.classList.add('is-open');
        if (!loaded) { loadConversations(); loaded = true; }
        close1.focus();
    }

    function closePanel() {
        panel.hidden = true;
        panel.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    }

    function showConversations() {
        convPane.hidden = false;
        threadPane.hidden = true;
        replyForm.hidden = true;
        headerConv.hidden = false;
        headerThread.hidden = true;
    }

    function showThread(convId, name, canR) {
        activeConvId = convId;
        canReply = canR;
        otherName.textContent = name;
        convPane.hidden = true;
        threadPane.hidden = false;
        replyForm.hidden = !canR;
        headerConv.hidden = true;
        headerThread.hidden = false;
        loadMessages(convId);
        if (canR) replyInput.focus();
    }

    async function loadConversations() {
        convLoading.hidden = false;
        convList.hidden = true;
        convEmpty.hidden = true;
        try {
            const r = await fetch('/api/chat/conversations', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await r.json();
            convLoading.hidden = true;
            if (!data.length) { convEmpty.hidden = false; return; }
            convList.innerHTML = data.map(c => `
                <li>
                    <button type="button" class="st-chat-conv-item" data-id="${c.id}" data-name="${escHtml(c.other_name)}" data-reply="true">
                        <span class="st-chat-conv-avatar">${escHtml(c.other_initials)}</span>
                        <span class="st-chat-conv-info">
                            <span class="st-chat-conv-name">${escHtml(c.other_name)}${c.unread > 0 ? `<span class="st-chat-badge-sm">${c.unread}</span>` : ''}</span>
                            <span class="st-chat-conv-preview">${escHtml(c.last_body ?? '')}</span>
                        </span>
                        <span class="st-chat-conv-time">${escHtml(c.last_at ?? '')}</span>
                    </button>
                </li>`).join('');
            convList.hidden = false;
            convList.querySelectorAll('.st-chat-conv-item').forEach(btn => {
                btn.addEventListener('click', () => showThread(btn.dataset.id, btn.dataset.name, btn.dataset.reply === 'true'));
            });
        } catch {
            convLoading.textContent = 'Gagal memuat percakapan.';
        }
    }

    async function loadMessages(convId) {
        messagesEl.innerHTML = '<p class="st-chat-loading">Memuat pesan…</p>';
        try {
            const r = await fetch(`/api/chat/conversations/${convId}/messages`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await r.json();
            canReply = data.can_reply;
            replyForm.hidden = !canReply;
            renderMessages(data.messages);
        } catch {
            messagesEl.innerHTML = '<p class="st-chat-loading">Gagal memuat pesan.</p>';
        }
    }

    function renderMessages(messages) {
        if (!messages.length) {
            messagesEl.innerHTML = '<p class="st-chat-empty">Belum ada pesan dalam percakapan ini.</p>';
            return;
        }
        messagesEl.innerHTML = messages.map(m => `
            <div class="st-chat-msg ${m.mine ? 'is-mine' : 'is-theirs'}">
                <div class="st-chat-bubble">${escHtml(m.body)}</div>
                <span class="st-chat-time">${escHtml(m.at)}</span>
            </div>`).join('');
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    replyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = replyInput.value.trim();
        if (!body || !activeConvId) return;
        replyInput.value = '';
        try {
            const r = await fetch(`/api/chat/conversations/${activeConvId}/reply`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ body }),
            });
            const msg = await r.json();
            if (r.ok) {
                const div = document.createElement('div');
                div.className = 'st-chat-msg is-mine';
                div.innerHTML = `<div class="st-chat-bubble">${escHtml(msg.body)}</div><span class="st-chat-time">${escHtml(msg.at)}</span>`;
                messagesEl.appendChild(div);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }
        } catch { /* silent fail — user can retry */ }
    });

    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    toggle.addEventListener('click', () => panel.hidden ? openPanel() : closePanel());
    close1.addEventListener('click', closePanel);
    close2.addEventListener('click', closePanel);
    back.addEventListener('click', showConversations);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) closePanel();
    });
})();
</script>
