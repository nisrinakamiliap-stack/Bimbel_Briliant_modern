@props(['program'])

@php
$isPopular = !empty($program['populer']);
$slug = $program['slug'] ?? '';
$icon = $program['ikon'] ?? 'book';
@endphp

<article class="program-card" id="card-program-{{ $slug }}"
    style="{{ $isPopular ? 'border: 2px solid #86efac; background-color: #fafffe;' : 'border: 1px solid #e2e8f0; background-color: #ffffff;' }} position: relative; display: flex; flex-direction: column; justify-content: space-between; border-radius: 1rem; padding: 2rem; transition: box-shadow 0.25s ease, transform 0.25s ease, border-color 0.25s ease; height: 100%;">

    @if ($isPopular)
    <div style="position: absolute; top: 1.25rem; right: 1.25rem;" aria-hidden="true">
        <span class="badge-pill badge-green" style="font-size: 0.725rem; font-weight: 700; border: 1px solid rgba(22, 132, 91, 0.2);">Populer</span>
    </div>
    @endif

    <div>
        {{-- Ikon garis di dalam kotak hijau muda --}}
        <div class="program-card-icon" style="width: 3.5rem; height: 3.5rem; background-color: #EAF6EF; border-radius: 0.875rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; flex-shrink: 0;" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                @if ($icon === 'book')
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                @elseif ($icon === 'book-open')
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                @elseif ($icon === 'graduation-cap')
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                @elseif ($icon === 'check-circle')
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                @else
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                @endif
            </svg>
        </div>

        {{-- Judul dan Badge Kelas --}}
        <div style="margin-bottom: 0.875rem;">
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #142B4A; margin: 0; line-height: 1.3;">
                    {{ $program['judul'] }}
                </h3>
            </div>
            @if (!empty($program['kelas']))
                <span class="badge-pill {{ $program['badge_class'] ?? 'badge-green' }}" style="font-size: 0.75rem;">
                    {{ $program['kelas'] }}
                </span>
            @endif
        </div>

        {{-- Deskripsi singkat --}}
        <p style="font-size: 0.875rem; color: #4a5568; line-height: 1.65; margin-bottom: 1.25rem;">
            {{ $program['deskripsi'] }}
        </p>

        {{-- Daftar poin dengan centang hijau --}}
        @if (!empty($program['poin']))
            <ul style="list-style: none; padding: 0; margin: 0 0 1.75rem 0; display: flex; flex-direction: column; gap: 0.5rem;" aria-label="Keunggulan program">
                @foreach ($program['poin'] as $poin)
                    <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.8125rem; color: #4a5568; line-height: 1.5;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>{{ $poin }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Tombol Lihat Program hijau solid, full-width --}}
    <div style="margin-top: auto; padding-top: 0.5rem;">
        <a href="{{ route('program.show', $slug) }}"
           class="btn-primary btn-sm"
           id="btn-lihat-program-{{ $slug }}"
           style="width: 100%; justify-content: center; text-align: center; border-radius: 0.5rem;">
            Lihat Program
        </a>
    </div>
</article>
