document.addEventListener('DOMContentLoaded', function () {
    const videoModal = document.getElementById('medical-video-modal');
    if (!videoModal) return;

    const closeBtn = videoModal.querySelector('.medical-modal-close');
    const video = videoModal.querySelector('video');
    const iframe = videoModal.querySelector('#medical-video-iframe');
    const playBtn = videoModal.querySelector('.medical-modal-play-btn');

    // ── Botón play para video nativo ─────────────────────────────────────────
    if (playBtn && video) {
        playBtn.addEventListener('click', function () {
            video.play();
        });
        video.addEventListener('play', () => playBtn.classList.add('is-hidden'));
        video.addEventListener('pause', () => playBtn.classList.remove('is-hidden'));
        video.addEventListener('ended', () => playBtn.classList.remove('is-hidden'));
    }

    // ── Abrir modal ──────────────────────────────────────────────────────────
    function openModal(e) {
        if (e) e.preventDefault();
        videoModal.classList.add('is-visible');
        document.body.style.overflow = 'hidden';

        if (video) {
            video.currentTime = 0;
            video.play();
        }

        // Cargar iframe solo al abrir (evita autoplay antes de tiempo)
        if (iframe && !iframe.src) {
            iframe.src = iframe.dataset.src;
        }
    }

    // ── Cerrar modal ─────────────────────────────────────────────────────────
    function closeModal() {
        videoModal.classList.remove('is-visible');
        document.body.style.overflow = '';

        if (video) {
            video.pause();
        }

        // Detener iframe vaciando el src (pausa YouTube/Vimeo)
        if (iframe) {
            iframe.src = '';
        }
    }

    // ── Autoplay behavior ────────────────────────────────────────────────────
    const autoplayBehavior = videoModal.getAttribute('data-autoplay');
    if (autoplayBehavior === 'always') {
        openModal();
    } else if (autoplayBehavior === 'once') {
        if (!localStorage.getItem('medical_video_seen')) {
            openModal();
            localStorage.setItem('medical_video_seen', 'true');
        }
    }

    // ── Triggers ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.js-open-video-modal').forEach(btn => {
        btn.addEventListener('click', openModal);
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Cerrar al hacer click en el backdrop
    videoModal.addEventListener('click', function (e) {
        if (e.target === videoModal) closeModal();
    });

    // Cerrar con Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && videoModal.classList.contains('is-visible')) {
            closeModal();
        }
    });
});
