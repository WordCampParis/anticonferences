<?php
/**
 * Plugin's admin functions.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function anticonferences_admin_register_metabox( $camp = null ) {
	$pt = get_post_type( $camp );

	if ( 'camps' !== $pt ) {
		return;
	}

	remove_meta_box( 'commentstatusdiv', get_current_screen(), 'normal' );
	remove_meta_box( 'commentsdiv', get_current_screen(), 'normal' );

	$metaboxes = array(
		'ac-details-metabox' => (object) array(
			'id'    => 'ac-details-metabox',
			'title' => __( 'Details du camp', 'anticonferences' ),
			'cb'    => 'anticonferences_admin_details_metabox',
			'ctxt'  => 'normal',
			'prio'  => 'high',
		),
	);

	if ( isset( $camp->ID ) ) {
		$topics_count = wp_count_comments( $camp->ID );

		if ( ! empty( $topics_count->total_comments ) ) {
			$metaboxes['commentsdiv'] = (object) array(
				'id'    => 'commentsdiv',
				'title' => __( 'Sujets proposés', 'anticonferences' ),
				'cb'    => 'post_comment_meta_box',
				'ctxt'  => 'aniticonferences',
				'prio'  => 'high',
			);
		}
	}


	if ( current_theme_supports( 'post-formats' ) ) {
		remove_meta_box( 'formatdiv', get_current_screen(), 'side' );

		$metaboxes['formatdiv'] = (object) array(
			'id'    => 'formatdiv',
			'title' => _x( 'Format', 'post format', 'anticonferences' ),
			'cb'    => 'anticonferences_admin_format_metabox',
			'ctxt'  => 'side',
			'prio'  => 'default',
		);
	}

	foreach ( $metaboxes as $metabox ) {
		add_meta_box( $metabox->id, $metabox->title, $metabox->cb, $pt, $metabox->ctxt, $metabox->prio );
	}
}

function anticonferences_admin_details_metabox( $camp = null ) {
	$pt = get_post_type( $camp );

	if ( 'camps' !== $pt ) {
		printf( '<p class="notice error">%s</p>', esc_html__( 'Le type de contenu ne correspond pas à celui attentdu.', 'anticonferences' ) );
		return;
	}

	$metas = get_registered_meta_keys( $pt );

	if ( ! $metas ) {
		printf( '<p class="notice error">%s</p>', esc_html__( 'Les détails ne sont pas disponibles pour ce camp', 'anticonferences' ) );
		return;
	}

	$customs      = get_post_custom( $camp->ID );
	$placeholders = apply_filters( 'anticonferences_meta_placeholders', array(
		'_camp_closing_date'  => 'YYYY-MM-DD HH:II',
		'_camp_slack_webhook' => __( 'URL du webhook Slack', 'anticonferences' ),
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

			if ( '_camp_closing_date' === $key && is_numeric( $value ) ) {
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

function anticonferences_admin_format_metabox( $camp = null ) {
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

	$post_format = get_post_format( $camp->ID );

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

function anticonferences_admin_box_area( $camp = null ) {
	if ( empty( $camp->ID ) ) {
		return;
	}

	$topics_count = wp_count_comments( $camp->ID );

	// Do not display the custom Metabox area when no topics.
	if ( empty( $topics_count->total_comments ) ) {
		return;
	}
	?>
	<br class="clear" />
	<div id="postbox-container-0" class="postbox-container">

		<?php
		/**
		 * Add a custom Metabox area so that topics are
		 * listed first.
		 */
		do_meta_boxes( null, 'aniticonferences', $camp ); ?>

	</div>
	<br class="clear" />
	<?php
}
add_action( 'edit_form_after_title', 'anticonferences_admin_box_area', 10, 1 );

