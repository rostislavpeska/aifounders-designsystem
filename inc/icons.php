<?php
/**
 * SVG Icons — unified design-system helper (Batch 1, 2026-07-02)
 *
 * UNION of both themes' inc/icons.php: AIF file (27 icons) + 9 AIG-only
 * icons (smart-button, check-bold, pin, skills, linkedin, x, instagram,
 * bluesky, web) = 36 icons. DO NOT INVENT NEW ICONS.
 *
 * Batch 2 (2026-07-03, operator-approved import): +15 Lucide icons
 * (lucide-static v0.563.0, icons/lucide/). Tier 1 — gaps both live sites
 * papered over with inline SVGs: menu, chevron-left, chevron-right, copy.
 * Tier 2 — future-useful: chevron-up, clock, users, external-link, mail,
 * briefcase, circle-alert, info, play, plus, minus. Geometry swaps (slugs
 * kept stable): course -> Lucide graduation-cap, skills -> Lucide hammer.
 *
 * Brand color: arrow defaults resolve via var(--brand) —
 * per-site (yellow/blue) through [data-theme]; no hardcoded brand fallback.
 * Themes alias at adoption: aif_icon()/aiguild_icon() → aigds_icon().
 *
 * Note on colors:
 * - Most icons use currentColor to inherit text color from parent.
 * - Some icons (like 'course') use explicit values where the .section-dark
 *   wildcard cascade would break button-internal colors.
 * - Use 'color' argument to override the stroke color when needed.
 *
 * @package AIG_Design_System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns SVG icon markup.
 *
 * @param string $icon Icon name.
 * @param array  $args Optional. Arguments for the icon.
 *                     - 'class' (string) Additional CSS class.
 *                     - 'size'  (int)    Icon size in px. Default 20.
 *                     - 'color' (string) Stroke color. Default depends on icon.
 * @return string SVG markup.
 */
