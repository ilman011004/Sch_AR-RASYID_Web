{{-- Dipakai bersama oleh form modul CRUD dan halaman Pengaturan Sekolah.
     Variabel: $fields (array definisi), $model (model atau null) --}}
@foreach ($fields as $f)
    @php
        $n = $f['name'];
        $current = $model ? $model->{$n} : ($f['default'] ?? null);
        $val = old($n, $current);
        if ($val instanceof \DateTimeInterface) {
            $val = $val->format($f['type'] === 'datetime' ? 'Y-m-d\TH:i' : 'Y-m-d');
        }
        $inputType = ['date' => 'date', 'datetime' => 'datetime-local', 'number' => 'number', 'color' => 'color', 'url' => 'url'][$f['type']] ?? 'text';
    @endphp

    <div class="field">
        <label for="f_{{ $n }}">{{ $f['label'] }}</label>

        @if ($f['type'] === 'textarea')
            <textarea id="f_{{ $n }}" name="{{ $n }}" rows="{{ $f['rows'] ?? 4 }}">{{ $val }}</textarea>

        @elseif ($f['type'] === 'select')
            <select id="f_{{ $n }}" name="{{ $n }}">
                @foreach ($f['options'] as $key => $label)
                    <option value="{{ $key }}" @selected((string) $val === (string) $key)>{{ $label }}</option>
                @endforeach
            </select>

        @elseif ($f['type'] === 'image')
            @if ($model && $model->{$n})
                <img class="thumb" src="{{ asset('storage/'.$model->{$n}) }}" alt="">
                <small>Unggah berkas baru hanya jika ingin mengganti.</small>
            @endif
            <input id="f_{{ $n }}" type="file" name="{{ $n }}" accept="image/jpeg,image/png,image/webp">
            <small>JPG, PNG, atau WebP, maksimal 2 MB.</small>

        @elseif ($f['type'] === 'checkbox')
            <input type="hidden" name="{{ $n }}" value="0">
            <label class="check"><input id="f_{{ $n }}" type="checkbox" name="{{ $n }}" value="1" @checked($val)> {{ $f['help'] ?? 'Aktif' }}</label>

        @else
            <input id="f_{{ $n }}" type="{{ $inputType }}" name="{{ $n }}" value="{{ $val }}"
                   @if ($f['type'] === 'number') min="0" @endif>
        @endif

        @if (! empty($f['help']) && $f['type'] !== 'checkbox')
            <small>{{ $f['help'] }}</small>
        @endif
    </div>
@endforeach