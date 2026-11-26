import $ from 'jquery';

// jQuery dibuat global agar bisa dipakai di script halaman (@push('scripts'))
window.$ = window.jQuery = $;

/*
 * JavaScript dasar: buka/tutup sidebar admin pada layar kecil.
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            sidebar?.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
        });
    });
});

/*
 * jQuery: perilaku umum yang dipakai di banyak halaman.
 */
$(function () {
    // Buka/tutup menu navbar user pada layar kecil
    $('[data-navbar-toggle]').on('click', function () {
        $('#navbar-mobile').slideToggle(150);
    });

    // Tutup alert ketika tombol x diklik
    $('[data-alert-close]').on('click', function () {
        $(this).closest('[data-alert]').fadeOut(200);
    });

    // Alert sukses hilang otomatis setelah 4 detik
    setTimeout(function () {
        $('[data-alert="success"]').fadeOut(400);
    }, 4000);

    // Konfirmasi sebelum submit form hapus: <form data-confirm="Yakin hapus?">
    $('form[data-confirm]').on('submit', function (event) {
        if (!confirm($(this).data('confirm'))) {
            event.preventDefault();
        }
    });

    initIdleTimeout();
});

/*
 * jQuery: logout otomatis jika lama tidak aktif.
 * Logout sebenarnya dilakukan server (middleware LogoutIfIdle);
 * bagian ini hanya menampilkan peringatan dan menjaga sesi tetap hidup
 * selama pengguna masih aktif (gerak mouse, ketik, klik, scroll).
 */
function initIdleTimeout() {
    const $body = $('body');
    const timeoutMs = Number($body.data('idle-timeout')) * 1000;
    const pingUrl = $body.data('ping-url');

    if (!timeoutMs || !pingUrl) {
        return; // halaman tamu (login) tidak memakai timer
    }

    const warningMs = Math.min(60000, timeoutMs / 2); // peringatan 60 detik sebelum habis
    const pingEveryMs = Math.min(60000, timeoutMs / 3); // kabari server paling sering tiap 60 detik
    const storageKey = 'ecotrack:lastActivity';

    let lastActivity = Date.now();
    let lastPing = Date.now();

    // Waktu aktivitas disimpan di localStorage agar beberapa tab saling sinkron
    function readLastActivity() {
        try {
            return Math.max(lastActivity, Number(localStorage.getItem(storageKey)) || 0);
        } catch (e) {
            return lastActivity;
        }
    }

    function markActivity() {
        lastActivity = Date.now();
        try {
            localStorage.setItem(storageKey, lastActivity);
        } catch (e) {
            // localStorage tidak tersedia: cukup pakai variabel lokal
        }

        // Perbarui last_activity di server, tapi tidak di setiap gerakan mouse
        if (lastActivity - lastPing >= pingEveryMs) {
            lastPing = lastActivity;
            $.get(pingUrl);
        }
    }

    markActivity();
    $(document).on('mousemove keydown click scroll touchstart', markActivity);

    $('#idle-stay').on('click', function () {
        lastPing = 0; // paksa ping ke server sekarang
        markActivity();
        $('#idle-warning').fadeOut(200);
    });

    setInterval(function () {
        const remainingMs = timeoutMs - (Date.now() - readLastActivity());

        if (remainingMs <= -2000) {
            // Muat ulang halaman: server akan logout dan mengarahkan ke login
            window.location.reload();
        } else if (remainingMs <= warningMs) {
            $('#idle-countdown').text(Math.max(0, Math.ceil(remainingMs / 1000)));
            $('#idle-warning').fadeIn(200);
        } else {
            $('#idle-warning').hide();
        }
    }, 1000);
}
