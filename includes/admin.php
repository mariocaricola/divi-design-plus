<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// URL base de la página de ajustes
function ddp_page_url( string $extra = '' ): string {
	return admin_url( 'admin.php?page=divi-design-plus' . $extra );
}

// ─── Menú principal (sidebar) + entrada en Settings ──────────────────────────

add_action( 'admin_menu', 'ddp_register_admin_page' );

function ddp_register_admin_page(): void {
	// Menú de primer nivel con icono propio (posición 59, justo antes de Appearance)
	add_menu_page(
		'DIVI Design Plus',
		'Design Plus',
		'manage_options',
		'divi-design-plus',
		'ddp_render_admin_page',
		'dashicons-art',
		59
	);

	// Renombra el primer ítem del submenú (WordPress lo duplica por defecto)
	add_submenu_page(
		'divi-design-plus',
		'DIVI Design Plus — Efectos',
		'Efectos',
		'manage_options',
		'divi-design-plus',
		'ddp_render_admin_page'
	);

	// También disponible en Settings > DIVI Design Plus
	add_options_page(
		'DIVI Design Plus',
		'DIVI Design Plus',
		'manage_options',
		'divi-design-plus-settings',
		'ddp_redirect_to_main'
	);
}

// Redirige la entrada de Settings al menú principal
function ddp_redirect_to_main(): void {
	wp_safe_redirect( ddp_page_url() );
	exit;
}

// ─── Enlace "Settings" en la lista de plugins ─────────────────────────────────

add_filter( 'plugin_action_links_divi-design-plus/divi-design-plus.php', 'ddp_add_action_links' );

function ddp_add_action_links( array $links ): array {
	array_unshift( $links, sprintf(
		'<a href="%s">Settings</a>',
		esc_url( ddp_page_url() )
	) );
	return $links;
}

// ─── Assets del panel ─────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', 'ddp_enqueue_admin_assets' );

function ddp_enqueue_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, [ 'toplevel_page_divi-design-plus', 'design-plus_page_divi-design-plus' ], true ) ) return;

	wp_enqueue_style( 'divi-design-plus',      DDP_PLUGIN_URL . 'assets/css/main.css',  [], DDP_VERSION );
	wp_enqueue_style( 'divi-design-plus-admin', DDP_PLUGIN_URL . 'assets/css/admin.css', [], DDP_VERSION );
}

// ─── Guardar efecto personalizado ─────────────────────────────────────────────

add_action( 'admin_post_ddp_save_effect', 'ddp_handle_save_effect' );

function ddp_handle_save_effect(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado.' );
	check_admin_referer( 'ddp_save_effect' );

	$effects   = get_option( 'ddp_custom_effects', [] );
	$effects[] = [
		'name'        => sanitize_text_field( $_POST['ddp_name']            ?? '' ),
		'class'       => sanitize_html_class( $_POST['ddp_class']           ?? '' ),
		'description' => sanitize_textarea_field( $_POST['ddp_description'] ?? '' ),
		'css'         => wp_strip_all_tags( $_POST['ddp_css']               ?? '' ),
	];
	update_option( 'ddp_custom_effects', $effects );

	wp_safe_redirect( ddp_page_url( '&ddp_msg=saved' ) );
	exit;
}

// ─── Eliminar efecto personalizado ────────────────────────────────────────────

add_action( 'admin_post_ddp_delete_effect', 'ddp_handle_delete_effect' );

function ddp_handle_delete_effect(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado.' );
	check_admin_referer( 'ddp_delete_effect' );

	$index   = absint( $_POST['ddp_index'] ?? -1 );
	$effects = get_option( 'ddp_custom_effects', [] );

	if ( isset( $effects[ $index ] ) ) {
		array_splice( $effects, $index, 1 );
		update_option( 'ddp_custom_effects', $effects );
	}

	wp_safe_redirect( ddp_page_url( '&ddp_msg=deleted' ) );
	exit;
}

// ─── Guardar variables CSS ────────────────────────────────────────────────────

add_action( 'admin_post_ddp_save_vars', 'ddp_handle_save_vars' );

