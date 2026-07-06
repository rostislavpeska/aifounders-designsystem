<?php
/**
 * AIGDS SANDBOX — full-width experiment pages (operator 2026-07-06).
 *
 * A safe environment for homepage-scale proposals: complete replica
 * sections composed ONLY from DS tokens/components + per-experiment CSS
 * that lives WITH the experiment (never in components.css). Nothing here
 * is canon; pages exist to be judged and then promoted or deleted.
 *
 * Route: /?aigds_sandbox=1&page={slug}&theme={brand}
 * Registry: aigds_sandbox_pages() — slug => [title, callback].
 *
 * @package AIGDS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aigds_sandbox_query_vars( $vars ) {
	$vars[] = 'aigds_sandbox';
	return $vars;
}
add_filter( 'query_vars', 'aigds_sandbox_query_vars' );

function aigds_sandbox_pages() {
	return array(
		'benefits-a' => array( 'A — ONE primitive (aggressive unification)', 'aigds_sandbox_benefits_a' ),
		'benefits-b' => array( 'B — TWO rows: benefit-card + blurb (middle)', 'aigds_sandbox_benefits_b' ),
		'benefits-c' => array( 'C — THREE components (conservative)', 'aigds_sandbox_benefits_c' ),
		'infobar'    => array( 'INFO BAR — three upgrade intensities (operator round)', 'aigds_sandbox_infobar' ),
		'aif-home-v1' => array( 'AIF HOMEPAGE clone · V1 conservative (production, tokens only)', 'aigds_sandbox_aif_home_v1' ),
		'aif-home-v2' => array( 'AIF HOMEPAGE clone · V2 — THE WORKING CONCEPT (canon info-bar + proposal-A benefits, no icons, secondary CTAs)', 'aigds_sandbox_aif_home_v2' ),
		'aif-home-v3' => array( 'AIF HOMEPAGE clone · V3 transformed (ONE blockquote grammar everywhere)', 'aigds_sandbox_aif_home_v3' ),
	);
}

function aigds_sandbox_maybe_render() {
	$on = get_query_var( 'aigds_sandbox' );
	if ( ! $on && isset( $_GET['aigds_sandbox'] ) ) {
		$on = sanitize_key( $_GET['aigds_sandbox'] );
	}
	if ( ! $on ) {
		return;
	}
	$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
	nocache_headers();
	aigds_sandbox_render( $page );
	exit;
}
add_action( 'template_redirect', 'aigds_sandbox_maybe_render' );

function aigds_sandbox_render( $page ) {
	$brand = aigds_styleguide_brand();
	$GLOBALS['aigds_sg_brand'] = $brand;
	$pages = aigds_sandbox_pages();
	$title = isset( $pages[ $page ] ) ? $pages[ $page ][0] : 'Sandbox index';

	header( 'Content-Type: text/html; charset=utf-8' );
	?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo esc_attr( $brand ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>SANDBOX · <?php echo esc_html( $title . ' — ' . $brand ); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&family=Space+Grotesk:wght@400;500;700&family=Spline+Sans+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( AIGDS_URL . 'assets/css/tokens.css?v=' . AIGDS_VERSION ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( AIGDS_URL . 'assets/css/components.css?v=' . AIGDS_VERSION ); ?>">
<style>
body { margin: 0; font-family: var(--font-primary); color: var(--text); background: var(--bg); }
/* minimal sandbox chrome — a fixed top strip, never confusable with content */
.sb-chrome { position: sticky; top: 0; z-index: 100; background: var(--black); color: var(--paper, #fff); display: flex; gap: 16px; align-items: center; padding: 8px 16px; font: 12px/1 var(--font-mono); text-transform: uppercase; letter-spacing: 0.08em; }
.sb-chrome a { color: inherit; }
.sb-chrome .sb-tag { background: var(--magenta); color: #fff; padding: 3px 8px; }
.sb-section-label { font: 700 11px/1 var(--font-mono); text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-tertiary); padding: 32px 24px 8px; border-top: 1px dashed var(--border-strong); }
.sb-index { max-width: 720px; margin: 64px auto; padding: 0 24px; }
</style>
</head>
<body>
<div class="sb-chrome">
	<span class="sb-tag">SANDBOX</span>
	<span><?php echo esc_html( $title ); ?></span>
	<span style="margin-left:auto;">
		<a href="<?php echo esc_url( add_query_arg( array( 'aigds_sandbox' => '1' ), home_url( '/' ) ) ); ?>">index</a>
		<?php foreach ( array( 'aiguild', 'aifounders' ) as $b ) : ?>
			· <a href="<?php echo esc_url( add_query_arg( array( 'aigds_sandbox' => '1', 'page' => $page, 'theme' => $b ), home_url( '/' ) ) ); ?>"<?php echo $b === $brand ? ' style="text-decoration:underline;"' : ''; ?>><?php echo esc_html( $b ); ?></a>
		<?php endforeach; ?>
	</span>
</div>
<?php
	if ( isset( $pages[ $page ] ) ) {
		call_user_func( $pages[ $page ][1] );
	} else {
		?>
		<div class="sb-index">
			<h1 class="heading-md">Sandbox experiments</h1>
			<p class="body-md">Full-width proposal pages — DS tokens + per-experiment CSS. Nothing here is canon.</p>
			<ul>
				<?php foreach ( $pages as $slug => $def ) : ?>
				<li style="margin:8px 0;"><a href="<?php echo esc_url( add_query_arg( array( 'aigds_sandbox' => '1', 'page' => $slug, 'theme' => $brand ), home_url( '/' ) ) ); ?>"><?php echo esc_html( $def[0] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
?>
</body>
</html>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════════
   SHARED DEMO CONTENT — the seven replica contexts (English, realistic)
   ═══════════════════════════════════════════════════════════════════════ */

function aigds_sb_benefits_content() {
	return array(
		'hero'    => array( // AIG homepage-hero mini-benefits (dark-blurb row)
			array( 'Accredited courses', 'Retraining accredited by the ministry — funding may cover up to 100%.', 'Browse courses' ),
			array( 'For companies', 'Custom workshops and team training built on real production use-cases.', 'Get a quote' ),
			array( 'Community', 'Meetups, newsletter and a growing network of practitioners.', 'Join us' ),
		),
		'infobar' => array( // info-bar statements (no titles)
			'Courses built by practitioners who ship AI products, not by career lecturers.',
			'Small cohorts — every participant leaves with a working project.',
			'Up to 100% funding through the labour office retraining scheme.',
		),
		'certs'   => array( // the editorial numbered benefits (homepage / course)
			array( 'Practical skill, not theory', 'You leave with a working AI workflow you built yourself, on your own use-case.' ),
			array( 'A certificate that counts', 'Ministry-accredited retraining certificate recognized by employers.' ),
			array( 'Lifetime community access', 'Alumni channel, monthly meetups, and first access to new courses.' ),
		),
		'lpwhat'  => array( // the "Six areas" light cells
			array( 'Signals', 'What moves the market', 'Reviews of new models and market shifts for product teams — design, dev, management, founders.' ),
			array( 'Development', 'Code, models, frameworks', 'Model and library releases, frameworks, technical deep-dives. What is worth an experiment.' ),
			array( 'Design', 'AI in the UI/UX workflow', 'Which tools deserve a week of experimenting, which are another Figma plugin.' ),
			array( 'Science', 'Applied research', 'From Nature, arXiv and preprints only what reaches products within 12 months.' ),
			array( 'AI in Europe', 'What opens in Europe', 'Horizon Europe grants with a month left. German mid-market demanding EU-based AI vendors.' ),
			array( 'AI in Czechia', 'Who hires, who pays', 'AI meetups in Prague and Brno. Seed-funded startups hunting first clients.' ),
		),
		'footer'  => array( // footer blurbs (eyebrow-as-title + text)
			array( 'About the guild', 'Practical AI education for the Czech market — courses, workshops and a newsletter read by 4 000+ professionals.' ),
			array( 'Contact', 'hello@example.com · Prague & Brno · weekdays 9–17.' ),
		),
	);
}

/* Section wrappers shared by all three proposals: replica surfaces */
function aigds_sb_section_open( $label, $classes = '', $style = '' ) {
	echo '<div class="sb-section-label">' . esc_html( $label ) . '</div>';
	echo '<section class="' . esc_attr( $classes ) . '" style="padding:56px 24px;' . esc_attr( $style ) . '"><div style="max-width:1200px;margin:0 auto;">';
}
function aigds_sb_section_close() {
	echo '</div></section>';
}

/* ═══════════════════════════════════════════════════════════════════════
   PROPOSAL A — ONE PRIMITIVE `.benefit` (aggressive unification)
   Density axis: --display / (default compact) / --statement
   Accent axis:  --rule-top / --rule-left · numbering via .benefit-row--numbered
   Voices: TWO title voices (display clamp · heading-xs) + TWO bodies
   (body-lg · caption). The SG/Inter body split DIES.
   ═══════════════════════════════════════════════════════════════════════ */

function aigds_sandbox_benefits_a() {
	$c = aigds_sb_benefits_content();
	?>
	<style>
	/* EXPERIMENT CSS — proposal A (not canon) */
	.benefit-row { display: flex; gap: var(--spacing-32); align-items: stretch; flex-wrap: wrap; }
	.benefit-row > .benefit { flex: 1 1 0; min-width: 240px; }
	.benefit-row--numbered { counter-reset: benefit; }
	.benefit { display: flex; flex-direction: column; gap: var(--spacing-12); }
	.benefit__eyebrow { font-family: var(--font-mono); font-size: var(--meta-size); font-weight: var(--weight-bold); line-height: var(--leading-none); text-transform: var(--case-upper); letter-spacing: var(--tracking-label); color: var(--text-tertiary); margin: 0; }
	.benefit-row--numbered .benefit { counter-increment: benefit; }
	.benefit-row--numbered .benefit__eyebrow:empty::before { content: counter(benefit, decimal-leading-zero); }
	.benefit__icon { width: 64px; height: 64px; }
	.benefit__title { font-family: var(--heading-xs-font); font-size: var(--heading-xs-size); font-weight: var(--heading-xs-weight); line-height: var(--heading-xs-leading); color: var(--text); margin: 0; }
	.benefit--display .benefit__title { font-size: clamp(24px, 2.4vw, 32px); line-height: var(--leading-snug); letter-spacing: var(--tracking-display); }
	.benefit__body { font-family: var(--body-md-font); font-size: var(--caption-size); line-height: var(--leading-body); color: var(--text-secondary); margin: 0; }
	.benefit--display .benefit__body { font-size: var(--body-lg-size); line-height: var(--leading-body); max-width: 38ch; }
	.benefit__action { margin-top: auto; align-self: flex-start; }
	.benefit--rule-top { border-top: var(--stroke-2) solid var(--border-strong); padding-top: var(--spacing-24); }
	.benefit--rule-left { border-left: var(--stroke-2) solid var(--brand); padding-left: var(--spacing-24); }
	.benefit--statement .benefit__body { font-size: var(--body-md-size); color: var(--text); }
	</style>

	<?php aigds_sb_section_open( 'A/1 · homepage hero mini-benefits — dark blurb row → .benefit (compact, icon, action)', 'section-dark' ); ?>
	<div class="benefit-row">
		<?php foreach ( $c['hero'] as $b ) : ?>
		<div class="benefit">
			<div class="benefit__icon"><span class="illustration-placeholder" style="display:block;width:64px;height:64px;background:var(--raised);"></span></div>
			<h3 class="benefit__title"><?php echo esc_html( $b[0] ); ?></h3>
			<p class="benefit__body"><?php echo esc_html( $b[1] ); ?></p>
			<a href="#" class="btn btn--sm btn--primary benefit__action"><?php echo esc_html( $b[2] ); ?></a>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'A/2 · info-bar statements → .benefit--statement --rule-left (no title)', 'content-section--dark-secondary section-dark' ); ?>
	<div class="benefit-row">
		<?php foreach ( $c['infobar'] as $t ) : ?>
		<div class="benefit benefit--statement benefit--rule-left"><p class="benefit__body"><?php echo esc_html( $t ); ?></p></div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'A/3 · editorial numbered benefits (homepage + course) → .benefit--display --rule-top, auto-numbered', 'section-dark' ); ?>
	<div class="benefit-row benefit-row--numbered">
		<?php foreach ( $c['certs'] as $b ) : ?>
		<div class="benefit benefit--display benefit--rule-top">
			<p class="benefit__eyebrow"></p>
			<h3 class="benefit__title"><?php echo esc_html( $b[0] ); ?></h3>
			<p class="benefit__body"><?php echo esc_html( $b[1] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'A/4 · "Six areas" light cells → .benefit--display with LABEL eyebrows (same component, light surface)' ); ?>
	<div class="benefit-row" style="display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:56px 64px; border-top: var(--stroke-2) solid var(--border-strong); padding-top:56px;">
		<?php foreach ( $c['lpwhat'] as $b ) : ?>
		<div class="benefit benefit--display">
			<p class="benefit__eyebrow"><?php echo esc_html( $b[0] ); ?></p>
			<h3 class="benefit__title" style="font-size:24px;"><?php echo esc_html( $b[1] ); ?></h3>
			<p class="benefit__body" style="font-size:var(--body-md-size);"><?php echo esc_html( $b[2] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'A/5 · footer blurbs → .benefit with eyebrow-as-title (no title slot)', 'section-dark' ); ?>
	<div class="benefit-row" style="max-width:800px;">
		<?php foreach ( $c['footer'] as $b ) : ?>
		<div class="benefit">
			<p class="benefit__eyebrow"><?php echo esc_html( $b[0] ); ?></p>
			<p class="benefit__body"><?php echo esc_html( $b[1] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════════
   PROPOSAL B — TWO ROWS: `.benefit-card` (loud editorial) + `.blurb`
   (quiet utility). The two real JOBS: SELLING vs ORIENTING.
   ═══════════════════════════════════════════════════════════════════════ */

function aigds_sandbox_benefits_b() {
	$c = aigds_sb_benefits_content();
	?>
	<style>
	/* EXPERIMENT CSS — proposal B (not canon) */
	/* the shared eyebrow atom */
	.eyebrow { font-family: var(--font-mono); font-size: var(--meta-size); font-weight: var(--weight-bold); line-height: var(--leading-none); text-transform: var(--case-upper); letter-spacing: var(--tracking-label); margin: 0; }
	/* BENEFIT-CARD — the loud editorial (cert / lp-what / nl lineage) */
	.bcard-row { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 48px 56px; }
	.bcard-row--numbered { counter-reset: bcard; }
	.benefit-card { display: flex; flex-direction: column; gap: var(--spacing-12); border-top: var(--stroke-2) solid var(--border-strong); padding-top: var(--spacing-24); }
	.benefit-card .eyebrow { color: var(--text-tertiary); }
	.bcard-row--numbered .benefit-card { counter-increment: bcard; }
	.bcard-row--numbered .benefit-card .eyebrow:empty::before { content: counter(bcard, decimal-leading-zero); }
	.benefit-card__title { font-family: var(--font-primary); font-size: clamp(24px, 2.4vw, 32px); font-weight: var(--weight-extrabold); line-height: var(--leading-snug); letter-spacing: var(--tracking-display); color: var(--text); margin: 0; }
	.benefit-card--sm .benefit-card__title { font-size: var(--heading-sm-size); }
	.benefit-card__body { font-family: var(--body-md-font); font-size: var(--body-lg-size); line-height: 1.55; color: var(--text-secondary); margin: 0; max-width: 38ch; }
	.benefit-card--sm .benefit-card__body { font-size: var(--body-md-size); line-height: var(--leading-body); }
	/* BLURB — the quiet utility (dark-blurb / footer / info-bar lineage) */
	.blurb-row { display: flex; gap: var(--spacing-24); align-items: stretch; flex-wrap: wrap; }
	.blurb-row > .blurb { flex: 1 1 0; min-width: 240px; }
	.blurb { display: flex; gap: var(--spacing-16); }
	.blurb__icon { flex: none; width: 64px; height: 64px; }
	.blurb__content { display: flex; flex-direction: column; gap: var(--spacing-12); min-width: 0; flex: 1; }
	.blurb__title { font-family: var(--heading-xs-font); font-size: var(--heading-xs-size); font-weight: var(--heading-xs-weight); line-height: var(--heading-xs-leading); color: var(--text); margin: 0; }
	.blurb__body { font-family: var(--caption-font); font-size: var(--caption-size); line-height: var(--leading-heading); color: var(--text-secondary); margin: 0; }
	.blurb__action { margin-top: auto; align-self: flex-start; }
	.blurb--boxed { background: var(--raised); padding: var(--spacing-24); }
	.blurb--statement { border-left: var(--stroke-2) solid var(--brand); padding-left: var(--spacing-24); }
	.blurb--statement .blurb__body { font-family: var(--description-font); font-size: var(--body-md-size); line-height: var(--leading-body); color: var(--text); }
	.blurb--label-title .eyebrow { color: var(--text-tertiary); line-height: 1.6; }
	</style>

	<?php aigds_sb_section_open( 'B/1 · hero mini-benefits → .blurb--boxed (icon + title + caption + pinned CTA)', 'section-dark' ); ?>
	<div class="blurb-row">
		<?php foreach ( $c['hero'] as $b ) : ?>
		<div class="blurb blurb--boxed">
			<div class="blurb__icon"><span style="display:block;width:64px;height:64px;background:var(--bg);"></span></div>
			<div class="blurb__content">
				<h3 class="blurb__title"><?php echo esc_html( $b[0] ); ?></h3>
				<p class="blurb__body"><?php echo esc_html( $b[1] ); ?></p>
				<a href="#" class="btn btn--sm btn--primary blurb__action"><?php echo esc_html( $b[2] ); ?></a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'B/2 · info-bar → .blurb--statement (description voice keeps the SG statement feel)', 'content-section--dark-secondary section-dark' ); ?>
	<div class="blurb-row">
		<?php foreach ( $c['infobar'] as $t ) : ?>
		<div class="blurb blurb--statement"><div class="blurb__content"><p class="blurb__body"><?php echo esc_html( $t ); ?></p></div></div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'B/3 · editorial numbered benefits → .benefit-card (auto-numbered eyebrow)', 'section-dark' ); ?>
	<div class="bcard-row bcard-row--numbered">
		<?php foreach ( $c['certs'] as $b ) : ?>
		<div class="benefit-card">
			<p class="eyebrow"></p>
			<h3 class="benefit-card__title"><?php echo esc_html( $b[0] ); ?></h3>
			<p class="benefit-card__body"><?php echo esc_html( $b[1] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'B/4 · "Six areas" → .benefit-card--sm with label eyebrows (same card, light + smaller rung)' ); ?>
	<div class="bcard-row" style="border-top: var(--stroke-2) solid var(--border-strong); padding-top:56px;">
		<?php foreach ( $c['lpwhat'] as $b ) : ?>
		<div class="benefit-card benefit-card--sm" style="border-top:0; padding-top:0;">
			<p class="eyebrow" style="color:var(--text-tertiary);"><?php echo esc_html( $b[0] ); ?></p>
			<h3 class="benefit-card__title"><?php echo esc_html( $b[1] ); ?></h3>
			<p class="benefit-card__body"><?php echo esc_html( $b[2] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'B/5 · footer blurbs → .blurb--label-title (the eyebrow IS the title)', 'section-dark' ); ?>
	<div class="blurb-row" style="max-width:800px;">
		<?php foreach ( $c['footer'] as $b ) : ?>
		<div class="blurb blurb--label-title">
			<div class="blurb__content">
				<p class="eyebrow"><?php echo esc_html( $b[0] ); ?></p>
				<p class="blurb__body" style="font-family:var(--body-md-font);"><?php echo esc_html( $b[1] ); ?></p>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════════
   PROPOSAL C — CONSERVATIVE: three components, production-closest looks,
   only the eyebrow atom + tokens unified.
   ═══════════════════════════════════════════════════════════════════════ */

function aigds_sandbox_benefits_c() {
	$c = aigds_sb_benefits_content();
	?>
	<style>
	/* EXPERIMENT CSS — proposal C (not canon) */
	.eyebrow { font-family: var(--font-mono); font-size: var(--meta-size); font-weight: var(--weight-bold); line-height: var(--leading-none); text-transform: var(--case-upper); letter-spacing: var(--tracking-label); margin: 0; }
	/* 1 · benefit-card — the AIG editorial cert, kept */
	.c-row { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 48px 56px; counter-reset: cc; }
	.c-cert { border-top: var(--stroke-2) solid var(--border-strong); padding-top: var(--spacing-32); counter-increment: cc; display:flex; flex-direction:column; gap: var(--spacing-16); }
	.c-cert .eyebrow { color: var(--link); }
	.c-cert .eyebrow:empty::before { content: counter(cc, decimal-leading-zero); }
	.c-cert__title { font-family: var(--font-primary); font-size: clamp(24px, 2.4vw, 32px); font-weight: var(--weight-extrabold); line-height: 1.15; letter-spacing: -0.01em; color: var(--text); margin: 0; }
	.c-cert__text { font-family: var(--body-md-font); font-size: var(--body-lg-size); line-height: 1.55; color: var(--text-secondary); max-width: 38ch; margin: 0; }
	/* 2 · blurb — production dark-blurb, kept (SG caption body, box) */
	.c-blurbs { display: flex; gap: var(--spacing-24); flex-wrap: wrap; }
	.c-blurb { flex: 1 1 0; min-width: 260px; background: var(--raised); padding: var(--spacing-24); display: flex; gap: 22px; }
	.c-blurb__icon { flex:none; width:64px; height:64px; background: var(--bg); }
	.c-blurb__content { display:flex; flex-direction:column; gap: var(--spacing-24); flex:1; min-width:0; }
	.c-blurb__title { font-family: var(--font-primary); font-size: var(--heading-xs-size); font-weight: var(--weight-extrabold); line-height: var(--leading-heading); margin:0; color: var(--text); }
	.c-blurb__desc { font-family: var(--caption-font); font-size: var(--caption-size); line-height: var(--leading-heading); margin:0; color: var(--text-secondary); }
	.c-blurb .btn { margin-top: auto; align-self: flex-start; }
	/* 3 · info-stripe — production info-bar, kept */
	.c-stripe { display:flex; gap: var(--spacing-40); flex-wrap: wrap; }
	.c-stripe__item { flex:1 1 0; min-width: 240px; border-left: var(--stroke-2) solid var(--brand); padding-left: var(--spacing-32); }
	.c-stripe__item p { font-family: var(--description-font); font-size: var(--body-md-size); line-height: var(--leading-body); color: var(--text); margin: 0; }
	/* lp-what kept as a benefit-card size variant */
	.c-cert--sm { border-top: 0; padding-top: 0; }
	.c-cert--sm .c-cert__title { font-size: 24px; line-height: 1.2; }
	.c-cert--sm .c-cert__text { font-size: 15px; line-height: 1.6; max-width: none; }
	.c-cert--sm .eyebrow { color: var(--text-tertiary); }
	/* footer blurb kept */
	.c-footer { display:flex; flex-direction:column; gap: var(--spacing-12); max-width: 380px; }
	.c-footer .eyebrow { color: var(--text-tertiary); line-height: 1.6; }
	.c-footer p { font-family: var(--body-md-font); font-size: var(--caption-size); line-height: var(--leading-heading); color: var(--text); margin: 0; }
	</style>

	<?php aigds_sb_section_open( 'C/1 · hero mini-benefits → .c-blurb (production look, tokens only)', 'section-dark' ); ?>
	<div class="c-blurbs">
		<?php foreach ( $c['hero'] as $b ) : ?>
		<div class="c-blurb">
			<div class="c-blurb__icon"></div>
			<div class="c-blurb__content">
				<div style="display:flex;flex-direction:column;gap:var(--spacing-12);">
					<p class="c-blurb__title"><?php echo esc_html( $b[0] ); ?></p>
					<p class="c-blurb__desc"><?php echo esc_html( $b[1] ); ?></p>
				</div>
				<a href="#" class="btn btn--sm btn--primary"><?php echo esc_html( $b[2] ); ?></a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'C/2 · info-stripe (production info-bar, tokens only)', 'content-section--dark-secondary section-dark' ); ?>
	<div class="c-stripe">
		<?php foreach ( $c['infobar'] as $t ) : ?>
		<div class="c-stripe__item"><p><?php echo esc_html( $t ); ?></p></div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'C/3 · editorial benefit-card (production cert-card, tokens only)', 'section-dark' ); ?>
	<div class="c-row">
		<?php foreach ( $c['certs'] as $b ) : ?>
		<div class="c-cert">
			<p class="eyebrow"></p>
			<h3 class="c-cert__title"><?php echo esc_html( $b[0] ); ?></h3>
			<p class="c-cert__text"><?php echo esc_html( $b[1] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'C/4 · "Six areas" → benefit-card size variant (production lp-what look)' ); ?>
	<div class="c-row" style="border-top: var(--stroke-2) solid var(--border-strong); padding-top:56px; counter-reset:none;">
		<?php foreach ( $c['lpwhat'] as $b ) : ?>
		<div class="c-cert c-cert--sm">
			<p class="eyebrow"><?php echo esc_html( $b[0] ); ?></p>
			<h3 class="c-cert__title"><?php echo esc_html( $b[1] ); ?></h3>
			<p class="c-cert__text"><?php echo esc_html( $b[2] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>

	<?php aigds_sb_section_open( 'C/5 · footer blurbs (production look, tokens only)', 'section-dark' ); ?>
	<div style="display:flex; gap:48px; flex-wrap:wrap;">
		<?php foreach ( $c['footer'] as $b ) : ?>
		<div class="c-footer">
			<p class="eyebrow"><?php echo esc_html( $b[0] ); ?></p>
			<p><?php echo esc_html( $b[1] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php aigds_sb_section_close(); ?>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════════
   INFO BAR — operator verdict 2026-07-06: SEPARATE COMPONENT, upgraded
   toward the BLOCKQUOTE/PEREX grammar (bolder fonts, 4px left border).
   Three intensities side by side; each shown on the production
   dark-secondary stripe AND on a light band (the AIG homepage light-flip
   experiment is still open — judge both here).
   ═══════════════════════════════════════════════════════════════════════ */

function aigds_sandbox_infobar() {
	$statements = aigds_sb_benefits_content()['infobar'];
	?>
	<style>
	/* EXPERIMENT CSS — info-bar intensities (not canon) */
	.ib-row { display: flex; gap: var(--spacing-40); flex-wrap: wrap; }
	.ib-row > * { flex: 1 1 0; min-width: 240px; }

	/* V1 · CONSERVATIVE — production look, tokens only:
	   2px brand rule · SG body-md REGULAR */
	.ib-v1 { border-left: var(--stroke-2) solid var(--brand); padding-left: var(--spacing-32); }
	.ib-v1 p { font-family: var(--description-font); font-size: var(--body-md-size); font-weight: var(--weight-regular); line-height: var(--leading-body); color: var(--text); margin: 0; }

	/* V2 · MIDDLE — the operator's ask literally:
	   4px brand rule · SG body-md BOLD · perex-style inside-indent */
	.ib-v2 { border-left: var(--stroke-4) solid var(--brand); padding-left: calc(var(--spacing-32) - var(--stroke-4)); }
	.ib-v2 p { font-family: var(--description-font); font-size: var(--body-md-size); font-weight: var(--weight-bold); line-height: var(--leading-body); color: var(--text); margin: 0; }

	/* V3 · FULLY TRANSFORMED — the PEREX VOICE verbatim: each statement IS a
	   mini-blockquote (SG lead-size BOLD, lead leading, 4px surface-aware
	   --perex-border rule, flow-indent alignment) */
	.ib-v3 { border-left: var(--stroke-4) solid var(--perex-border); padding-left: calc(var(--flow-indent, 40px) - var(--stroke-4)); }
	.ib-v3 p { font-family: var(--font-accent); font-size: var(--lead-size); font-weight: var(--weight-bold); line-height: var(--lead-leading); color: var(--text); margin: 0; }
	</style>

	<?php foreach ( array(
		array( 'ib-v1', 'V1 · CONSERVATIVE — production, tokens only (2px brand rule, SG 16 regular)' ),
		array( 'ib-v2', 'V2 · MIDDLE — 4px brand rule + BOLD (the literal ask)' ),
		array( 'ib-v3', 'V3 · FULLY TRANSFORMED — the perex voice verbatim (lead-size bold, surface-aware --perex-border, mini-blockquotes)' ),
	) as $v ) : list( $cls, $label ) = $v; ?>

		<?php aigds_sb_section_open( $label . ' — on the production DARK stripe', 'content-section--dark-secondary section-dark' ); ?>
		<div class="ib-row">
			<?php foreach ( $statements as $t ) : ?>
			<div class="<?php echo esc_attr( $cls ); ?>"><p><?php echo esc_html( $t ); ?></p></div>
			<?php endforeach; ?>
		</div>
		<?php aigds_sb_section_close(); ?>

		<?php aigds_sb_section_open( $label . ' — on a LIGHT band (the open AIG homepage experiment)', 'content-section--secondary' ); ?>
		<div class="ib-row">
			<?php foreach ( $statements as $t ) : ?>
			<div class="<?php echo esc_attr( $cls ); ?>"><p><?php echo esc_html( $t ); ?></p></div>
			<?php endforeach; ?>
		</div>
		<?php aigds_sb_section_close(); ?>

	<?php endforeach; ?>
	<?php
}

/* ═══════════════════════════════════════════════════════════════════════
   AIF HOMEPAGE CLONE — hero + info-bar + mission quote + dark blurbs
   (operator 2026-07-06: "we'll work with these and test concepts";
   content below the blurbs deliberately omitted). REAL production content;
   structure harvested from front-page.php + page.css + the live screenshot
   (production blurbs already evolved to eyebrow titles in ONE raised box
   with dividers — the screenshot wins over the stale theme clone).
   Three versions = three family-treatment concepts.
   ═══════════════════════════════════════════════════════════════════════ */

function aigds_sb_aif_home_content() {
	return array(
		'hero_headline' => 'Otevřená iniciativa pro české AI-průkopníky.',
		'hero_highlight' => '#signals',
		'hero_suffix' => '— denně',
		'hero_sub' => 'Praktické novinky, nástroje a zdroje pro foundery, designéry a vývojáře.',
		'infobar' => array(
			'Denně přinášíme aktuální informace ze světa umělé inteligence',
			'Poskytujeme otevřené zdroje pro odborníky a podnikatele',
			'Budujeme prostředí pro vznik českých AI produktů',
		),
		'quote' => 'Pomáháme těm, kdo staví produkty, odlišit signál od šumu, vybrat směr a převést ho do použitelného workflow.',
		'blurbs' => array(
			array( 'Partnerství a komunita', 'Otevřená komunita autorů, kteří AI produkty reálně staví — ne jen sledují. Pište pod naší hlavičkou: releasy, postřehy z terénu, návody pro foundery, designéry a vývojáře.', 'Naše mise' ),
			array( 'Reálné projekty', 'AI Founders píší lidé, kteří AI produkty sami staví. Když potřebujete víc než článek — agentic design system, AI native workflow, adopce AI v teamu — propojíme vás s AI Guild Blue.', 'Spojit se s týmem' ),
			array( 'Vzdělávání', 'Když je potřeba jít do hloubky, posíláme vás do AI Guild: české státem podporované programy postavené na moderních AI workflows. AI Founders zůstává otevřená iniciativa — AI Guild je řemeslo krok za krokem.', 'Prozkoumat kurzy' ),
		),
	);
}

/* shared shell CSS: hero + section scaffolding (identical in all versions) */
function aigds_sb_aif_home_shell_css() {
	?>
	<style>
	/* EXPERIMENT CSS — AIF homepage clone shell (not canon) */
	.hm-hero { background: var(--brand); padding: var(--spacing-120) 0 var(--spacing-80); }
	.hm-wrap { max-width: var(--container-max); margin: 0 auto; padding: 0 var(--spacing-24); }
	.hm-hero h1 { font-family: var(--heading-xl-font, var(--font-display)); font-size: var(--size-display); font-weight: var(--weight-black); line-height: var(--leading-display); letter-spacing: -0.015em; color: var(--text-on-brand); margin: 0; max-width: 1100px; }
	.hm-hero .hm-highlight { background: var(--white); padding: 0 0.12em; } /* production white chip; --bg resolves to brand inside section-brand */
	.hm-hero__sub { font-family: var(--font-accent); font-size: var(--lead-size); font-weight: var(--weight-bold); line-height: 1.25; color: var(--black); max-width: 620px; margin: var(--spacing-32) 0 0; }
	.hm-hero__form { max-width: 560px; margin-top: var(--spacing-32); }
	.hm-infobar { background: var(--bg); padding: var(--spacing-40) 0; }
	.hm-infobar .hm-wrap { display: flex; gap: var(--spacing-40); flex-wrap: wrap; }
	.hm-infobar .hm-wrap > * { flex: 1 1 0; min-width: 240px; }
	.hm-dark { background: var(--bg); padding: var(--spacing-120) 0; }
	.hm-dark .hm-wrap { display: flex; flex-direction: column; align-items: center; gap: var(--spacing-120); }
	.hm-quote { border-left: var(--stroke-4) solid var(--perex-border); padding-left: var(--spacing-32); max-width: var(--container-narrow); width: 100%; }
	.hm-quote p { font-family: var(--font-accent); font-size: var(--lead-size); font-weight: var(--weight-bold); line-height: 1.25; color: var(--text); margin: 0; }
	/* blurb box (the screenshot state: ONE raised box, inner dividers) */
	.hm-blurbs { background: var(--raised); display: flex; width: 100%; }
	.hm-blurb { flex: 1 1 0; min-width: 0; padding: var(--spacing-32); display: flex; flex-direction: column; gap: var(--spacing-16); }
	.hm-blurb + .hm-blurb { border-left: var(--stroke-1) solid var(--border); }
	.hm-blurb .btn { margin-top: auto; align-self: flex-start; }
	@media (max-width: 767px) {
		.hm-blurbs { flex-direction: column; }
		.hm-blurb + .hm-blurb { border-left: 0; border-top: var(--stroke-1) solid var(--border); }
	}
	.eyebrow { font-family: var(--font-mono); font-size: var(--meta-size); font-weight: var(--weight-bold); line-height: var(--leading-none); text-transform: var(--case-upper); letter-spacing: var(--tracking-label); margin: 0; color: var(--text-tertiary); }
	</style>
	<?php
}

function aigds_sb_aif_home_render( $v ) {
	$c = aigds_sb_aif_home_content();
	aigds_sb_aif_home_shell_css();
	?>
	<style>
	/* version-specific treatments */
	<?php if ( 1 === $v ) : ?>
	/* V1 REFERENCE — production blurb box (eyebrow titles) */
	.hm-blurb__body { font-family: var(--body-md-font); font-size: var(--body-md-size); line-height: var(--leading-body); color: var(--text-secondary); margin: 0; }
	<?php elseif ( 2 === $v ) : ?>
	/* V2 THE WORKING CONCEPT — now composed from THE CANON (.stack-grid
	   --divided + .blurb, shipped 2026-07-06). Only the consumer wrapper
	   remains experiment CSS: crust padding + raised fill + the bleed
	   recipe (production geometry: -80px, z-index 1). */
	.hm-dark { padding-bottom: 0; }
	.hm-box { background: var(--raised); padding: var(--spacing-24); position: relative; z-index: 1; margin-bottom: calc(-1 * var(--spacing-80)); width: 100%; box-sizing: border-box; }
	.hm-next { padding-top: calc(var(--spacing-80) + var(--spacing-120)); } /* compensates the bleed (production pattern) */
	<?php else : ?>
	/* V3 TRANSFORMED — the blurbs join the blockquote family (4px
	   surface-aware rules instead of the box) */
	.hm-blurbs { background: transparent; gap: var(--spacing-40); }
	.hm-blurb { padding: 0 0 0 var(--spacing-32); border-left: var(--stroke-4) solid var(--perex-border); }
	.hm-blurb + .hm-blurb { border-left: var(--stroke-4) solid var(--perex-border); border-top: 0; }
	.hm-blurb__body { font-family: var(--body-md-font); font-size: var(--body-md-size); line-height: var(--leading-body); color: var(--text-secondary); margin: 0; }
	@media (max-width: 767px) { .hm-blurb + .hm-blurb { border-left: var(--stroke-4) solid var(--perex-border); border-top: 0; } }
	<?php endif; ?>
	</style>

	<?php /* HERO — blue band, display H1 + highlight + capture form */ ?>
	<section class="hm-hero section-brand">
		<div class="hm-wrap">
			<h1><?php echo esc_html( $c['hero_headline'] ); ?> <span class="hm-highlight"><?php echo esc_html( $c['hero_highlight'] ); ?></span> <?php echo esc_html( $c['hero_suffix'] ); ?></h1>
			<p class="hm-hero__sub"><?php echo esc_html( $c['hero_sub'] ); ?></p>
			<div class="hm-hero__form">
				<div class="input-pair">
					<div class="form-control-wrapper"><input type="email" class="form-control" placeholder="Váš e-mail"></div>
					<button type="button" class="btn btn--lg btn--primary">Odebírat novinky</button>
				</div>
			</div>
		</div>
	</section>

	<?php /* INFO BAR — THE SHIPPED CANON (.info-bar, V2 verdict) on a dark host */ ?>
	<section class="section-dark">
		<div class="info-bar"><div class="info-bar__wrapper">
			<?php foreach ( $c['infobar'] as $t ) : ?>
			<div class="info-bar__item"><p><?php echo esc_html( $t ); ?></p></div>
			<?php endforeach; ?>
		</div></div>
	</section>

	<?php /* DARK SECTION — mission quote + blurbs */ ?>
	<section class="hm-dark section-dark">
		<div class="hm-wrap">
			<div class="hm-quote"><p><?php echo esc_html( $c['quote'] ); ?></p></div>
			<?php if ( 2 === $v ) : ?>
			<div class="hm-box">
				<div class="stack-grid stack-grid--divided" style="--stack-fill: var(--raised);">
					<?php foreach ( $c['blurbs'] as $b ) : ?>
					<div class="blurb">
						<h3 class="blurb__headline"><?php echo esc_html( ucfirst( $b[0] ) ); ?></h3>
						<p class="blurb__text blurb__text--sm"><?php echo esc_html( $b[1] ); ?></p>
						<div class="blurb__action"><a href="#" class="btn btn--sm btn--secondary"><?php echo esc_html( $b[2] ); ?></a></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php else : ?>
			<div class="hm-blurbs">
				<?php foreach ( $c['blurbs'] as $b ) : ?>
				<div class="hm-blurb">
					<p class="eyebrow"><?php echo esc_html( $b[0] ); ?></p>
					<p class="hm-blurb__body"><?php echo esc_html( $b[1] ); ?></p>
					<a href="#" class="btn btn--sm btn--secondary"><?php echo esc_html( $b[2] ); ?></a>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* the NEXT (light) section — a stub so the bleed is visible crossing the boundary */ ?>
	<section class="hm-next" style="background: var(--bg); padding: var(--spacing-120) 0;">
		<div class="hm-wrap"><p class="meta" style="color: var(--text-tertiary);">next section (light) — content omitted; on v2 the benefits box BLEEDS over this boundary (production pattern)</p></div>
	</section>
	<?php
}

function aigds_sandbox_aif_home_v1() { aigds_sb_aif_home_render( 1 ); }
function aigds_sandbox_aif_home_v2() { aigds_sb_aif_home_render( 2 ); }
function aigds_sandbox_aif_home_v3() { aigds_sb_aif_home_render( 3 ); }

