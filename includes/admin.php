<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Menu ─────────────────────────────────────────────────────────────────────

add_action( 'admin_menu', 'ddp_register_admin_page' );

function ddp_register_admin_page(): void {
	add_options_page(
		'DIVI Design Plus',
		'DIVI Design Plus',
		'manage_options',
		'divi-design-plus',
		'ddp_render_admin_page'
	);
}

// ─── "Settings" link on Plugins list page ────────────────────────────────────

add_filter( 'plugin_action_links_divi-design-plus/divi-design-plus.php', 'ddp_add_action_links' );

function ddp_add_action_links( array $links ): array {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=divi-design-plus' ) ),
		'Settings'
	);
	array_unshift( $links, $settings_link );
	return $links;
}

// ─── Admin assets ─────────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', 'ddp_enqueue_admin_assets' );

function ddp_enqueue_admin_assets( string $hook ): void {
	if ( $hook !== 'settings_page_divi-design-plus' ) return;

	wp_enqueue_style( 'divi-design-plus',       DDP_PLUGIN_URL . 'assets/css/main.css',  [], DDP_VERSION );
	wp_enqueue_style( 'divi-design-plus-admin',  DDP_PLUGIN_URL . 'assets/css/admin.css', [], DDP_VERSION );
}

// ─── Save new effect ──────────────────────────────────────────────────────────

add_action( 'admin_post_ddp_save_effect', 'ddp_handle_save_effect' );

function ddp_handle_save_effect(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado.' );
	check_admin_referer( 'ddp_save_effect' );

	$effects   = get_option( 'ddp_custom_effects', [] );
	$effects[] = [
		'name'        => sanitize_text_field( $_POST['ddp_name']        ?? '' ),
		'class'       => sanitize_html_class( $_POST['ddp_class']       ?? '' ),
		'description' => sanitize_textarea_field( $_POST['ddp_description'] ?? '' ),
		'css'         => wp_strip_all_tags( $_POST['ddp_css']           ?? '' ),
	];
	update_option( 'ddp_custom_effects', $effects );

	wp_redirect( admin_url( 'options-general.php?page=divi-design-plus&ddp_msg=saved' ) );
	exit;
}

// ─── Delete effect ────────────────────────────────────────────────────────────

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

	wp_redirect( admin_url( 'options-general.php?page=divi-design-plus&ddp_msg=deleted' ) );
	exit;
}

// ─── Render page ──────────────────────────────────────────────────────────────

function ddp_render_admin_page(): void {
	$tab            = sanitize_key( $_GET['tab'] ?? 'effects' );
	$custom_effects = get_option( 'ddp_custom_effects', [] );
	$msg            = $_GET['ddp_msg'] ?? '';

	$builtin = [
		[
			'name'        => 'Liquid Glass',
			'class'       => 'ddp-glass',
			'description' => 'Cristal líquido con backdrop-filter blur 20 px, saturación 180 % y borde de luz interna.',
			'preview_bg'  => 'linear-gradient(135deg,#667eea,#764ba2)',
		],
		[
			'name'        => 'Bento SaaS',
			'class'       => 'ddp-bento',
			'description' => 'Tarjeta estilo bento con sombra premium en tres capas y border-radius de 24 px.',
			'preview_bg'  => 'linear-gradient(135deg,#f5f7fa,#c3cfe2)',
		],
		[
			'name'        => 'Aurora Gradient',
			'class'       => 'ddp-aurora',
			'description' => 'Fondo animado tipo Stripe: gradiente mesh con movimiento fluido de colores.',
			'preview_bg'  => 'none',
		],
		[
			'name'        => 'Hover Lift',
			'class'       => 'ddp-hover-lift',
			'description' => 'Elevación magnética al hacer hover con cubic-bezier de rebote suave.',
			'preview_bg'  => 'linear-gradient(135deg,#ffecd2,#fcb69f)',
		],
		[
			'name'        => 'Fade In',
			'class'       => 'ddp-fade-in',
			'description' => 'Aparición suave al entrar en el viewport. Requiere scroll para activarse.',
			'preview_bg'  => 'linear-gradient(135deg,#a1c4fd,#c2e9fb)',
		],
		[
			'name'        => 'Slide Up',
			'class'       => 'ddp-slide-up',
			'description' => 'Deslizamiento hacia arriba + fade al entrar en el viewport.',
			'preview_bg'  => 'linear-gradient(135deg,#d4fc79,#96e6a1)',
		],
		[
			'name'        => 'Reveal',
			'class'       => 'ddp-reveal',
			'description' => 'Escala desde 94 % + fade al entrar en el viewport.',
			'preview_bg'  => 'linear-gradient(135deg,#fbc2eb,#a6c1ee)',
		],
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
		<?php endif; ?>

		<nav class="nav-tab-wrapper ddp-tabs">
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=divi-design-plus&tab=effects' ) ); ?>"
			   class="nav-tab <?php echo $tab === 'effects' ? 'nav-tab-active' : ''; ?>">
				🎨 Efectos
			</a>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=divi-design-plus&tab=help' ) ); ?>"
			   class="nav-tab <?php echo $tab === 'help' ? 'nav-tab-active' : ''; ?>">
				📖 Ayuda
			</a>
		</nav>

		<div class="ddp-tab-content">

		<?php if ( $tab === 'effects' ): ?>

			<!-- ── Efectos incluidos ── -->
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

			<!-- ── Efectos personalizados ── -->
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

			<!-- ── Añadir efecto ── -->
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
						<tr>
							<th>Clase</th>
							<th>Efecto</th>
							<th>Ideal para</th>
						</tr>
					</thead>
					<tbody>
						<tr><td><code>ddp-glass</code></td>      <td>Liquid Glass — cristal líquido con blur y borde de luz</td>     <td>Secciones, cards, modales</td></tr>
						<tr><td><code>ddp-bento</code></td>      <td>Bento SaaS — sombra premium, borde sutil, radio 24 px</td>     <td>Blurbs, rows, textos</td></tr>
						<tr><td><code>ddp-aurora</code></td>     <td>Aurora Gradient — gradiente mesh animado tipo Stripe</td>       <td>Heroes, CTAs</td></tr>
						<tr><td><code>ddp-hover-lift</code></td> <td>Elevación magnética al hover con resorte cubic-bezier</td>      <td>Botones, cards, imágenes</td></tr>
						<tr><td><code>ddp-fade-in</code></td>    <td>Fade in al entrar en el viewport</td>                           <td>Cualquier módulo</td></tr>
						<tr><td><code>ddp-slide-up</code></td>   <td>Slide hacia arriba + fade al entrar en el viewport</td>         <td>Títulos, blurbs, columnas</td></tr>
						<tr><td><code>ddp-reveal</code></td>     <td>Escala + fade al entrar en el viewport</td>                     <td>Cards, imágenes, secciones</td></tr>
					</tbody>
				</table>

				<h2 style="margin-top:2em">Variables CSS personalizables</h2>
				<p>Pega esto en <strong>Divi › Theme Options › Custom CSS</strong> y ajusta los valores:</p>
				<pre class="ddp-code">:root {
  --ddp-glass-blur:      32px;      /* defecto: 20px  */
  --ddp-bento-radius:    16px;      /* defecto: 24px  */
  --ddp-lift-y:          -14px;     /* defecto: -10px */
  --ddp-reveal-duration: 0.8s;      /* defecto: 0.65s */
}</pre>

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