function ddp_handle_save_vars(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado.' );
	check_admin_referer( 'ddp_save_vars' );

	update_option( 'ddp_css_vars', [
		'glass_blur'      => absint( $_POST['ddp_glass_blur']      ?? 20 ),
		'glass_opacity'   => absint( $_POST['ddp_glass_opacity']   ?? 12 ),
		'glass_border'    => absint( $_POST['ddp_glass_border']    ?? 30 ),
		'bento_radius'    => absint( $_POST['ddp_bento_radius']    ?? 24 ),
		'bento_shadow'    => absint( $_POST['ddp_bento_shadow']    ?? 5  ),
		'lift_y'          => absint( $_POST['ddp_lift_y']          ?? 10 ),
		'lift_shadow'     => absint( $_POST['ddp_lift_shadow']     ?? 16 ),
		'reveal_duration' => round( (float) ( $_POST['ddp_reveal_duration'] ?? 0.65 ), 2 ),
		'slideup_dist'    => absint( $_POST['ddp_slideup_dist']    ?? 36 ),
		'reveal_scale'    => absint( $_POST['ddp_reveal_scale']    ?? 94 ),
		'aurora_duration' => absint( $_POST['ddp_aurora_duration'] ?? 10 ),
		'aurora_palette'  => sanitize_key( $_POST['ddp_aurora_palette'] ?? 'stripe' ),
		'custom_c1'       => sanitize_hex_color( $_POST['ddp_custom_c1'] ?? '#ee7752' ),
		'custom_c2'       => sanitize_hex_color( $_POST['ddp_custom_c2'] ?? '#e73c7e' ),
		'custom_c3'       => sanitize_hex_color( $_POST['ddp_custom_c3'] ?? '#23a6d5' ),
		'custom_c4'       => sanitize_hex_color( $_POST['ddp_custom_c4'] ?? '#23d5ab' ),
		'custom_c5'       => sanitize_hex_color( $_POST['ddp_custom_c5'] ?? '#6c63ff' ),
		'custom_c6'       => sanitize_hex_color( $_POST['ddp_custom_c6'] ?? '#f7971e' ),
		'orb_c1'          => sanitize_hex_color( $_POST['ddp_orb_c1']    ?? '#667eea' ),
		'orb_c2'          => sanitize_hex_color( $_POST['ddp_orb_c2']    ?? '#f093fb' ),
		'orb_c3'          => sanitize_hex_color( $_POST['ddp_orb_c3']    ?? '#4facfe' ),
		'orb_blur'        => absint( $_POST['ddp_orb_blur']     ?? 80 ),
		'orb_opacity'     => absint( $_POST['ddp_orb_opacity']  ?? 55 ),
		'orb_duration'    => absint( $_POST['ddp_orb_duration'] ?? 8  ),
	] );

	wp_safe_redirect( ddp_page_url( '&tab=customize&ddp_msg=vars_saved' ) );
	exit;
}

// ─── Restablecer variables CSS ────────────────────────────────────────────────

add_action( 'admin_post_ddp_reset_vars', 'ddp_handle_reset_vars' );

function ddp_handle_reset_vars(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado.' );
	check_admin_referer( 'ddp_reset_vars' );

	delete_option( 'ddp_css_vars' );

	wp_safe_redirect( ddp_page_url( '&tab=customize&ddp_msg=vars_reset' ) );
	exit;
}

// ─── Render de la página ──────────────────────────────────────────────────────

