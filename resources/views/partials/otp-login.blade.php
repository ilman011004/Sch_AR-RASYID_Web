{{-- Pakai: @include('site.partials.otp-login', ['school' => $school]) --}}
<div id="login-otp" style="margin-top:1.5rem">
    <h3>Atau masuk dengan kode email</h3>

    <label for="lo-email">Email</label>
    <input type="email" id="lo-email" autocomplete="email">
    <button type="button" id="lo-send">Kirim kode</button>

    <div id="lo-verify-row" style="display:none;margin-top:.5rem">
        <input type="text" id="lo-code" inputmode="numeric" maxlength="6"
               placeholder="6 digit kode" autocomplete="one-time-code">
        <button type="button" id="lo-check">Masuk</button>
    </div>

    <p id="lo-msg" style="margin-top:.5rem"></p>
</div>

<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const urls = {
        send: @json(route('ppdb.otp.login.send', $school)),
        verify: @json(route('ppdb.otp.login.verify', $school)),
    };

    function say(text, ok) {
        $('lo-msg').textContent = text;
        $('lo-msg').style.color = ok ? 'green' : 'crimson';
    }

    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
    }

    $('lo-send').addEventListener('click', async () => {
        $('lo-send').disabled = true;
        const r = await post(urls.send, { email: $('lo-email').value });
        say(r.data.message || 'Terjadi kesalahan.', r.ok);

        if (r.ok) {
            $('lo-verify-row').style.display = 'block';
            setTimeout(() => ($('lo-send').disabled = false), 60000);
        } else {
            $('lo-send').disabled = false;
        }
    });

    $('lo-check').addEventListener('click', async () => {
        const r = await post(urls.verify, { email: $('lo-email').value, code: $('lo-code').value });
        say(r.data.message || 'Terjadi kesalahan.', r.ok);
        if (r.ok && r.data.redirect) window.location.href = r.data.redirect;
    });
})();
</script>