@extends($layout)

@section('title', 'Notifikasi')
@section('page_title', 'Notifikasi dan Pesan')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Notifikasi</h2>
            <p>{{ $unreadCount }} notifikasi belum dibaca. Notifikasi dibuat otomatis dari peristiwa di aplikasi.</p>
        </div>
        @if ($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="admin-btn admin-btn-secondary" id="btn-read-all"><x-admin.icon name="check" :size="16" /> Tandai Semua Dibaca</button>
            </form>
        @endif
    </div>

    @include('notifications.partials.tabs')

    <section class="admin-panel" aria-labelledby="notifications-heading">
        <h3 id="notifications-heading" class="sr-only">Daftar notifikasi</h3>
        <form action="{{ route('notifications.index') }}" method="GET" class="admin-toolbar is-stacked">
            <div class="admin-toolbar-row">
                <label for="kategori" class="sr-only">Kategori</label>
                <select id="kategori" name="kategori" class="admin-select">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->value }}" @selected(($filters['kategori'] ?? null) === $categoryOption->value)>{{ $categoryOption->label() }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Status baca</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua</option>
                    <option value="belum" @selected(($filters['status'] ?? null) === 'belum')>Belum dibaca</option>
                    <option value="sudah" @selected(($filters['status'] ?? null) === 'sudah')>Sudah dibaca</option>
                </select>
                <label for="dari" class="sr-only">Dari tanggal</label>
                <input type="date" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;">
                <label for="sampai" class="sr-only">Sampai tanggal</label>
                <input type="date" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;">
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
            </div>
            @error('sampai') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>

        @if ($notifications->isEmpty())
            <x-admin.empty-state title="Tidak ada notifikasi" icon="inbox">Notifikasi akan muncul ketika ada pendaftaran, pembayaran, tugas, penilaian, atau pengumuman baru.</x-admin.empty-state>
        @else
            <ul class="admin-list" id="notification-list">
                @foreach ($notifications as $notification)
                    @php $category = \App\NotificationCategory::tryFrom($notification->data['category'] ?? ''); @endphp
                    <li class="admin-list-item {{ $notification->read_at === null ? 'is-unread' : '' }}">
                        <div class="admin-list-item-body">
                            <p class="admin-list-item-title">
                                @if ($category)<span class="admin-badge {{ $category->badgeClass() }}" style="margin-right: .375rem;">{{ $category->label() }}</span>@endif
                                {{ $notification->data['title'] ?? 'Notifikasi' }}
                            </p>
                            <p style="margin: 0; font-size: .875rem;">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="admin-list-item-meta">{{ $notification->created_at->translatedFormat('d M Y, H:i') }} · {{ $notification->read_at ? 'Dibaca '.$notification->read_at->translatedFormat('d M Y, H:i') : 'Belum dibaca' }}</p>
                        </div>
                        <div class="admin-actions" style="flex-wrap: wrap;">
                            @if (! empty($notification->data['url']))
                                <a href="{{ route('notifications.open', $notification->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Buka</a>
                            @endif
                            @if ($notification->read_at === null)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Tandai Dibaca</button>
                                </form>
                            @else
                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" data-confirm="Hapus notifikasi ini?" data-confirm-title="Hapus Notifikasi" data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-danger-outline admin-btn-sm">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            @if ($notifications->hasPages())
                {{ $notifications->links('admin.partials.pagination') }}
            @endif
        @endif
    </section>
@endsection
