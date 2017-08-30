<?php
/**
 * Plugin's functions.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function anticonferences_topic_get_editor() {
	$args = array(
		'textarea_name' => 'comment',
		'wpautop'       => true,
		'media_buttons' => false,
		'textarea_rows' => 8,
		'teeny'         => false,
		'dfw'           => false,
		'tinymce'       => true,
		'quicktags'     => false
	);

	// Temporarly filter the editor buttons
	add_filter( 'mce_buttons', 'anticonferences_mce_buttons', 10, 1 );

	ob_start();
	?>

	<p class="comment-form-comment">
		<label for="comment"><?php esc_html_e( 'Description du Sujet', 'anticonferences' ) ;?> <span class="required">*</span></label>
		<?php wp_editor( '', 'comment', $args ); ?>
	</p>

	<?php
	$editor = ob_get_clean();

	// Remove the temporary filter
	remove_filter( 'mce_buttons', 'anticonferences_mce_buttons', 10, 1 );

	return $editor;
}

function anticonferences_topics_toolbar() {
	?>
	<div id="topics-toolbar">
		<ul class="filter-links">
			<li id="ac-new-topic">
				<button class="button button-primary">
					<span class="label"><?php esc_html_e( 'Nouveau sujet', 'anticonferences' ); ?></span>
				</button>
			</li>
		</ul>
	</div>
	<?php
}

function anticonferences_topic_form() {
	// Temporarly filter the comment form default arguments.
	add_filter( 'comment_form_defaults', 'anticonferences_topic_form_fields' );

	comment_form( array(
		'title_reply'    => '',
		'label_submit'   => __( 'Publier', 'anticonferences' ),
	) );

	// Remove the temporary filter
	remove_filter( 'comment_form_defaults', 'anticonferences_topic_form_fields' );
}

function anticonferences_topic_type( $submit_fields = '' ) {
	$submit_fields .= '<input type="hidden" value="ac_topic" name="ac_comment_type"/>';
	return $submit_fields;
}

function anticonferences_topic_support( $link = '', $args = array(), WP_Comment $comment ) {
	// Topics need to be approved to be supported.
	if ( ! $comment->comment_approved ) {
		return;
	}

	return esc_html__( 'Supporter ce sujet', 'anticonferences' );
}

function anticonferences_topics_closed() {
	$closed   = false;
	$end_date = (int) get_post_meta( get_the_ID(), '_camp_closing_date', true );

	if ( ! $end_date ) {
		return $closed;
	}

	$now = strtotime( date_i18n( 'Y-m-d H:i' ) );

	if ( $end_date <= $now ) {
		$closed = true;
	}

	return $closed;
}
