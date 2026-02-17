<?php
/**
 * Medico Info Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering on.
 * @param array $context The context provided to the block by the post or it's parent block.
 */

// Support custom "anchor" values.
$anchor = '';
if (!empty($block['anchor'])) {
    $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'medico-info-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

// Load values and assign defaults.
$foto = get_field('foto_perfil');
$bio = get_field('biografia_corta');
$horarios = get_field('horarios_atencion');
$sedes = get_field('sedes_atencion');
?>

<div <?php echo $anchor; ?>class="
    <?php echo esc_attr($class_name); ?>">
    <div class="medico-grid">
        <div class="medico-photo">
            <?php if ($foto): ?>
                <img src="<?php echo esc_url($foto['sizes']['medium']); ?>" alt="<?php echo esc_attr($foto['alt']); ?>" />
            <?php else: ?>
                <div class="placeholder-photo">No Photo</div>
            <?php endif; ?>
        </div>
        <div class="medico-content">
            <h2>
                <?php the_title(); ?>
            </h2>
            <?php if ($bio): ?>
                <div class="medico-bio">
                    <p>
                        <?php echo esc_html($bio); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($horarios): ?>
                <div class="medico-horarios">
                    <h3>Horarios de Atención</h3>
                    <ul>
                        <?php foreach ($horarios as $horario):
                            $sede_blk = isset($horario['sede']) ? $horario['sede'] : null;
                            $dia_blk = isset($horario['dia']) ? $horario['dia'] : '';
                            $inicio_blk = isset($horario['hora_inicio']) ? $horario['hora_inicio'] : '';
                            $fin_blk = isset($horario['hora_fin']) ? $horario['hora_fin'] : '';
                            ?>
                            <li>
                                <div class="schedule-main">
                                    <strong><?php echo ucfirst($dia_blk); ?>:</strong>
                                    <span><?php echo esc_html($inicio_blk); ?> - <?php echo esc_html($fin_blk); ?></span>
                                </div>
                                <?php if ($sede_blk): ?>
                                    <div class="schedule-location">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html($sede_blk->post_title); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<style>
    .medico-info-block {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-soft);
        padding: 40px;
        margin-bottom: 40px;
    }

    .medico-grid {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    .medico-photo {
        flex: 0 0 300px;
    }

    .medico-photo img {
        width: 100%;
        height: auto;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-soft);
    }

    .medico-content {
        flex: 1;
    }

    .medico-content h2 {
        font-size: 2.2rem;
        margin-bottom: 10px;
        color: var(--text-heading);
    }

    .medico-bio {
        font-size: 1.1rem;
        color: var(--text-body);
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .medico-horarios {
        background: var(--bg-light);
        padding: 25px;
        border-radius: var(--border-radius);
    }

    .medico-horarios h3 {
        font-size: 1.25rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .medico-horarios h3::before {
        content: '📅 ';
        font-size: 1.1em;
    }

    .medico-horarios ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .medico-horarios li {
        padding: 12px 0;
        border-bottom: 1px dashed rgba(97, 94, 252, 0.2);
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .medico-horarios li:last-child {
        border-bottom: none;
    }

    .schedule-main {
        display: flex;
        justify-content: space-between;
        width: 100%;
        align-items: center;
    }

    .schedule-location {
        font-size: 0.85rem;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
        background: rgba(0, 0, 0, 0.03);
        padding: 4px 10px;
        border-radius: 4px;
        align-self: flex-start;
    }

    .schedule-location .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        color: var(--primary-color);
    }

    .medico-horarios li strong {
        color: var(--primary-color);
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
    }

    @media (max-width: 768px) {
        .medico-grid {
            flex-direction: column;
        }

        .medico-photo {
            flex: 0 0 auto;
            width: 100%;
        }
    }
</style>