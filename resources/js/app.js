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
});