function ddp_render_admin_page(): void {
	$tab            = sanitize_key( $_GET['tab'] ?? 'effects' );
	$custom_effects = get_option( 'ddp_custom_effects', [] );
	$msg            = sanitize_key( $_GET['ddp_msg'] ?? '' );

	$builtin = [
		[ 'name' => 'Liquid Glass',    'class' => 'ddp-glass',      'description' => 'Cristal líquido con backdrop-filter blur 20 px, saturación 180 % y borde de luz interna.', 'preview_bg' => 'linear-gradient(135deg,#667eea,#764ba2)' ],
		[ 'name' => 'Bento SaaS',      'class' => 'ddp-bento',      'description' => 'Tarjeta estilo bento con sombra premium en tres capas y border-radius de 24 px.',           'preview_bg' => 'linear-gradient(135deg,#f5f7fa,#c3cfe2)' ],
		[ 'name' => 'Aurora Gradient', 'class' => 'ddp-aurora',     'description' => 'Fondo animado tipo Stripe: gradiente mesh con movimiento fluido de colores.',               'preview_bg' => 'none' ],
		[ 'name' => 'Hover Lift',      'class' => 'ddp-hover-lift', 'description' => 'Elevación magnética al hacer hover con cubic-bezier de rebote suave.',                     'preview_bg' => 'linear-gradient(135deg,#ffecd2,#fcb69f)' ],
		[ 'name' => 'Fade In',         'class' => 'ddp-fade-in',    'description' => 'Aparición suave al entrar en el viewport. Requiere scroll para activarse.',                 'preview_bg' => 'linear-gradient(135deg,#a1c4fd,#c2e9fb)' ],
		[ 'name' => 'Slide Up',        'class' => 'ddp-slide-up',   'description' => 'Deslizamiento hacia arriba + fade al entrar en el viewport.',                              'preview_bg' => 'linear-gradient(135deg,#d4fc79,#96e6a1)' ],
		[ 'name' => 'Reveal',          'class' => 'ddp-reveal',     'description' => 'Escala desde 94 % + fade al entrar en el viewport.',                                       'preview_bg' => 'linear-gradient(135deg,#fbc2eb,#a6c1ee)' ],
		[ 'name' => 'Gradient Orbs',   'class' => 'ddp-orbs',       'description' => 'Tres orbs de color difusos que flotan lentamente. Efecto estilo Linear / Vercel.',            'preview_bg' => 'none' ],
	];
	?>
	<div class="wrap ddp-admin">

		<div class="ddp-admin-header">
			<span class="ddp-logo">✦</span>
			<div>
				<h1>DIVI Design Plus</h1>
				<p class="ddp-subtitle">Efectos CSS premium para Divi 5 · v<?php echo esc_html( DDP_VERSION ); ?></p>
			</div>
		</div>

		<?php if ( $msg === 'saved' ): ?>
			<div class="notice notice-success is-dismissible"><p>✓ Efecto guardado correctamente.</p></div>
		<?php elseif ( $msg === 'deleted' ): ?>
			<div class="notice notice-success is-dismissible"><p>✓ Efecto eliminado.</p></div>
		<?php elseif ( $msg === 'vars_saved' ): ?>
			<div class="notice notice-success is-dismissible"><p>✓ Variables guardadas. Los cambios ya están activos en el frontend.</p></div>
		<?php elseif ( $msg === 'vars_reset' ): ?>
			<div class="notice notice-success is-dismissible"><p>✓ Variables restablecidas a los valores por defecto.</p></div>
		<?php endif; ?>

		<nav class="nav-tab-wrapper ddp-tabs">
			<a href="<?php echo esc_url( ddp_page_url( '&tab=effects' ) ); ?>"
			   class="nav-tab <?php echo $tab === 'effects' ? 'nav-tab-active' : ''; ?>">🎨 Efectos</a>
			<a href="<?php echo esc_url( ddp_page_url( '&tab=customize' ) ); ?>"
			   class="nav-tab <?php echo $tab === 'customize' ? 'nav-tab-active' : ''; ?>">⚙️ Personalizar</a>
			<a href="<?php echo esc_url( ddp_page_url( '&tab=help' ) ); ?>"
			   class="nav-tab <?php echo $tab === 'help' ? 'nav-tab-active' : ''; ?>">📖 Ayuda</a>
		</nav>

		<div class="ddp-tab-content">

		<?php if ( $tab === 'effects' ): ?>

			<h2 class="ddp-section-title">Efectos incluidos <span class="ddp-badge ddp-badge-builtin"><?php echo count( $builtin ); ?></span></h2>
			<div class="ddp-grid">
				<?php foreach ( $builtin as $e ): ?>
				<div class="ddp-card">
					<div class="ddp-preview" style="background:<?php echo esc_attr( $e['preview_bg'] ); ?>">
						<div class="ddp-preview-inner <?php echo esc_attr( $e['class'] ); ?> <?php echo $e['class'] === 'ddp-aurora' ? '' : 'is-visible'; ?>">Aa</div>
					</div>
					<div class="ddp-card-body">
						<strong><?php echo esc_html( $e['name'] ); ?></strong>
						<code class="ddp-class-pill">.<?php echo esc_html( $e['class'] ); ?></code>
						<p><?php echo esc_html( $e['description'] ); ?></p>
					</div>
					<div class="ddp-card-footer">
						<span class="ddp-tag">Incluido</span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $custom_effects ) ): ?>
			<h2 class="ddp-section-title" style="margin-top:2em">
				Efectos personalizados <span class="ddp-badge ddp-badge-custom"><?php echo count( $custom_effects ); ?></span>
			</h2>
			<div class="ddp-grid">
				<?php foreach ( $custom_effects as $i => $e ): ?>
				<div class="ddp-card ddp-card-custom">
					<div class="ddp-preview" style="background:linear-gradient(135deg,#434343,#000)">
						<div class="ddp-preview-inner <?php echo esc_attr( $e['class'] ); ?> is-visible">Aa</div>
					</div>
					<div class="ddp-card-body">
						<strong><?php echo esc_html( $e['name'] ); ?></strong>
						<code class="ddp-class-pill">.<?php echo esc_html( $e['class'] ); ?></code>
						<?php if ( $e['description'] ): ?>
							<p><?php echo esc_html( $e['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<div class="ddp-card-footer">
						<span class="ddp-tag ddp-tag-custom">Personalizado</span>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
							  onsubmit="return confirm('¿Eliminar «<?php echo esc_js( $e['name'] ); ?>»?')">
							<input type="hidden" name="action"    value="ddp_delete_effect">
							<input type="hidden" name="ddp_index" value="<?php echo $i; ?>">
							<?php wp_nonce_field( 'ddp_delete_effect' ); ?>
							<button type="submit" class="button ddp-btn-delete">✕ Eliminar</button>
						</form>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="ddp-add-box">
				<h2 class="ddp-section-title">➕ Añadir nuevo efecto personalizado</h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ddp-form">
					<input type="hidden" name="action" value="ddp_save_effect">
					<?php wp_nonce_field( 'ddp_save_effect' ); ?>

					<div class="ddp-form-row">
						<div class="ddp-form-group">
							<label for="ddp_name">Nombre del efecto *</label>
							<input type="text" id="ddp_name" name="ddp_name" required placeholder="Mi Efecto Premium">
						</div>
						<div class="ddp-form-group">
							<label for="ddp_class">Clase CSS *</label>
							<input type="text" id="ddp_class" name="ddp_class" required placeholder="ddp-mi-efecto">
						</div>
					</div>

					<div class="ddp-form-group">
						<label for="ddp_description">Descripción <small>(opcional)</small></label>
						<input type="text" id="ddp_description" name="ddp_description" placeholder="Breve descripción de lo que hace el efecto">
					</div>

					<div class="ddp-form-group">
						<label for="ddp_css">Código CSS *</label>
						<textarea id="ddp_css" name="ddp_css" rows="9" required
							placeholder=".ddp-mi-efecto {
  /* Escribe aquí tu CSS */
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 16px;
  color: #fff;
}"></textarea>
					</div>

					<button type="submit" class="button button-primary ddp-btn-save">Guardar efecto</button>
				</form>
			</div>

		<?php elseif ( $tab === 'customize' ):
			$saved = get_option( 'ddp_css_vars', [] );
			$v = wp_parse_args( $saved, [
				'glass_blur'      => 20,   'glass_opacity'   => 12,  'glass_border'    => 30,
				'bento_radius'    => 24,   'bento_shadow'    => 5,
				'lift_y'          => 10,   'lift_shadow'     => 16,
				'reveal_duration' => 0.65, 'slideup_dist'    => 36,  'reveal_scale'    => 94,
				'aurora_duration' => 10,   'aurora_palette'  => 'stripe',
				'custom_c1'       => '#ee7752', 'custom_c2'  => '#e73c7e', 'custom_c3'  => '#23a6d5',
				'custom_c4'       => '#23d5ab', 'custom_c5'  => '#6c63ff', 'custom_c6'  => '#f7971e',
				'orb_c1'          => '#667eea', 'orb_c2'     => '#f093fb', 'orb_c3'     => '#4facfe',
				'orb_blur'        => 80,        'orb_opacity' => 55,       'orb_duration' => 8,
			] );

			$sections = [
				[
					'title' => '🫧 Glass',
					'controls' => [
						[ 'key' => 'glass_blur',     'label' => 'Blur',             'desc' => 'Intensidad del desenfoque',           'min' => 0,   'max' => 60,  'step' => 1,    'unit' => 'px', 'default' => 20   ],
						[ 'key' => 'glass_opacity',  'label' => 'Opacidad fondo',   'desc' => 'Transparencia del fondo de cristal',  'min' => 0,   'max' => 80,  'step' => 1,    'unit' => '%',  'default' => 12   ],
						[ 'key' => 'glass_border',   'label' => 'Opacidad borde',   'desc' => 'Intensidad del borde de luz interna', 'min' => 0,   'max' => 100, 'step' => 1,    'unit' => '%',  'default' => 30   ],
					],
				],
				[
					'title' => '🃏 Bento',
					'controls' => [
						[ 'key' => 'bento_radius',   'label' => 'Radio esquinas',   'desc' => 'Redondez de las esquinas',            'min' => 0,   'max' => 48,  'step' => 1,    'unit' => 'px', 'default' => 24   ],
						[ 'key' => 'bento_shadow',   'label' => 'Sombra',           'desc' => 'Intensidad de la sombra exterior',    'min' => 0,   'max' => 30,  'step' => 1,    'unit' => '%',  'default' => 5    ],
					],
				],
				[
					'title' => '✨ Hover Lift',
					'controls' => [
						[ 'key' => 'lift_y',         'label' => 'Elevación',        'desc' => 'Píxeles que sube al hacer hover',     'min' => 0,   'max' => 40,  'step' => 1,    'unit' => 'px', 'default' => 10   ],
						[ 'key' => 'lift_shadow',    'label' => 'Sombra al hover',  'desc' => 'Opacidad de la sombra al elevar',     'min' => 0,   'max' => 50,  'step' => 1,    'unit' => '%',  'default' => 16   ],
					],
				],
				[
					'title' => '🎬 Scroll Reveal',
					'controls' => [
						[ 'key' => 'reveal_duration','label' => 'Velocidad',        'desc' => 'Duración de fade-in, slide-up, reveal','min' => 0.1, 'max' => 2,   'step' => 0.05, 'unit' => 's',  'default' => 0.65 ],
						[ 'key' => 'slideup_dist',   'label' => 'Distancia slide',  'desc' => 'Píxeles que sube en slide-up',        'min' => 10,  'max' => 100, 'step' => 1,    'unit' => 'px', 'default' => 36   ],
						[ 'key' => 'reveal_scale',   'label' => 'Escala inicial',   'desc' => 'Tamaño de partida del efecto reveal', 'min' => 70,  'max' => 99,  'step' => 1,    'unit' => '%',  'default' => 94   ],
					],
				],
			];
			$palettes = ddp_aurora_palettes();
		?>
			<h2 class="ddp-section-title">Personalizar valores de los efectos</h2>
			<p style="color:#6b7280;margin-bottom:24px;font-size:13px;">Los cambios se aplican automáticamente en el frontend al guardar. Sin tocar código.</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ddp_save_vars">
				<?php wp_nonce_field( 'ddp_save_vars' ); ?>

				<?php foreach ( $sections as $section ): ?>
				<div class="ddp-vars-section">
					<h3 class="ddp-vars-section-title"><?php echo esc_html( $section['title'] ); ?></h3>
					<div class="ddp-vars-grid">
						<?php foreach ( $section['controls'] as $c ):
							$val = $c['step'] < 1 ? (float) $v[ $c['key'] ] : absint( $v[ $c['key'] ] );
						?>
						<div class="ddp-var-card">
							<div class="ddp-var-header">
								<strong><?php echo esc_html( $c['label'] ); ?></strong>
								<span class="ddp-var-default">Def: <?php echo esc_html( $c['default'] . $c['unit'] ); ?></span>
							</div>
							<p class="ddp-var-desc"><?php echo esc_html( $c['desc'] ); ?></p>
							<div class="ddp-var-input-row">
								<input type="range"
									   name="ddp_<?php echo esc_attr( $c['key'] ); ?>"
									   min="<?php echo esc_attr( $c['min'] ); ?>" max="<?php echo esc_attr( $c['max'] ); ?>" step="<?php echo esc_attr( $c['step'] ); ?>"
									   value="<?php echo esc_attr( $val ); ?>"
									   class="ddp-range" data-target="ddp_num_<?php echo esc_attr( $c['key'] ); ?>">
								<input type="number"
									   id="ddp_num_<?php echo esc_attr( $c['key'] ); ?>"
									   min="<?php echo esc_attr( $c['min'] ); ?>" max="<?php echo esc_attr( $c['max'] ); ?>" step="<?php echo esc_attr( $c['step'] ); ?>"
									   value="<?php echo esc_attr( $val ); ?>"
									   class="ddp-number" data-range="ddp_<?php echo esc_attr( $c['key'] ); ?>">
								<span class="ddp-unit"><?php echo esc_html( $c['unit'] ); ?></span>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>

				<!-- Orbs -->
				<div class="ddp-vars-section">
					<h3 class="ddp-vars-section-title">🔮 Gradient Orbs</h3>
					<div class="ddp-vars-grid">
						<div class="ddp-var-card">
							<div class="ddp-var-header"><strong>Blur</strong><span class="ddp-var-default">Def: 80px</span></div>
							<p class="ddp-var-desc">Suavidad del difuminado de cada orb</p>
							<div class="ddp-var-input-row">
								<input type="range" name="ddp_orb_blur" min="20" max="150" step="5" value="<?php echo esc_attr( absint( $v['orb_blur'] ) ); ?>" class="ddp-range" data-target="ddp_num_orb_blur">
								<input type="number" id="ddp_num_orb_blur" min="20" max="150" step="5" value="<?php echo esc_attr( absint( $v['orb_blur'] ) ); ?>" class="ddp-number" data-range="ddp_orb_blur">
								<span class="ddp-unit">px</span>
							</div>
						</div>
						<div class="ddp-var-card">
							<div class="ddp-var-header"><strong>Opacidad</strong><span class="ddp-var-default">Def: 55%</span></div>
							<p class="ddp-var-desc">Transparencia de los orbs</p>
							<div class="ddp-var-input-row">
								<input type="range" name="ddp_orb_opacity" min="10" max="90" step="1" value="<?php echo esc_attr( absint( $v['orb_opacity'] ) ); ?>" class="ddp-range" data-target="ddp_num_orb_opacity">
								<input type="number" id="ddp_num_orb_opacity" min="10" max="90" step="1" value="<?php echo esc_attr( absint( $v['orb_opacity'] ) ); ?>" class="ddp-number" data-range="ddp_orb_opacity">
								<span class="ddp-unit">%</span>
							</div>
						</div>
						<div class="ddp-var-card">
							<div class="ddp-var-header"><strong>Velocidad</strong><span class="ddp-var-default">Def: 8s</span></div>
							<p class="ddp-var-desc">Duración de un ciclo de movimiento</p>
							<div class="ddp-var-input-row">
								<input type="range" name="ddp_orb_duration" min="3" max="20" step="1" value="<?php echo esc_attr( absint( $v['orb_duration'] ) ); ?>" class="ddp-range" data-target="ddp_num_orb_duration">
								<input type="number" id="ddp_num_orb_duration" min="3" max="20" step="1" value="<?php echo esc_attr( absint( $v['orb_duration'] ) ); ?>" class="ddp-number" data-range="ddp_orb_duration">
								<span class="ddp-unit">s</span>
							</div>
						</div>
					</div>
					<p style="font-size:13px;font-weight:600;color:#374151;margin:4px 0 10px;">Colores de los orbs</p>
					<div class="ddp-color-pickers">
						<?php foreach ( [ 'orb_c1' => 'Orb 1', 'orb_c2' => 'Orb 2', 'orb_c3' => 'Orb 3' ] as $key => $label ): ?>
						<div class="ddp-color-item">
							<label for="ddp_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
							<input type="color" id="ddp_<?php echo esc_attr( $key ); ?>"
								   name="ddp_<?php echo esc_attr( $key ); ?>"
								   value="<?php echo esc_attr( $v[ $key ] ); ?>"
								   class="ddp-color-input">
						</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Aurora -->
				<div class="ddp-vars-section">
					<h3 class="ddp-vars-section-title">🌌 Aurora</h3>
					<div class="ddp-vars-grid">
						<div class="ddp-var-card">
							<div class="ddp-var-header">
								<strong>Velocidad</strong>
								<span class="ddp-var-default">Def: 10s</span>
							</div>
							<p class="ddp-var-desc">Duración de un ciclo completo del gradiente</p>
							<div class="ddp-var-input-row">
								<input type="range" name="ddp_aurora_duration" min="3" max="30" step="1"
									   value="<?php echo esc_attr( absint( $v['aurora_duration'] ) ); ?>"
									   class="ddp-range" data-target="ddp_num_aurora_duration">
								<input type="number" id="ddp_num_aurora_duration" min="3" max="30" step="1"
									   value="<?php echo esc_attr( absint( $v['aurora_duration'] ) ); ?>"
									   class="ddp-number" data-range="ddp_aurora_duration">
								<span class="ddp-unit">s</span>
							</div>
						</div>
					</div>

					<p style="font-size:13px;font-weight:600;color:#374151;margin:16px 0 10px;">Paleta de colores</p>
					<div class="ddp-palette-grid">
						<?php foreach ( $palettes as $key => $palette ): ?>
						<label class="ddp-palette-option <?php echo $v['aurora_palette'] === $key ? 'is-selected' : ''; ?>">
							<input type="radio" name="ddp_aurora_palette" value="<?php echo esc_attr( $key ); ?>"
								   <?php checked( $v['aurora_palette'], $key ); ?>>
							<span class="ddp-palette-swatch" style="background:linear-gradient(135deg,<?php echo esc_attr( implode( ',', $palette['colors'] ) ); ?>)"></span>
							<span class="ddp-palette-name"><?php echo esc_html( $palette['label'] ); ?></span>
						</label>
						<?php endforeach; ?>
						<label class="ddp-palette-option <?php echo $v['aurora_palette'] === 'custom' ? 'is-selected' : ''; ?>">
							<input type="radio" name="ddp_aurora_palette" value="custom"
								   <?php checked( $v['aurora_palette'], 'custom' ); ?>>
							<span class="ddp-palette-swatch ddp-palette-swatch-custom"
								  style="background:linear-gradient(135deg,<?php echo esc_attr( implode( ',', [ $v['custom_c1'], $v['custom_c2'], $v['custom_c3'], $v['custom_c4'], $v['custom_c5'], $v['custom_c6'] ] ) ); ?>)">
								<span class="ddp-palette-custom-icon">✏️</span>
							</span>
							<span class="ddp-palette-name">Personalizada</span>
						</label>
					</div>

					<div class="ddp-custom-colors <?php echo $v['aurora_palette'] === 'custom' ? 'is-visible' : ''; ?>" id="ddp-custom-colors">
						<p style="font-size:12px;color:#6b7280;margin:12px 0 10px;">Elige los 6 colores del gradiente:</p>
						<div class="ddp-color-pickers">
							<?php foreach ( range(1,6) as $i ): ?>
							<div class="ddp-color-item">
								<label for="ddp_custom_c<?php echo $i; ?>">Color <?php echo $i; ?></label>
								<input type="color" id="ddp_custom_c<?php echo $i; ?>"
									   name="ddp_custom_c<?php echo $i; ?>"
									   value="<?php echo esc_attr( $v[ 'custom_c' . $i ] ); ?>"
									   class="ddp-color-input">
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="ddp-vars-actions">
					<button type="submit" class="button button-primary ddp-btn-save">Guardar cambios</button>
				</div>
			</form>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px"
				  onsubmit="return confirm('¿Restablecer todos los valores al defecto?')">
				<input type="hidden" name="action" value="ddp_reset_vars">
				<?php wp_nonce_field( 'ddp_reset_vars' ); ?>
				<button type="submit" class="button ddp-btn-reset">Restablecer defectos</button>
			</form>

			<script>
			(function(){
				document.querySelectorAll('.ddp-range').forEach(function(range){
					var num = document.getElementById(range.dataset.target);
					if(!num) return;
					range.addEventListener('input', function(){ num.value = this.value; });
					num.addEventListener('input',   function(){ range.value = this.value; });
				});
				var customColors = document.getElementById('ddp-custom-colors');
				document.querySelectorAll('.ddp-palette-option input[type="radio"]').forEach(function(radio){
					radio.addEventListener('change', function(){
						document.querySelectorAll('.ddp-palette-option').forEach(function(el){ el.classList.remove('is-selected'); });
						radio.closest('.ddp-palette-option').classList.add('is-selected');
						if(customColors) customColors.classList.toggle('is-visible', radio.value === 'custom');
					});
				});
				// Update custom swatch live when color inputs change
				var customSwatch = document.querySelector('.ddp-palette-swatch-custom');
				document.querySelectorAll('.ddp-color-input').forEach(function(input){
					input.addEventListener('input', function(){
						if(!customSwatch) return;
						var colors = Array.from(document.querySelectorAll('.ddp-color-input')).map(function(i){ return i.value; });
						customSwatch.style.background = 'linear-gradient(135deg,' + colors.join(',') + ')';
					});
				});
			})();
			</script>

		<?php elseif ( $tab === 'help' ): ?>

			<div class="ddp-help">
				<h2>Cómo aplicar efectos en Divi 5</h2>

				<div class="ddp-help-steps">
					<div class="ddp-step">
						<span class="ddp-step-num">1</span>
						<div>
							<strong>Selecciona un módulo</strong>
							<p>Cualquier Section, Row, Column, Text, Blurb, Button, Image…</p>
						</div>
					</div>
					<div class="ddp-step">
						<span class="ddp-step-num">2</span>
						<div>
							<strong>Abre Advanced › Attributes</strong>
							<p>En el panel derecho del Visual Builder, ve a la pestaña <em>Advanced</em> y despliega <em>Attributes</em>.</p>
						</div>
					</div>
					<div class="ddp-step">
						<span class="ddp-step-num">3</span>
						<div>
							<strong>Añade el atributo <code>class</code></strong>
							<p>Haz clic en <strong>+ Add Attribute</strong>.<br>
							   · Attribute Name → <code>class</code><br>
							   · Attribute Value → el nombre de la clase (p.ej. <code>ddp-glass</code>)</p>
						</div>
					</div>
					<div class="ddp-step">
						<span class="ddp-step-num">4</span>
						<div>
							<strong>Combina clases</strong>
							<p>Puedes poner varias clases separadas por espacio:<br>
							<code>ddp-bento ddp-hover-lift ddp-slide-up</code></p>
						</div>
					</div>
				</div>

				<h2 style="margin-top:2em">Tabla de clases disponibles</h2>
				<table class="wp-list-table widefat fixed striped ddp-table">
					<thead>
						<tr><th>Clase</th><th>Efecto</th><th>Ideal para</th></tr>
					</thead>
					<tbody>
						<tr><td><code>ddp-glass</code></td>      <td>Liquid Glass — cristal líquido con blur y borde de luz</td>    <td>Secciones, cards, modales</td></tr>
						<tr><td><code>ddp-bento</code></td>      <td>Bento SaaS — sombra premium, borde sutil, radio 24 px</td>    <td>Blurbs, rows, textos</td></tr>
						<tr><td><code>ddp-aurora</code></td>     <td>Aurora Gradient — gradiente mesh animado tipo Stripe</td>      <td>Heroes, CTAs</td></tr>
						<tr><td><code>ddp-hover-lift</code></td> <td>Elevación magnética al hover con resorte cubic-bezier</td>     <td>Botones, cards, imágenes</td></tr>
						<tr><td><code>ddp-fade-in</code></td>    <td>Fade in al entrar en el viewport</td>                          <td>Cualquier módulo</td></tr>
						<tr><td><code>ddp-slide-up</code></td>   <td>Slide hacia arriba + fade al entrar en el viewport</td>        <td>Títulos, blurbs, columnas</td></tr>
						<tr><td><code>ddp-reveal</code></td>     <td>Escala + fade al entrar en el viewport</td>                    <td>Cards, imágenes, secciones</td></tr>
						<tr><td><code>ddp-orbs</code></td>       <td>Gradient Orbs — tres blobs de color difusos flotantes</td>      <td>Heroes, CTAs, secciones</td></tr>
					</tbody>
				</table>

				<h2 style="margin-top:2em">Notas importantes</h2>
				<ul class="ddp-notes">
					<li>Los efectos de scroll (<code>ddp-fade-in</code>, <code>ddp-slide-up</code>, <code>ddp-reveal</code>) se activan <strong>una sola vez</strong> al entrar en pantalla.</li>
					<li>En navegadores sin soporte de <code>IntersectionObserver</code> (IE 11) los elementos se muestran directamente.</li>
					<li>Todos los efectos respetan <code>prefers-reduced-motion</code> para accesibilidad.</li>
					<li><code>ddp-glass</code> necesita un fondo de color detrás del elemento para que el blur sea visible.</li>
				</ul>
			</div>

		<?php endif; ?>
		</div><!-- .ddp-tab-content -->
	</div><!-- .wrap.ddp-admin -->
	<?php
}
