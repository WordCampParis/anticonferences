<?php
/**
 * Plugin's template tags.
 *
 * @since  1.0.0
 *
 * @package  AntiConferences\inc
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Gets a WP Editor to write a topic.
 *
 * @since  1.0.0
 *
 * @return string the WP Editor
 */
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
		<label for="comment"><?php esc_html_e( 'Topic description', 'anticonferences' ) ;?> <span class="required">*</span></label>
		<?php wp_editor( '', 'comment', $args ); ?>
	</p>

	<?php
	$editor = ob_get_clean();

	// Remove the temporary filter
	remove_filter( 'mce_buttons', 'anticonferences_mce_buttons', 10, 1 );

	return $editor;
}

/**
 * Outputs the Camp's topics order form.
 *
 * @since  1.0.0
 */
function anticonferences_order_form() {
	$order_options = anticonferences_get_order_options();
	$order_value   = get_query_var( 'orderby' );

	if ( ! $order_value ) {
		$order_value = 'date_asc';
	}

	$by             = 'ASC';
	$options_output = '';
	foreach ( $order_options as $o => $orderby ) {
		$selected = selected( $order_value, $o, false );

		$options_output .= '<option value="' . $o . '" ' . $selected . '>' . esc_html( $orderby['label'] ) . '</option>';

		if ( $selected ) {
			$by = $orderby['order'];
		}

	}

	$by_input = sprintf( '<input type="hidden" id="ac-order-order" name="order" value="%s"/>', esc_attr( $by ) );

	$order_form_html = sprintf( '
		<form action="%1$s" method="get" id="ac-order-form" class="nav-form">%2$s
			<label for="orderby">
				<span class="screen-reader-text">%3$s</span>
			</label>
			<select name="orderby" id="ac-order-box">
				%4$s
			</select>

			<button type="submit" class="submit-sort">
				<span class="ac-order-icon"></span>
				<span class="screen-reader-text">%5$s</span>
			</button>
		</form>
	', esc_url( get_post_permalink() ), $by_input, esc_attr__( 'Display', 'anticonferences' ), $options_output, esc_attr__( 'Display', 'anticonferences' ) );

	echo $order_form_html;
}

/**
 * Outputs the Topics toolbar.
 *
 * @since  1.0.0
 */
function anticonferences_topics_toolbar() {
	?>
	<div id="topics-toolbar">
		<ul class="filter-links">
			<?php if ( ! anticonferences_topics_closed() ) : ?>
				<li id="ac-new-topic">
					<button class="button button-primary">
						<span class="label"><?php esc_html_e( 'New topic', 'anticonferences' ); ?></span>
					</button>
				</li>
			<?php else : ?>

				<li id="ac-camp-closed">
					<p><?php esc_html_e( 'Time to suggest new topics is over.', 'anticonferences' ); ?></p>
				</li>

			<?php endif ; ?>

			<li id="ac-order-form" class="last">
				<?php anticonferences_order_form() ;?>
			</li>
		</ul>
	</div>
	<?php
}

/**
 * Outputs the new Topic form.
 *
 * @since 1.0.0
 */
function anticonferences_topic_form() {
	// Temporarly filter the comment form default arguments.
	add_filter( 'comment_form_defaults', 'anticonferences_topic_form_fields' );

	comment_form( array(
		'title_reply'    => __( 'Suggest a new topic', 'anticonferences' ),
		'label_submit'   => __( 'Publish', 'anticonferences' ),
	) );

	// Remove the temporary filter
	remove_filter( 'comment_form_defaults', 'anticonferences_topic_form_fields' );
}

/**
 * Add custom submit fields for the new Topic form.
 *
 * @since  1.0.0
 *
 * @param  string $submit_fields HTML output for custom submit fields.
 * @return string                HTML output for custom submit fields.
 */
function anticonferences_topic_type( $submit_fields = '' ) {
	$submit_fields .= '<input type="hidden" value="ac_topic" name="ac_comment_type"/>';

	return '<input type="reset" value="' . esc_attr__( 'Cancel', 'anticonferences' ) . '"/>' . $submit_fields;
}

/**
 * Outputs the Support action button by replacing the Comment reply link.
 *
 * @since  1.0.0
 *
 * @param  string     $link    The Comment reply link
 * @param  array      $args    @see get_comment_reply_link() for the description of available args.
 * @param  WP_Comment $comment The Topic object.
 * @return string              The Support action button.
 */
function anticonferences_topic_support( $link = '', $args = array(), WP_Comment $comment ) {
	// Topics need to be approved to be supported.
	if ( ! $comment->comment_approved ) {
		return;
	}

	$support_count = anticonferences_topic_get_support_count( $comment );
	$children      = $comment->get_children( array( 'type' => 'ac_support' ) );

	// Make sure to check children if there's a support count.
	if ( ! $children && 0 !== $support_count ) {
		$children = get_comments( array(
			'parent' => $comment->comment_ID,
			'type'   => 'ac_support',
		) );
	}

	$emails    = wp_list_pluck( $children, 'comment_approved', 'comment_author_email' );
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
				__( 'Your support to this topic is awaiting validation. Please check your emails to proceed to it.', 'anticonferences' )
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
		esc_html__( 'Support this topic', 'anticonferences' ),
		$class,
		$support_count,
		$feedback
	);
}

/**
 * Outputs the new support form.
 *
 * @since  1.0.0
 */
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
		esc_attr__(  'Support', 'anticonferences' ),
		esc_attr__(  'Cancel', 'anticonferences' )
	);
}

/**
 * Checks if the call for topics is closed.
 *
 * @since  1.0.0
 *
 * @return boolean True if the call for topics is closed. False otherwise.
 */
function anticonferences_topics_closed() {
	return anticonferences_camp_ended( get_the_ID() );
}