function anticonferences_admin_load_edit_comments() {
	global $typenow;

	if ( ! empty( $_GET['post_type'] ) || empty( $_GET ) ) {
		return;
	}

	$get_keys = array_keys( $_GET );

	// Editing a single Topic
	if ( 'load-comment.php' === current_action() ) {
		if ( empty( $_GET['c'] ) ) {
			return;
		}

		$comment = get_comment( $_GET['c'] );

		if ( 'ac_topic' !== $comment->comment_type ) {
			return;
		}

		$post_type = 'camps';
		anticonferences()->admin_inline_script = array(
			'editTopic' => esc_html__( 'Modifier le sujet', 'anticonferences' ),
			'titletag'  => esc_html__( 'Modification d\'un sujet', 'anticonferences' ),
		);

	// Moderating Topics
	} else {
		$keys = array( 'p', 'comment_status' );
		$match_keys = array_intersect( $get_keys, $keys );

		if ( ! $match_keys ) {
			return;
		}

		$post_type = get_post_type( absint( $_GET['p'] ) );
		if ( empty( $post_type ) || 'camps' !== $post_type ) {
			return;
		}

		anticonferences()->admin_inline_script = array(
			'moderateTopics' => esc_html__( 'Sujets proposés pour {l}', 'anticonferences' ),
			'searchTopics'   => esc_html__( 'Rechercher un sujet', 'anticonferences' ),
			'topicColumn'    => esc_html__( 'Sujet', 'anticonferences' ),
			'titletag'       => esc_html__( 'Modérations des sujets', 'anticonferences' ),
		);
	}

	$typenow = $post_type;
	get_current_screen()->post_type = $post_type;

	add_filter( 'admin_title', 'anticonferences_admin_title', 10, 1 );
}
add_action( 'load-edit-comments.php', 'anticonferences_admin_load_edit_comments', 10 );
add_action( 'load-comment.php',       'anticonferences_admin_load_edit_comments', 10 );

function anticonferences_admin_title( $admin_title = '' ) {
	$title = explode( '&lsaquo;', $admin_title );

	$ac = anticonferences();

	if ( isset( $ac->admin_inline_script['titletag'] ) && isset( $title[1] ) ) {
		$title[0]    = $ac->admin_inline_script['titletag'];
		$admin_title = join( ' &lsaquo;', $title );
	}

	return $admin_title;
}

function anticonferences_admin_head() {
	global $parent_file;

	$ac_parent = add_query_arg( 'post_type', 'camps', 'edit.php' );

	if ( 'camps' === get_current_screen()->post_type && $ac_parent !== $parent_file ) {
		$parent_file = $ac_parent;
	}
}
add_action( 'admin_head', 'anticonferences_admin_head', 10 );

function anticonferences_admin_enqueue_scripts() {
	if ( 'camps' !== get_current_screen()->post_type ) {
		return;
	}

	wp_enqueue_style( 'ac-admin-style', anticonferences_get_stylesheet( 'admin' ), array(), anticonferences()->version );

	if ( isset( anticonferences()->admin_inline_script ) ) {
		wp_add_inline_script( 'common', sprintf( '
			( function( $ ) {
				$( document ).ready( function() {
					var text = JSON.parse( \'%s\' );

					if ( $( \'.edit-comments-php h1.wp-heading-inline\' ).length ) {
						var link = $( \'.edit-comments-php h1.wp-heading-inline\' ).find( \'a\' ).get( 0 ).outerHTML;

						$( \'.edit-comments-php h1.wp-heading-inline\' ).html( text.moderateTopics.replace( \'{l}\', link ) );
						$( \'#comments-form #search-submit\' ).val( text.searchTopics );
						$( \'#comments-form .wp-list-table th.column-comment\').html( text.topicColumn );
					} else if ( $( \'.comment-php .wrap h1\' ).length ) {
						$( \'.comment-php .wrap h1\' ).html( text.editTopic );
					}
				} );
			} )( jQuery );
		', json_encode( anticonferences()->admin_inline_script ) ) );
	}
}
add_action( 'admin_enqueue_scripts', 'anticonferences_admin_enqueue_scripts' );
