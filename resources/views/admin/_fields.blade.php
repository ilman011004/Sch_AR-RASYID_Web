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
        $hasFile = $model && $model->{$n};
        $showIf = $f['show_if'] ?? null;
        $showIfName = $showIf ? array_key_first($showIf) : null;
        $showIfValue = $showIf ? $showIf[$showIfName] : null;
    @endphp

    <div class="field"
         @if ($showIf) data-show-if-name="{{ $showIfName }}" data-show-if-value="{{ $showIfValue }}" @endif>
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
            <div class="upload" data-upload>
                <img class="thumb" data-preview @if ($hasFile) src="{{ asset('storage/'.$model->{$n}) }}" @else hidden @endif alt="">
                <div class="upload-row">
                    <input class="upload-input" id="f_{{ $n }}" type="file" name="{{ $n }}" accept="image/jpeg,image/png,image/webp">
                    <label class="upload-btn" for="f_{{ $n }}">
                        <span aria-hidden="true">⬆</span>
                        {{ $hasFile ? 'Ganti '.($f['label']) : ($f['button'] ?? 'Pilih Foto') }}
                    </label>
                    <span class="upload-name" data-name>{{ $hasFile ? 'Sudah ada. Pilih lagi hanya jika ingin mengganti.' : 'Belum ada file dipilih' }}</span>
                </div>
            </div>
            <small>Format JPG, PNG, atau WebP, maksimal 2 MB.</small>

        @elseif ($f['type'] === 'video')
            <div class="upload" data-upload>
                @if ($hasFile)
                    <video class="thumb" src="{{ asset('storage/'.$model->{$n}) }}" muted controls preload="metadata" style="max-height:140px"></video>
                @endif
                <div class="upload-row">
                    <input class="upload-input" id="f_{{ $n }}" type="file" name="{{ $n }}" accept="video/mp4,video/webm">
                    <label class="upload-btn" for="f_{{ $n }}">
                        <span aria-hidden="true">⬆</span>
                        {{ $hasFile ? 'Ganti Video' : ($f['button'] ?? 'Pilih Video') }}
                    </label>
                    <span class="upload-name" data-name>{{ $hasFile ? 'Sudah ada. Pilih lagi hanya jika ingin mengganti.' : 'Belum ada file dipilih' }}</span>
                </div>
            </div>
            <small>Format MP4 atau WebM, maksimal {{ round(($f['max_kb'] ?? 51200) / 1024) }} MB.</small>

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

@once
<script>
    (function () {
        // Tampilkan nama file dan pratinjau foto setelah dipilih
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input.matches || !input.matches('.upload-input')) return;
            var box = input.closest('[data-upload]');
            var file = input.files && input.files[0];
            var name = box.querySelector('[data-name]');
            var preview = box.querySelector('[data-preview]');

            if (name) name.textContent = file ? file.name : 'Belum ada file dipilih';

            if (preview) {
                if (file && file.type.indexOf('image/') === 0) {
                    preview.src = URL.createObjectURL(file);
                    preview.hidden = false;
                }
            }
        });

        // Tampilkan kolom hanya bila field lain bernilai tertentu (mis. Video hanya untuk jenis Video)
        function applyShowIf() {
            document.querySelectorAll('[data-show-if-name]').forEach(function (box) {
                var ctrl = document.querySelector('[name="' + box.dataset.showIfName + '"]');
                if (!ctrl) return;
                box.hidden = String(ctrl.value) !== box.dataset.showIfValue;
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target.name) applyShowIf();
        });
        document.addEventListener('DOMContentLoaded', applyShowIf);
        applyShowIf();
    })();
</script>
@endonce