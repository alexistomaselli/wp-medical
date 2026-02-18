document.addEventListener('DOMContentLoaded', function () {
    const videoModal = document.getElementById('medical-video-modal');
    if (!videoModal) return;

    const modalContent = videoModal.querySelector('.medical-modal-content');
    const modalBackdrop = videoModal.parentElement; // Assuming container is backdrop
    const closeBtn = videoModal.querySelector('.medical-modal-close');
    const video = videoModal.querySelector('video');
    const playBtn = videoModal.querySelector('.medical-modal-play-btn');

    if (playBtn && video) {
        playBtn.addEventListener('click', function () {
            video.play();
        });

        video.addEventListener('play', function () {
            playBtn.classList.add('is-hidden');
        });

        video.addEventListener('pause', function () {
            playBtn.classList.remove('is-hidden');
        });

        video.addEventListener('ended', function () {
            playBtn.classList.remove('is-hidden');
        });
    }

    // Find trigger buttons - could be multiple
    const triggerBtns = document.querySelectorAll('.js-open-video-modal');

    function openModal(e) {
        if (e) e.preventDefault();
        videoModal.classList.add('is-visible');
        if (video) {
            video.currentTime = 0;
            video.play();
        }
        document.body.style.overflow = 'hidden'; // Prevent scroll
    }

    // Check for autoplay behavior
    const autoplayBehavior = videoModal.getAttribute('data-autoplay');

    if (autoplayBehavior === 'always') {
        openModal();
    } else if (autoplayBehavior === 'once') {
        if (!localStorage.getItem('medical_video_seen')) {
            openModal();
            localStorage.setItem('medical_video_seen', 'true');
        }
    }


    function closeModal() {
        videoModal.classList.remove('is-visible');
        if (video) {
            video.pause();
        }
        document.body.style.overflow = '';
    }

    triggerBtns.forEach(btn => {
        btn.addEventListener('click', openModal);
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Close on click outside (backdrop)
    videoModal.addEventListener('click', function (e) {
        if (e.target === videoModal) {
            closeModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && videoModal.classList.contains('is-visible')) {
            closeModal();
        }
    });
});
