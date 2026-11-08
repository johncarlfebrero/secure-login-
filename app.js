// UI helpers only. All real validation happens on the server (register.php / login.php).
document.addEventListener('DOMContentLoaded', function () {

    // Show / hide password buttons
    document.querySelectorAll('.toggle-pass').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('showing', show);
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    // Password strength meter (register page)
    var pass = document.getElementById('password');
    var bar = document.querySelector('.strength span');
    var hint = document.getElementById('strength-hint');
    if (pass && bar && hint) {
        pass.addEventListener('input', function () {
            var v = pass.value, score = 0;
            if (v.length >= 8) score++;
            if (v.length >= 12) score++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;

            var levels = [
                { w: '10%',  c: '#dc2626', t: 'Too short – at least 8 characters' },
                { w: '25%',  c: '#dc2626', t: 'Weak' },
                { w: '45%',  c: '#f59e0b', t: 'Fair' },
                { w: '65%',  c: '#84cc16', t: 'Good' },
                { w: '85%',  c: '#16a34a', t: 'Strong' },
                { w: '100%', c: '#15803d', t: 'Very strong 💪' }
            ];
            var lvl = v.length === 0 ? null : (v.length < 8 ? levels[0] : levels[score]);
            bar.style.width = lvl ? lvl.w : '0';
            bar.style.background = lvl ? lvl.c : 'transparent';
            hint.textContent = lvl ? lvl.t : '';
            hint.className = 'hint' + (lvl ? (v.length < 8 ? ' bad' : (score >= 4 ? ' ok' : '')) : '');
            checkMatch();
        });
    }

    // "Passwords match" hint (register page)
    var confirm = document.getElementById('confirm_password');
    var matchHint = document.getElementById('match-hint');
    function checkMatch() {
        if (!confirm || !matchHint) return;
        if (confirm.value === '') { matchHint.textContent = ''; matchHint.className = 'hint'; return; }
        var ok = confirm.value === pass.value;
        matchHint.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';
        matchHint.className = 'hint ' + (ok ? 'ok' : 'bad');
    }
    if (confirm) confirm.addEventListener('input', checkMatch);

    // Loading state on submit
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.style.opacity = '.75'; btn.querySelector('.label').textContent = 'Please wait…'; }
        });
    });
});
