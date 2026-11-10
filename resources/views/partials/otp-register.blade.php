{{-- Pakai: @include('site.partials.otp-register', ['school' => $school, 'emailId' => 'email']) --}}
<div id="otp-register" style="margin:-.25rem 0 1rem">
    <div id="otp-alert" class="auth-alert" style="display:none" role="alert"></div>

    <button class="btn auth-submit" type="button" id="otp-send">Kirim kode ke email</button>

    <div id="otp-verify-row" style="display:none;margin-top:1rem">
        <div class="auth-field">
            <label for="otp-code">Kode 6 digit dari email</label>
            <div class="auth-input">
                <input id="otp-code" type="text" inputmode="numeric" maxlength="6"
                       placeholder="123456" autocomplete="one-time-code">
            </div>
        </div>
        <button class="btn auth-submit" type="button" id="otp-check">Verifikasi email</button>
    </div>
</div>

<script>
(function () {
    var emailEl = document.getElementById(@json($emailId));
    var codeEl = document.getElementById('otp-code');
    var sendBtn = document.getElementById('otp-send');
    var checkBtn = document.getElementById('otp-check');
    var row = document.getElementById('otp-verify-row');
    var alertBox = document.getElementById('otp-alert');

    var urls = {
        send: @json(route('ppdb.otp.register.send', $school)),
        verify: @json(route('ppdb.otp.register.verify', $school))
    };
    var csrf = @json(csrf_token());

    function say(text, ok) {
        alertBox.textContent = text;
        alertBox.style.display = text ? 'block' : 'none';
        alertBox.style.color = ok ? '#166534' : '';
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(body)
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                var message = data.message;
                if (data.errors) { message = Object.values(data.errors)[0][0]; }
                return { ok: res.ok, data: data, message: message };
            });
        }).catch(function () {
            return { ok: false, data: {}, message: 'Koneksi bermasalah. Coba lagi.' };
        });
    }

    function cooldown(btn, seconds) {
        var label = btn.getAttribute('data-label') || btn.textContent;
        btn.setAttribute('data-label', label);
        btn.disabled = true;
        var left = seconds;
        btn.textContent = 'Kirim ulang dalam ' + left + ' detik';
        var timer = setInterval(function () {
            left--;
            if (left <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.textContent = label;
            } else {
                btn.textContent = 'Kirim ulang dalam ' + left + ' detik';
            }
        }, 1000);
    }

    sendBtn.addEventListener('click', function () {
        if (!emailEl.value.trim()) {
            say('Isi email terlebih dahulu.', false);
            return;
        }
        sendBtn.disabled = true;
        post(urls.send, { email: emailEl.value }).then(function (r) {
            say(r.message || 'Terjadi kesalahan.', r.ok);
            if (r.ok) {
                row.style.display = 'block';
                codeEl.focus();
                cooldown(sendBtn, 60);
            } else {
                sendBtn.disabled = false;
            }
        });
    });

    checkBtn.addEventListener('click', function () {
        if (!/^\d{6}$/.test(codeEl.value.trim())) {
            say('Kode harus 6 digit angka.', false);
            return;
        }
        checkBtn.disabled = true;
        post(urls.verify, { email: emailEl.value, code: codeEl.value.trim() }).then(function (r) {
            say(r.message || 'Terjadi kesalahan.', r.ok);
            if (r.ok) {
                emailEl.readOnly = true;
                row.style.display = 'none';
                sendBtn.style.display = 'none';
            } else {
                checkBtn.disabled = false;
            }
        });
    });

    // Email diubah setelah verifikasi = harus verifikasi ulang.
    emailEl.addEventListener('input', function () {
        emailEl.readOnly = false;
        sendBtn.style.display = '';
        sendBtn.disabled = false;
        checkBtn.disabled = false;
        say('', true);
    });
})();
</script>