<?php
/**
 * Plugin's functions.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function anticonferences_subject_get_editor() {
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

function anticonferences_subject_form() {
	// Temporarly filter the comment form default arguments.
	add_filter( 'comment_form_defaults', 'anticonferences_subject_form_fields' );

	comment_form( array(
		'title_reply'    => __( 'Proposer un nouveau sujet', 'anticonferences' ),
		'label_submit'   => __( 'Publier', 'anticonferences' ),
	) );

	// Remove the temporary filter
	remove_filter( 'comment_form_defaults', 'anticonferences_subject_form_fields' );
}

function anticonferences_subject_type( $submit_fields = '' ) {
	$submit_fields .= '<input type="hidden" value="ac_subject" name="ac_comment_type"/>';
	return $submit_fields;
}

function anticonferences_subject_support( $link = '', $args = array(), WP_Comment $comment ) {
	// Subjects need to be approved to be supported.
	if ( ! $comment->comment_approved ) {
		return;
	}

	return esc_html__( 'Supporter ce sujet', 'anticonferences' );
}