function aigds_icon( $icon, $args = array() ) {

	$defaults = array(
		'class' => '',
		'size'  => 20,
		'color' => null, // null = use icon default
	);

	$args = wp_parse_args( $args, $defaults );

	$class = 'icon';
	if ( ! empty( $args['class'] ) ) {
		$class .= ' ' . esc_attr( $args['class'] );
	}

	$size = absint( $args['size'] );

	/*
	 * COLOR LAW (operator, 2026-07-02): icons are STRICTLY color-agnostic.
	 * Every icon defaults to currentColor and recolors from CSS context.
	 * The old per-icon brand-color defaults (arrows = brand, course = text)
	 * were removed — contexts that need a specific color set it via CSS or
	 * pass the 'color' arg explicitly. Only 'colored'-type icons (the
	 * smart-button character) carry baked/token accent fills.
	 */
	$color  = ( null === $args['color'] ) ? 'currentColor' : $args['color'];
	$stroke = esc_attr( $color );

	/*
	 * ICON TAXONOMY (operator, 2026-07-02):
	 *  - outline: stroke-based line icons (default) — stepped stroke applies
	 *  - shape:   solid fill icons (socials, filled lightbulb) — no stroke
	 *  - colored: multi-color art with accent fills (smart-button) — untouched,
	 *             SIZE-LOCKED: doesn't scale, needs per-size art variants
	 *             (REPOSITORY_RULES.md §6.2)
	 */
	$icon_types = array(
		'linkedin'         => 'shape',
		'x'                => 'shape',
		'instagram'        => 'shape',
		'facebook'         => 'shape', // registered 2026-07-06 (footer socials — live on both sites)
		'bluesky'          => 'shape',
		'lightbulb-filled' => 'shape',
		'quote-brackets'   => 'shape', // testimonial quote mark (harvested art, registered 2026-07-04)
		'smart-button'     => 'colored',
		'check-bold'       => 'outline-fixed', // deliberately thick — exempt from stepping
	);
	$type = isset( $icon_types[ $icon ] ) ? $icon_types[ $icon ] : 'outline';

	/*
	 * STROKE (operator 2026-07-04, supersedes constant-1.5): STEPPED stroke.
	 * vector-effect:non-scaling-stroke keeps the width in SCREEN px (no
	 * viewBox math); the step follows the rendered size:
	 *   < 16px → --stroke-1 (fine) · 16–32px → --stroke-1_5 (default) ·
	 *   > 32px → --stroke-3 (heavy).
	 * (Operator boundary ruling: 16px itself is 1.5 — only below 16 is fine.)
	 * The classes hook the CSS rules in components.css; shape/colored icons
	 * stay off them; check-bold keeps its deliberate bold.
	 */
	if ( 'outline' === $type ) {
		$class .= ' icon--stroked';
		if ( $size < 16 ) {
			$class .= ' icon--stroked-fine';
		} elseif ( $size > 32 ) {
			$class .= ' icon--stroked-heavy';
		}
	} elseif ( 'outline-fixed' === $type ) {
		$class .= ' icon--stroked icon--stroked-bold';
	}


	$icons = array(

		// Course / Education icon (graduation cap)
		// Batch 2 geometry swap: true Lucide graduation-cap, 24x24 — replaces
		// the legacy 20-grid harvest with cropped viewBox (1 3.5 18 13) that
		// fought the stepped-stroke math. Slug stays 'course' (themes + ACF).
		'course' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>',

		// Arrow right (from design system: arrow_right.svg, scaled from 24x24 to 20x20)
		'arrow-right' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 6.66667L18.3333 10M18.3333 10L15 13.3333M18.3333 10H1.66667" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Calendar
		'calendar' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.3333 1.66669V5.00002M6.66667 1.66669V5.00002M2.5 8.33335H17.5M4.16667 3.33335H15.8333C16.7538 3.33335 17.5 4.07955 17.5 5.00002V16.6667C17.5 17.5872 16.7538 18.3334 15.8333 18.3334H4.16667C3.24619 18.3334 2.5 17.5872 2.5 16.6667V5.00002C2.5 4.07955 3.24619 3.33335 4.16667 3.33335Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Chat / Message
		'chat' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.5 9.58335C17.5029 10.6832 17.2459 11.7683 16.75 12.75C16.162 13.9265 15.2581 14.916 14.1395 15.6078C13.021 16.2995 11.7319 16.6662 10.4167 16.6667C9.31678 16.6696 8.23176 16.4126 7.25 15.9167L2.5 17.5L4.08333 12.75C3.58744 11.7683 3.33047 10.6832 3.33333 9.58335C3.33384 8.26815 3.70051 6.97907 4.39227 5.86048C5.08402 4.7419 6.07355 3.838 7.25 3.25002C8.23176 2.75413 9.31678 2.49716 10.4167 2.50002H10.8333C12.5703 2.59585 14.2109 3.32899 15.441 4.55907C16.671 5.78915 17.4042 7.42973 17.5 9.16669V9.58335Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Check / Checkmark
		'check' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 5L7.50001 14.1667L3.33334 10" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Circle Check Big (Lucide circle-check-big) — success state
		// Figma: AI Guild file, node 2955:642
		// 24x24 viewBox — stroke-width 1.8 compensates for scale at larger sizes
		'circle-check-big' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 11 3 3L22 4" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Mail Check (Lucide mail-check) — confirmation/email-sent state, e.g. "check your email"
		// Figma: AI Guild file, node 2957:644
		// 24x24 viewBox — stroke-width 1.8 compensates for scale at larger sizes
		'mail-check' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h8" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m16 19 2 2 4-4" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Arrow down
		'arrow-down' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.66675 15L10.0001 18.3334M10.0001 18.3334L13.3334 15M10.0001 18.3334V1.66669" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Download (arrow into tray — Figma 2953-2689)
		'download' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Close / X icon (for modals, panels)
		'close' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 5L5 15M5 5L15 15" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Edit / pencil icon
		'edit' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.1667 2.5L17.5 5.83333L6.66667 16.6667H3.33333V13.3333L14.1667 2.5Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Signal icon (arrow up-right) - for Signal badges
		'signal' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.83301 5.83325H14.1663M14.1663 5.83325V14.1666M14.1663 5.83325L5.83301 14.1666" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Editorial icon (star) - for Editorial badges
		'editorial' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.60404 1.91249C9.64056 1.83871 9.69697 1.7766 9.76692 1.73318C9.83686 1.68976 9.91755 1.66675 9.99987 1.66675C10.0822 1.66675 10.1629 1.68976 10.2328 1.73318C10.3028 1.7766 10.3592 1.83871 10.3957 1.91249L12.3207 5.81166C12.4475 6.0683 12.6347 6.29033 12.8662 6.4587C13.0977 6.62707 13.3666 6.73675 13.6499 6.77833L17.9549 7.40833C18.0364 7.42014 18.1131 7.45455 18.1761 7.50766C18.2391 7.56076 18.2861 7.63045 18.3116 7.70883C18.337 7.78721 18.3401 7.87116 18.3204 7.95119C18.3006 8.03121 18.2589 8.10412 18.1999 8.16166L15.0865 11.1933C14.8812 11.3934 14.7276 11.6404 14.6389 11.913C14.5502 12.1856 14.5291 12.4757 14.5774 12.7583L15.3124 17.0417C15.3268 17.1232 15.318 17.2071 15.287 17.2839C15.2559 17.3607 15.204 17.4272 15.137 17.4758C15.07 17.5245 14.9907 17.5533 14.9081 17.5591C14.8255 17.5648 14.743 17.5472 14.6699 17.5083L10.8215 15.485C10.568 15.3518 10.2859 15.2823 9.99946 15.2823C9.71306 15.2823 9.43094 15.3518 9.17737 15.485L5.32987 17.5083C5.25681 17.547 5.17437 17.5644 5.09191 17.5585C5.00946 17.5527 4.9303 17.5238 4.86345 17.4752C4.7966 17.4266 4.74473 17.3601 4.71375 17.2835C4.68277 17.2069 4.67392 17.1231 4.68821 17.0417L5.42237 12.7592C5.47087 12.4764 5.44986 12.1862 5.36115 11.9134C5.27245 11.6406 5.11871 11.3935 4.91321 11.1933L1.79987 8.16249C1.74037 8.10502 1.6982 8.03199 1.67817 7.95172C1.65815 7.87145 1.66107 7.78717 1.6866 7.70848C1.71214 7.6298 1.75926 7.55986 1.8226 7.50665C1.88594 7.45343 1.96296 7.41907 2.04487 7.40749L6.34904 6.77833C6.63259 6.73708 6.90186 6.62754 7.13369 6.45915C7.36552 6.29076 7.55296 6.06855 7.67987 5.81166L9.60404 1.91249Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Map pin icon (location marker) - for Event location badges
		'map-pin' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6663 8.33341C16.6663 12.4942 12.0505 16.8276 10.5005 18.1659C10.3561 18.2745 10.1803 18.3332 9.99967 18.3332C9.81901 18.3332 9.64324 18.2745 9.49884 18.1659C7.94884 16.8276 3.33301 12.4942 3.33301 8.33341C3.33301 6.5653 4.03539 4.86961 5.28563 3.61937C6.53587 2.36913 8.23156 1.66675 9.99967 1.66675C11.7678 1.66675 13.4635 2.36913 14.7137 3.61937C15.964 4.86961 16.6663 6.5653 16.6663 8.33341Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.99967 10.8334C11.3804 10.8334 12.4997 9.71413 12.4997 8.33341C12.4997 6.9527 11.3804 5.83341 9.99967 5.83341C8.61896 5.83341 7.49967 6.9527 7.49967 8.33341C7.49967 9.71413 8.61896 10.8334 9.99967 10.8334Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Calendar check icon - for Weekly summary badges
		'calendar-check' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.66667 1.66669V5.00002M13.3333 1.66669V5.00002M17.5 11.6667V5.00002C17.5 4.55799 17.3244 4.13407 17.0118 3.82151C16.6993 3.50895 16.2754 3.33335 15.8333 3.33335H4.16667C3.72464 3.33335 3.30072 3.50895 2.98816 3.82151C2.67559 4.13407 2.5 4.55799 2.5 5.00002V16.6667C2.5 17.1087 2.67559 17.5326 2.98816 17.8452C3.30072 18.1578 3.72464 18.3334 4.16667 18.3334H10.8333M2.5 8.33335H17.5M13.3333 16.6667L15 18.3334L18.3333 15" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// External link / Source icon (chain link) - for Signal source buttons
		'source' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 9.74997C7.82209 10.1806 8.23302 10.5369 8.70491 10.7947C9.17681 11.0525 9.69863 11.2058 10.235 11.2442C10.7713 11.2826 11.3097 11.2052 11.8135 11.0173C12.3173 10.8294 12.7748 10.5353 13.155 10.155L15.405 7.90497C16.0881 7.19772 16.4661 6.25046 16.4575 5.26722C16.449 4.28398 16.0546 3.34343 15.3593 2.64815C14.664 1.95287 13.7235 1.55849 12.7403 1.54995C11.757 1.5414 10.8098 1.91938 10.1025 2.60247L8.8125 3.88497M10.5 8.24999C10.1779 7.81939 9.76695 7.4631 9.29505 7.20528C8.82316 6.94746 8.30134 6.79415 7.76498 6.75574C7.22862 6.71732 6.69028 6.79471 6.18646 6.98265C5.68264 7.17059 5.22513 7.46468 4.84497 7.84499L2.59497 10.095C1.91187 10.8022 1.5339 11.7495 1.54244 12.7327C1.55098 13.716 1.94537 14.6565 2.64065 15.3518C3.33593 16.0471 4.27647 16.4415 5.25971 16.45C6.24295 16.4586 7.19021 16.0806 7.89747 15.3975L9.17997 14.115" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// RSS feed icon (24x24 viewBox — stroke-width 1.8 compensates for scale)
		'rss' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 11C6.38695 11 8.67613 11.9482 10.364 13.636C12.0518 15.3239 13 17.6131 13 20M4 4C8.24346 4 12.3131 5.68571 15.3137 8.68629C18.3143 11.6869 20 15.7565 20 20M6 19C6 19.5523 5.55228 20 5 20C4.44772 20 4 19.5523 4 19C4 18.4477 4.44772 18 5 18C5.55228 18 6 18.4477 6 19Z" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// User / Profile icon (rounded, 24x24 viewBox — stroke-width 1.8 compensates for scale)
		'user-round' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 13C14.7614 13 17 10.7614 17 8C17 5.23858 14.7614 3 12 3C9.23858 3 7 5.23858 7 8C7 10.7614 9.23858 13 12 13ZM12 13C14.1217 13 16.1566 13.8429 17.6569 15.3431C19.1571 16.8434 20 18.8783 20 21M12 13C9.87827 13 7.84344 13.8429 6.34315 15.3431C4.84285 16.8434 4 18.8783 4 21" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Lightbulb (outline) — Aha! engagement button, default/not-clicked state.
		// 1:1 from AI Guild banners/bulb_grey.svg. 20x20 viewBox.
		// Stroke uses currentColor so it inherits the parent .aif-aha text color (gray default).
		'lightbulb' => '<svg class="' . $class . ' icon-lightbulb" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M10.7108 17.994V17.2656H9.28102M9.28102 17.2656V17.994M9.28102 17.2656H8.92358C8.80053 17.2656 8.6892 17.2418 8.57786 17.2M8.58373 17.2059C8.16184 17.0567 7.85715 16.6567 7.85715 16.179M8.58373 17.2059C8.16184 17.0627 7.85715 16.6567 7.85715 16.179M8.58373 17.2059C8.69506 17.2418 8.80639 17.2716 8.92944 17.2716H11.0682M7.85715 16.179V13.976C7.87472 12.7759 7.40595 11.6177 6.56803 10.7818C5.4547 9.7131 4.88046 8.17872 5.02695 6.62641C5.04453 6.41148 5.08555 6.20251 5.12657 5.99951M7.85715 16.179V14.7223H12.1405V16.179C12.1405 16.6567 11.8358 17.0567 11.4139 17.2059M14.8769 5.99951C14.9531 6.35177 14.9941 6.71597 15 7.0921C15 8.50112 14.4258 9.84446 13.4179 10.8057C12.5917 11.6177 12.1288 12.7461 12.1464 13.9163V16.179C12.1464 16.6567 11.8417 17.0627 11.4198 17.2059M11.4139 17.2059C11.3026 17.2418 11.1912 17.2716 11.0682 17.2716M11.4139 17.2059C11.3026 17.2477 11.1912 17.2716 11.0682 17.2716M11.0682 17.2716H10.7108V18M14.8711 5.99355C14.3965 3.79048 12.5214 2.11278 10.2244 2.00532C7.76924 1.89188 5.63635 3.6054 5.1207 5.99355C5.07383 6.19655 5.03866 6.4055 5.02108 6.62044C4.88045 8.17274 5.44884 9.70712 6.56216 10.7758C7.40594 11.6176 7.86886 12.7699 7.85128 13.97V14.7163H12.1346V13.9043C12.1171 12.7341 12.58 11.6057 13.4062 10.7937C14.4199 9.8325 14.9941 8.48916 14.9883 7.08015C14.9883 6.70401 14.9414 6.33983 14.8652 5.98758L14.8711 5.99355Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Trash (Lucide trash-2 style) — for self-delete on own comment (Figma 2901:8613).
		// Stroke uses currentColor so parent text color drives the icon shade.
		'trash-2' => '<svg class="' . $class . ' icon-trash-2" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Lightbulb (filled) — Aha! engagement button, clicked/active state.
		// 1:1 from AI Guild banners/bulb_color.svg. Yellow #F5C400 fill + dark #05070A stroke.
		// Brand colors are baked in (do NOT inherit currentColor — bulb is yellow always).
		'lightbulb-filled' => '<svg class="' . $class . ' icon-lightbulb-filled" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M10.7108 17.994V17.2656H9.28102V17.994" fill="#F5C400"/><path d="M9.28102 17.994V17.2656H8.92358C8.80053 17.2656 8.6892 17.2418 8.57786 17.2" fill="#F5C400"/><path d="M11.4139 17.2059C11.3026 17.2418 11.1912 17.2716 11.0682 17.2716H10.7108V18" fill="#F5C400"/><path d="M14.8711 5.99355C14.3965 3.79048 12.5214 2.11278 10.2244 2.00532C7.76924 1.89188 5.63635 3.6054 5.1207 5.99355C5.07383 6.19655 5.03866 6.4055 5.02108 6.62044C4.88045 8.17274 5.44884 9.70712 6.56216 10.7758C7.40594 11.6176 7.86886 12.7699 7.85128 13.97V14.7163H12.1346V13.9043C12.1171 12.7341 12.58 11.6057 13.4062 10.7937C14.4199 9.8325 14.9941 8.48916 14.9883 7.08015C14.9883 6.70401 14.9414 6.33983 14.8652 5.98758L14.8711 5.99355Z" fill="#F5C400"/><path d="M10.7108 17.994V17.2656H9.28102M9.28102 17.2656V17.994M9.28102 17.2656H8.92358C8.80053 17.2656 8.6892 17.2418 8.57786 17.2M8.58373 17.2059C8.16184 17.0567 7.85715 16.6567 7.85715 16.179M8.58373 17.2059C8.16184 17.0627 7.85715 16.6567 7.85715 16.179M8.58373 17.2059C8.69506 17.2418 8.80639 17.2716 8.92944 17.2716H11.0682M7.85715 16.179V13.976C7.87472 12.7759 7.40595 11.6177 6.56803 10.7818C5.4547 9.7131 4.88046 8.17872 5.02695 6.62641C5.04453 6.41148 5.08555 6.20251 5.12657 5.99951M7.85715 16.179V14.7223H12.1405V16.179C12.1405 16.6567 11.8358 17.0567 11.4139 17.2059M14.8769 5.99951C14.9531 6.35177 14.9941 6.71597 15 7.0921C15 8.50112 14.4258 9.84446 13.4179 10.8057C12.5917 11.6177 12.1288 12.7461 12.1464 13.9163V16.179C12.1464 16.6567 11.8417 17.0627 11.4198 17.2059M11.4139 17.2059C11.3026 17.2418 11.1912 17.2716 11.0682 17.2716M11.4139 17.2059C11.3026 17.2477 11.1912 17.2716 11.0682 17.2716M11.0682 17.2716H10.7108V18M14.8711 5.99355C14.3965 3.79048 12.5214 2.11278 10.2244 2.00532C7.76924 1.89188 5.63635 3.6054 5.1207 5.99355C5.07383 6.19655 5.03866 6.4055 5.02108 6.62044C4.88045 8.17274 5.44884 9.70712 6.56216 10.7758C7.40594 11.6176 7.86886 12.7699 7.85128 13.97V14.7163H12.1346V13.9043C12.1171 12.7341 12.58 11.6057 13.4062 10.7937C14.4199 9.8325 14.9941 8.48916 14.9883 7.08015C14.9883 6.70401 14.9414 6.33983 14.8652 5.98758L14.8711 5.99355Z" stroke="#05070A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Share — 1:1 from AI Guild Desktop/share.svg. 20x20 viewBox.
		// Stroke swapped from hardcoded black to currentColor so it inherits
		// .aif-share text color (matches the Aha! lightbulb pattern).
		'share' => '<svg class="' . $class . ' icon-share" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9.99996 4.16667C11.25 2.91667 12.2833 2.5 13.75 2.5C14.9655 2.5 16.1313 2.98289 16.9909 3.84243C17.8504 4.70197 18.3333 5.86776 18.3333 7.08333C18.3333 8.99167 17.075 10.45 15.8333 11.6667L9.99996 17.5L4.16663 11.6667C2.91663 10.4583 1.66663 9 1.66663 7.08333C1.66663 5.86776 2.14951 4.70197 3.00905 3.84243C3.86859 2.98289 5.03438 2.5 6.24996 2.5C7.71663 2.5 8.74996 2.91667 9.99996 4.16667ZM9.99996 4.16667L7.53328 6.63333C7.36396 6.80142 7.22958 7.00135 7.13788 7.22161C7.04617 7.44186 6.99896 7.67808 6.99896 7.91667C6.99896 8.15525 7.04617 8.39147 7.13788 8.61173C7.22958 8.83198 7.36396 9.03191 7.53328 9.2C8.21661 9.88333 9.30828 9.90833 10.0333 9.25833L11.7583 7.675C12.1907 7.28266 12.7536 7.06533 13.3374 7.06533C13.9213 7.06533 14.4842 7.28266 14.9166 7.675L17.3833 9.89167M15 12.5L13.3333 10.8333M12.5 15L10.8333 13.3333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Code XML (Lucide) — newsletter pref: AI Development
		// 24x24 viewBox — stroke-width 1.8 compensates for scale
		'code-xml' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m18 16 4-4-4-4" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m6 8-4 4 4 4" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m14.5 4-5 16" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Pen Tool (Lucide) — newsletter pref: Design
		// 24x24 viewBox — stroke-width 1.8 compensates for scale
		'pen-tool' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.707 21.293a1 1 0 0 1-1.414 0l-1.586-1.586a1 1 0 0 1 0-1.414l5.586-5.586a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 1 0 1.414z" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m18 13-1.375-6.874a1 1 0 0 0-.746-.776L3.235 2.028a1 1 0 0 0-1.207 1.207L5.35 15.879a1 1 0 0 0 .776.746L13 18" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m2.3 2.3 7.286 7.286" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="11" cy="11" r="2" stroke="' . $stroke . '" stroke-width="1.8"/></svg>',

		// Flask Conical (Lucide) — newsletter pref: Science / Research
		// 24x24 viewBox — stroke-width 1.8 compensates for scale
		'flask-conical' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2v6a2 2 0 0 0 .245.96l5.51 10.08A2 2 0 0 1 18 22H6a2 2 0 0 1-1.755-2.96l5.51-10.08A2 2 0 0 0 10 8V2" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.453 15h11.094" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.5 2h7" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Badge Euro (Lucide) — newsletter pref: European AI News
		// 24x24 viewBox — stroke-width 1.8 compensates for scale
		'badge-euro' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 12h5" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 9.4a4 4 0 1 0 0 5.2" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Map Pin Check (Lucide) — newsletter pref: Czech AI News
		// 24x24 viewBox — stroke-width 1.8 compensates for scale
		'map-pin-check' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.43 12.935c.357-.967.57-1.955.57-2.935a8 8 0 0 0-16 0c0 4.993 5.539 10.193 7.399 11.799a1 1 0 0 0 1.202 0 32.197 32.197 0 0 0 .813-.728" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke="' . $stroke . '" stroke-width="1.8"/><path d="m16 18 2 2 4-4" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',


		/* Chevron down (Lucide) — select triggers; the long arrow-down was never a chevron */
		'chevron-down' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>',

		/* Send (paper plane, Lucide) — harvested from live AIF newsletter markup */
		'send' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><path d="M22 2L15 22L11 13L2 9L22 2Z"/></svg>',

		/* ---- Batch 2 Tier 1 (Lucide v0.563.0) — gaps both live sites
		 * papered over with inline SVGs; import so themes migrate to the
		 * helper instead of hand-rolling markup. ---- */

		/* Menu (hamburger) — was inline in AIF header.php:152 + AIG header.php:99 */
		'menu' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/></svg>',

		/* Chevron left — was inline in AIG testimonials carousel (prev) */
		'chevron-left' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>',

		/* Chevron right — was inline in AIG testimonials carousel (next) + AIF comments.php CTA */
		'chevron-right' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',

		/* Copy (clipboard) — was inline in AIF page-newsletter-contacts.php */
		'copy' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>',

		/* ---- Batch 2 Tier 2 (Lucide v0.563.0) — future-useful, no live
		 * usage yet; grounded in site features (events, cohorts, careers,
		 * forms, video). ---- */

		/* Chevron up — chevron family completion (accordion collapse state) */
		'chevron-up' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>',

		/* Clock — event / course times */
		'clock' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>',

		/* Users — community / cohorts */
		'users' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>',

		/* External link — true outbound affordance ('source' is Lucide link = chain, semantically "source") */
		'external-link' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>',

		/* Mail — contact affordances (mail-check covers only the confirmation state) */
		'mail' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>',

		/* Briefcase — job positions (AIG careers) */
		'briefcase' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>',

		/* Circle alert — form validation / error states */
		'circle-alert' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',

		/* Info — notice states */
		'info' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',

		/* Play — video embeds (courses / articles) */
		'play' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z"/></svg>',

		/* Plus — accordion expand / add */
		'plus' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',

		/* Minus — accordion collapse / remove */
		'minus' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>',

		/* ---- Icons contributed by the AIG theme (absent from AIF) ---- */
		'smart-button' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.3,7.5c0.1,0.5,0.2,1,0.2,1.5c0,1.9-0.8,3.8-2.1,5.1c-1.1,1.1-1.7,2.7-1.7,4.3v1.1H13v-1c0-0.6-0.1-1.3-0.3-1.9c-0.1-0.2-0.1-0.4-0.2-0.5c-0.3-0.7-0.7-1.4-1.3-2l0,0c-0.8-0.8-1.4-1.8-1.8-3c-0.1-0.2-0.1-0.4-0.1-0.5C9.2,9.9,9.2,9.1,9.2,8.4c0-0.3,0.1-0.6,0.1-0.9h0v0C10,4.2,12.9,1.9,16.1,2C19.2,2.2,21.6,4.4,22.3,7.5z" style="fill: var(--icon-smart-accent)"/><polyline points="16.8,23 16.8,23 14.9,23" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><polyline points="14.9,23 14.9,23 14.9,24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.9,23h-0.5c-0.1,0-0.2,0-0.2,0c-0.1,0-0.1,0-0.2-0.1" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.9,22.9L13.9,22.9c-0.6-0.2-1-0.8-1-1.4" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.9,22.9c0.1,0,0.2,0.1,0.2,0.1c0.1,0,0.1,0,0.2,0h0.5h1.9" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.2,8.4c0-0.3,0.1-0.6,0.1-0.9h0H5.9C4.8,7.5,4,8.4,4,9.5V23c0,1.1,0.8,2,1.9,2h4.7c0.3,0,0.6,0.1,0.8,0.4l3.7,5.2c0.2,0.2,0.4,0.4,0.7,0.4c0.3,0,0.5-0.1,0.7-0.4l3.7-5.2c0.2-0.3,0.5-0.4,0.8-0.4h4.7c1,0,1.9-0.9,1.9-2V9.5c0-1.1-0.9-2-1.9-2h-3.5c0.1,0.5,0.2,1,0.2,1.5c0,1.9-0.8,3.8-2.1,5.1c-1.1,1.1-1.7,2.7-1.7,4.3v3.1c0,0.4-0.2,0.8-0.4,1.1" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.5,11.1c-0.1-0.2-0.1-0.4-0.1-0.5" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.5,16c-0.3-0.7-0.7-1.4-1.3-2" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13,19.5L13,19.5v-1c0-0.6-0.1-1.3-0.3-1.9" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13,21.5v-2h5.7v2c0,0.4-0.2,0.8-0.4,1.1c-0.1,0.2-0.3,0.3-0.5,0.4h0" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.6,22.9c-0.1,0-0.3,0.1-0.4,0.1" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.7,22.9C17.7,22.9,17.7,22.9,17.7,22.9C17.5,23,17.4,23,17.2,23" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17.2,23 16.8,23 16.8,24 16.8,24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13,19.5h5.7v-1.1c0-1.6,0.6-3.2,1.7-4.3c1.4-1.3,2.1-3.2,2.1-5.1c0-0.5-0.1-1-0.2-1.5c-0.6-3-3.1-5.3-6.2-5.5C12.9,1.9,10,4.2,9.4,7.5v0C9.3,7.8,9.3,8.1,9.2,8.4c-0.1,0.8,0,1.5,0.2,2.2c0,0.2,0.1,0.4,0.1,0.5c0.3,1.1,0.9,2.1,1.8,3l0,0c0.5,0.6,1,1.2,1.3,2c0.1,0.2,0.1,0.4,0.2,0.5c0.2,0.6,0.3,1.2,0.3,1.9L13,19.5L13,19.5z" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Lightbulb (outline) — Aha! engagement button, default state.
		'check-bold' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 5L7.50001 14.1667L3.33334 10" stroke="' . $stroke . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Location pin (map pin) — job position location chips. Same 20×20
		// stroke-1.5 grammar as the core set.
		'pin' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 8.33335C16.6667 12.4942 11.4651 17.0014 10.4067 17.8689C10.289 17.9579 10.1467 18.006 10.0001 18.006C9.85342 18.006 9.71114 17.9579 9.59341 17.8689C8.53508 17.0014 3.33341 12.4942 3.33341 8.33335C3.33341 6.56524 4.03579 4.86955 5.28604 3.61931C6.53628 2.36907 8.23197 1.66669 10.0001 1.66669C11.7682 1.66669 13.4639 2.36907 14.7141 3.61931C15.9644 4.86955 16.6667 6.56524 16.6667 8.33335Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.0001 10.8334C11.3808 10.8334 12.5001 9.71407 12.5001 8.33335C12.5001 6.95264 11.3808 5.83335 10.0001 5.83335C8.61937 5.83335 7.50008 6.95264 7.50008 8.33335C7.50008 9.71407 8.61937 10.8334 10.0001 10.8334Z" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

		// Skills — job position skills line. Batch 2 geometry swap: Lucide
		// hammer replaces the off-grammar Feather "tool" wrench (stroke 1.7).
		// Slug stays 'skills' (position cards).
		'skills' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="m15 12-9.373 9.373a1 1 0 0 1-3.001-3L12 9"/><path d="m18 15 4-4"/><path d="m21.5 11.5-1.914-1.914A2 2 0 0 1 19 8.172v-.344a2 2 0 0 0-.586-1.414l-1.657-1.657A6 6 0 0 0 12.516 3H9l1.243 1.243A6 6 0 0 1 12 8.485V10l2 2h1.172a2 2 0 0 1 1.414.586L18.5 14.5"/></svg>',

		// Source / External link (chain) — 1:1 port of the AIF event-card CTA
		// icon (aigds_icon "source"); used by the position card "Detail pozice".
		'linkedin' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',

		'x' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',

		'instagram' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',

		'facebook' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M24 12c0-6.627-5.373-12-12-12S0 5.373 0 12c0 5.628 3.874 10.35 9.101 11.647v-7.98H6.627V12h2.474v-1.58c0-4.084 1.849-5.978 5.859-5.978.76 0 2.072.15 2.608.298v3.325c-.283-.03-.775-.045-1.386-.045-1.967 0-2.728.746-2.728 2.683V12h3.92l-.673 3.667h-3.247v8.245C19.396 23.195 24 18.135 24 12z"/></svg>',
		'bluesky' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.785 2.627 3.624 3.496 6.222 3.103-4.588.698-8.59 2.496-4.246 7.062C8.498 26.603 10.793 18.143 12 14.87c1.207 3.274 3.093 11.354 9.4 5.542 4.344-4.566.342-6.364-4.246-7.062 2.598.393 5.437-.476 6.222-3.103C23.622 9.418 24 4.458 24 3.768c0-.688-.139-1.86-.902-2.203-.659-.299-1.664-.621-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/></svg>',

		'web' => '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>',

		// Testimonial quote mark — HARVESTED art (icons/custom/quote-brackets.svg,
		// the reference-card shortcode's inline SVG), registered 2026-07-04.
		// Shape type: fill currentColor, recolors from context. 64x56 native.
		'quote-brackets' => '<svg class="' . $class . '" width="' . $size . '" height="' . round( $size * 56 / 64 ) . '" viewBox="0 0 64 56" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M50.4158 55.1772C46.2144 55.1772 42.76 53.8235 40.0525 51.116C37.4384 48.4085 36.1313 44.4406 36.1313 39.2123C36.1313 32.5835 38.1853 25.5813 42.2932 18.2057C46.4012 10.8301 51.3961 4.7615 57.2779 0L62.0394 4.34136C60.4522 6.11525 59.0051 8.35596 57.698 11.0635C56.391 13.771 55.2706 16.6652 54.337 19.7462C53.4967 22.8271 52.9832 25.9548 52.7965 29.1291C56.1576 29.6893 58.8651 31.1831 60.919 33.6105C62.973 35.9446 64 38.7455 64 42.0131C64 45.841 62.7396 49.0153 60.2188 51.5361C57.698 53.9635 54.4303 55.1772 50.4158 55.1772ZM14.2845 55.1772C9.98979 55.1772 6.53538 53.8235 3.92122 51.116C1.30708 48.4085 0 44.4406 0 39.2123C0 32.5835 2.00729 25.5813 6.02188 18.2057C10.1298 10.8301 15.1714 4.7615 21.1466 0L25.9081 4.34136C24.4143 6.11525 22.9672 8.35596 21.5667 11.0635C20.2597 13.771 19.1393 16.6652 18.2057 19.7462C17.3654 22.8271 16.8519 25.9548 16.6652 29.1291C20.0263 29.6893 22.7338 31.1831 24.7877 33.6105C26.8417 35.9446 27.8687 38.7455 27.8687 42.0131C27.8687 45.841 26.6083 49.0153 24.0875 51.5361C21.5667 53.9635 18.299 55.1772 14.2845 55.1772Z"/></svg>',

	);

	if ( ! isset( $icons[ $icon ] ) ) {
		return '';
	}

	$svg = $icons[ $icon ];

	/*
	 * Stroke width is no longer computed here: outline icons carry the
	 * .icon--stroked class, and components.css sets stroke-width: var(--stroke-1_5)
	 * + vector-effect:non-scaling-stroke, so every outline icon renders a constant
	 * 1.5px stroke at any size/viewBox (the inline stroke-width attrs are just
	 * fallbacks the CSS overrides). check-bold adds .icon--stroked-bold (3px).
	 */
	return $svg;
}

