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
		'title_reply'    => __( 'Proposez un nouveau sujet', 'anticonferences' ),
		'label_submit'   => __( 'Publier', 'anticonferences' ),
	) );

	// Remove the temporary filter
	remove_filter( 'comment_form_defaults', 'anticonferences_topic_form_fields' );
}

function anticonferences_topic_type( $submit_fields = '' ) {
	$submit_fields .= '<input type="hidden" value="ac_topic" name="ac_comment_type"/>';

	return '<input type="reset" value="' . esc_attr__( 'Abandonner', 'anticonferences' ) . '"/>' . $submit_fields;
}

function anticonferences_topic_support( $link = '', $args = array(), WP_Comment $comment ) {
	// Topics need to be approved to be supported.
	if ( ! $comment->comment_approved ) {
		return;
	}

	$emails    = wp_list_pluck( $comment->get_children(), 'comment_approved', 'comment_author_email' );
	$commenter = wp_get_current_commenter();
	$class     = 'ac-love';
	$disabled  = '';
	$feedback  = '';

	if ( is_user_logged_in() ) {
		$commenter['comment_author_email'] = wp_get_current_user()->user_email;
	}

	if ( isset( $commenter['comment_author_email'] ) && isset( $emails[ $commenter['comment_author_email'] ] ) ) {
		$class    = 'ac-loved';
		$disabled = ' disabled="disabled"';

		if ( 0 === (int) $emails[ $commenter['comment_author_email'] ] ) {
			$feedback = sprintf(
				'<p class="support-awaiting-moderation">%s</p>',
				__( 'Votre soutien à ce sujet est en attente de validation. Un email vous a été envoyé pour procéder à celle-ci.', 'anticonferences' )
			);
		}
	}

	return sprintf( '<button type="button" class="ac-support-button" data-topic-id="%1$s"%2$s>
			<span class="screen-reader-text">%3$s</span>
			<span class="%4$s"></span>
		</button>
		<span class="ac-support-count">%5$s</span>
		%6$s',
		(int) $comment->comment_ID,
		$disabled,
		esc_html__( 'Supporter ce sujet', 'anticonferences' ),
		$class,
		anticonferences_topic_get_support_count( $comment ),
		$feedback
	);
}

function anticonferences_support_form() {
	$unlogged_inputs = '';

	if ( ! is_user_logged_in() ) {
		$commenter = wp_get_current_commenter();
		$unlogged_inputs = sprintf( '<div class="comment-form-email">
				<label for="support-email">%1$s <span class="required">*</span></label>
				<input id="support-email" name="email" type="email" value="%2$s" size="30" maxlength="100"/>
				<input type="hidden" name="author" id="ac-support-author" value="%3$s"/>
			</div>',
			esc_html__( 'Email', 'anticonferences' ),
			esc_attr( $commenter['comment_author_email'] ),
			esc_attr( $commenter['comment_author'] )
		);
	}

	printf( '<form class="comment-form support-form" action="%1$s" method="post">
			%2$s
			<div class="submit">
				<input type="hidden" name="comment" id="ac-support-amount"/>
				<input type="hidden" name="ac_comment_type" value="ac_support"/>
				<input type="hidden" name="comment_post_ID" value="%3$s"/>
				<input type="hidden" name="comment_parent" id="ac-topic-id"/>
				<input name="submit" type="submit" class="submit" value="%4$s"/>
				<input name="reset" type="reset" class="reset" value="%5$s"/>
			</div>
		</form>',
		esc_url( site_url( '/wp-comments-post.php' ) ),
		$unlogged_inputs,
		(int) get_the_ID(),
		esc_attr__(  'Soutenir', 'anticonferences' ),
		esc_attr__(  'Abandonner', 'anticonferences' )
	);
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
