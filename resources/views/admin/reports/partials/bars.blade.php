{{--
    Tabel ringkas dengan batang proporsional (data dari database).

    @var \Illuminate\Support\Collection<string, int|float> $rows
    @var string $empty
    @var bool $isMoney
--}}
@php
$isMoney = $isMoney ?? false;
$maxValue = max(1, (float) collect($rows)->max());
@endphp
@if (collect($rows)->sum() == 0)
    <p class="admin-result-info">{{ $empty }}</p>
@else
    <table class="admin-table">
        <tbody>
            @foreach ($rows as $label => $value)
                <tr>
                    <td style="width: 40%;">{{ $label }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: .625rem;">
                            <span aria-hidden="true" style="display: inline-block; height: .5rem; border-radius: 999px; background: var(--admin-primary); width: {{ max(2, round((float) $value / $maxValue * 100)) }}%; max-width: 70%;"></span>
                            <strong>{{ $isMoney ? \App\Models\Payment::rupiah($value) : number_format((float) $value, 0, ',', '.') }}</strong>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