/**
 * Returns array of available icon choices for ACF select fields.
 *
 * @return array Icon slug => Label pairs.
 */
function aigds_icon_choices() {
	return array(
		''            => '— None (use custom image) —',
		'course'      => 'Course (graduation cap)',
		'calendar'    => 'Calendar',
		'chat'        => 'Chat / Message',
		'check'       => 'Checkmark',
		'circle-check-big' => 'Circle Check Big (success)',
		'mail-check'  => 'Mail Check (email confirmation)',
		'arrow-right' => 'Arrow Right',
		'arrow-down'  => 'Arrow Down',
		'download'    => 'Download',
		'close'       => 'Close / X',
		'edit'        => 'Edit / Pencil',
		'signal'      => 'Signal (arrow up-right)',
		'editorial'   => 'Editorial (star)',
		'map-pin'     => 'Map Pin (location)',
		'source'      => 'Source / External link (chain)',
		'rss'         => 'RSS Feed',
		'user-round'    => 'User / Profile (rounded)',
		'code-xml'      => 'Code XML (development)',
		'pen-tool'      => 'Pen Tool (design)',
		'flask-conical' => 'Flask (science/research)',
		'badge-euro'    => 'Badge Euro (EU news)',
		'map-pin-check' => 'Map Pin Check (Czech news)',
		'chevron-down'  => 'Chevron Down (select trigger)',
		'chevron-up'    => 'Chevron Up',
		'chevron-left'  => 'Chevron Left',
		'chevron-right' => 'Chevron Right',
		'menu'          => 'Menu (hamburger)',
		'copy'          => 'Copy (clipboard)',
		'clock'         => 'Clock (time)',
		'users'         => 'Users (community/cohort)',
		'external-link' => 'External Link (outbound)',
		'mail'          => 'Mail (contact)',
		'briefcase'     => 'Briefcase (job position)',
		'circle-alert'  => 'Circle Alert (error/warning)',
		'info'          => 'Info (notice)',
		'play'          => 'Play (video)',
		'plus'          => 'Plus (expand/add)',
		'minus'         => 'Minus (collapse/remove)',
		'send'          => 'Send (paper plane — newsletter)',
		'smart-button'  => 'Smart Button (bulb, BRAND accent fill)',
		'check-bold'    => 'Checkmark Bold',
		'pin'           => 'Pin (map marker)',
		'skills'        => 'Skills (wrench)',
		'linkedin'      => 'LinkedIn (social)',
		'x'             => 'X / Twitter (social)',
		'instagram'     => 'Instagram (social)',
		'facebook'      => 'Facebook (social)',
		'bluesky'       => 'Bluesky (social)',
		'web'           => 'Web (globe)',
		'quote-brackets' => 'Quote brackets (testimonial)',
	);
}

/**
 * Returns array of icon slugs (without labels) for validation.
 *
 * @return array Icon slugs.
 */
function aigds_icon_slugs() {
	$choices = aigds_icon_choices();
	unset( $choices[''] );
	return array_keys( $choices );
}
