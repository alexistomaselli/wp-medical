<?php
/**
 * The template for displaying the front page
 *
 * @package Medical_Health
 */

get_header();
?>

<main id="primary" class="site-main">

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-badge">Cuidado Médico de Confianza</span>
                <h1 class="hero-title">Expertos médicos dedicados a tu <span>bienestar</span></h1>
                <p class="hero-description">En Medical, combinamos tecnología de vanguardia con un equipo humano
                    excepcional para ofrecerte la mejor atención médica personalizada.</p>
                <div class="hero-actions">
                    <a href="<?php echo esc_url(home_url('/medicos/')); ?>" class="btn-primary">Ver Staff
                        Médico</a>
                    <?php
                    $video_url = function_exists('get_field') ? get_field('video_promocional', 'option') : '';
                    if (!$video_url) {
                        $video_url = get_theme_mod('medical_video_promocional');
                    }

                    if ($video_url):
                        ?>
                        <a href="#" class="btn-secondary js-open-video-modal">
                            <span class="dashicons dashicons-video-alt3"
                                style="margin-right: 5px; vertical-align: middle;"></span>
                            Ver Video
                        </a>
                    <?php else: ?>
                        <a href="#" class="btn-secondary">Más Información</a>
                    <?php endif; ?>
                </div>
                <div class="hero-stats">
                    <div class="stat-card">
                        <span class="stat-value">50+</span>
                        <span class="stat-label">Especialistas</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">24/7</span>
                        <span class="stat-label">Soporte Médico</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">4.9</span>
                        <span class="stat-label">Valoración</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=1000&auto=format&fit=crop"
                        alt="Doctor en Medical">
                    <div class="floating-badge badge-top">
                        <span class="icon">🏆</span>
                        <span class="text">Líderes en Salud</span>
                    </div>
                    <div class="floating-badge badge-bottom">
                        <span class="icon">👨‍⚕️</span>
                        <span class="text">100% Profesionales</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<style>
    .hero-section {
        padding: 50px 0 100px;
        background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
        overflow: hidden;
        position: relative;
    }

    .hero-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .hero-badge {
        display: inline-block;
        background: rgba(97, 94, 252, 0.1);
        color: var(--primary-color);
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 25px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .hero-title {
        font-size: 4rem;
        line-height: 1.1;
        margin-bottom: 25px;
        color: var(--text-heading);
    }

    .hero-title span {
        color: var(--primary-color);
        position: relative;
    }

    .hero-description {
        font-size: 1.2rem;
        color: var(--text-body);
        margin-bottom: 40px;
        max-width: 550px;
    }

    .hero-actions {
        display: flex;
        gap: 20px;
        margin-bottom: 60px;
    }

    .btn-secondary {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        padding: 12px 28px;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-family: var(--font-heading);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: var(--primary-color);
        color: var(--white);
    }

    .hero-stats {
        display: flex;
        gap: 30px;
    }

    .stat-card {
        background: var(--white);
        padding: 15px 25px;
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-color);
        font-family: var(--font-heading);
    }

    .stat-label {
        font-size: 0.9rem;
        color: #666;
        font-weight: 500;
    }

    /* Hero Image Styles */
    .hero-image {
        position: relative;
    }

    .image-wrapper {
        position: relative;
        z-index: 1;
    }

    .image-wrapper img {
        width: 100%;
        border-radius: 30px;
        box-shadow: 20px 20px 60px rgba(0, 0, 0, 0.1);
    }

    .floating-badge {
        position: absolute;
        background: var(--white);
        padding: 12px 20px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 2;
        transition: all 0.3s ease;
    }

    .badge-top {
        top: 20%;
        left: -15%;
    }

    .badge-bottom {
        bottom: 15%;
        right: -10%;
    }

    .floating-badge:hover {
        transform: scale(1.05);
    }

    @media (max-width: 992px) {
        .hero-container {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero-title {
            font-size: 3rem;
        }

        .hero-actions {
            justify-content: center;
        }

        .hero-stats {
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-image {
            margin-top: 50px;
        }

        .badge-top,
        .badge-bottom {
            display: none;
        }
    }
</style>

<!-- Video Modal -->
<?php if ($video_url):
    $video_poster = get_theme_mod('medical_video_poster');
    $video_autoplay = get_theme_mod('medical_video_autoplay', 'none');
    ?>
    <div id="medical-video-modal" class="medical-modal-backdrop" data-autoplay="<?php echo esc_attr($video_autoplay); ?>">
        <div class="medical-modal-content">
            <button class="medical-modal-close" aria-label="Cerrar">&times;</button>
            <button class="medical-modal-play-btn" aria-label="Reproducir">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 5V19L19 12L8 5Z" />
                </svg>
            </button>
            <video controls <?php if ($video_poster)
                echo 'poster="' . esc_url($video_poster) . '"'; ?>>
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                Tu navegador no soporta el tag de video.
            </video>
        </div>
    </div>
<?php endif; ?>

<?php
get_footer();
