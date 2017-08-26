<?php
/**
 * Plugin's admin functions.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function anticonferences_admin_register_metabox( $festival = null ) {
	$pt = get_post_type( $festival );

	if ( 'festivals' !== $pt ) {
		return;
	}

	remove_meta_box( 'commentstatusdiv', get_current_screen(), 'normal' );

	$metaboxes = array(
		'ac-details-metabox' => (object) array(
			'id'    => 'ac-details-metabox',
			'title' => __( 'Details du festival', 'anticonferences' ),
			'cb'    => 'anticonferences_admin_details_metabox',
			'ctxt'  => 'normal',
			'prio'  => 'high',
		),
	);

	if ( current_theme_supports( 'post-formats' ) ) {
		remove_meta_box( 'formatdiv', get_current_screen(), 'side' );

		$metaboxes['formatdiv'] = (object) array(
			'id'    => 'formatdiv',
			'title' => _x( 'Format', 'post format', 'anticonferences' ),
			'cb'    => 'anticonferences_admin_format_metabox',
			'ctxt'  => 'side',
			'prio'  => 'low',
		);
	}

	foreach ( $metaboxes as $metabox ) {
		add_meta_box( $metabox->id, $metabox->title, $metabox->cb, $pt, $metabox->ctxt, $metabox->prio );
	}
}

function anticonferences_admin_details_metabox( $festival = null ) {
	$pt = get_post_type( $festival );

	if ( 'festivals' !== $pt ) {
		printf( '<p class="notice error">%s</p>', esc_html__( 'Le type de contenu ne correspond pas à celui attentdu.', 'anticonferences' ) );
		return;
	}

	$metas = get_registered_meta_keys( $pt );

	if ( ! $metas ) {
		printf( '<p class="notice error">%s</p>', esc_html__( 'Les détails ne sont pas disponibles pour ce festival', 'anticonferences' ) );
		return;
	}

	$customs      = get_post_custom( $festival->ID );
	$placeholders = apply_filters( 'anticonferences_meta_placeholders', array(
		'_festival_closing_date' => 'YYYY-MM-DD HH:II',
	) );

	$output = '';
	foreach ( $metas as $key => $meta ) {
		$type = 'text';
		$ph   = $value = '';

		if ( 'integer' === $meta['type'] ) {
			$type = 'numeric';
		}

		if ( isset( $customs[ $key ] ) ) {
			$value = reset( $customs[ $key ] );

			if ( '_festival_closing_date' === $key && is_numeric( $value ) ) {
				$value = date_i18n( 'Y-m-d H:i', $value );
			}
		}

		if ( isset( $placeholders[ $key ] ) ) {
			$ph = ' placeholder="' . esc_attr( $placeholders[ $key ] ) . '"';
		}

		$output .= sprintf( '<tr>
			<td class="left">
				<label for="%1$s">%2$s</label>
			</td>
			<td>
				<input type="%3$s" name="meta_input[%1$s]" id="%1$s" class="widefat" value="%4$s"%5$s/>
			</td>
		</tr>
		', esc_attr( $key ), esc_html( $meta['description'] ), esc_attr( $type ), esc_attr( $value ), $ph );
	}

	if ( ! $output ) {
		return;
	}

	echo '<table class="fixed" width="100%">' . $output . '</table>';
}

function anticonferences_admin_format_metabox( $festival = null ) {
	$theme_pf     = get_theme_support( 'post-formats' );
	$theme_pf     = reset( $theme_pf );
	$supported_pf = anticonferences_get_support( 'post-formats' );
	$post_formats = array_intersect( $theme_pf, $supported_pf );

	if ( ! $post_formats ) {
		$post_formats = array( 0 );
		$default      = 0;
	} else {
		$default = reset( $post_formats );
	}

	$post_format = get_post_format( $festival->ID );

	if ( ! $post_format ) {
		$post_format = $default;
	}

	?>
	<div id="post-formats-select">
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Post Formats', 'anticonferences' ); ?></legend>

			<?php foreach ( $post_formats as $format ) : ?>

				<input type="radio" name="post_format" class="post-format" id="post-format-<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $format ); ?>" <?php checked( $post_format, $format ); ?> /> <label for="post-format-<?php echo esc_attr( $format ); ?>" class="post-format-icon post-format-<?php echo esc_attr( $format ); ?>"><?php echo esc_html( get_post_format_string( $format ) ); ?></label><br />

			<?php endforeach; ?>
		</fieldset>
	</div>
	<?php
}
