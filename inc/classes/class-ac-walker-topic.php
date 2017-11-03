<?php
/**
 * Topic Walker
 *
 * Adapts the Comments Walker to our topic needs.
 *
 * @since  1.0.0
 *
 * @package  AntiConferences\inc\classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Walker_Comment' ) ) {
	require_once ABSPATH . WPINC . '/class-walker-comment.php';
}

class AC_Walker_Topic extends Walker_Comment {
	/**
	 * Outputs a single topic.
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function comment( $comment, $depth, $args ) {
		add_filter( 'comment_reply_link', 'anticonferences_topic_support', 10, 3 );
		$comment = get_comment( $comment );

		ob_start();
		parent::comment( $comment, $depth, $args );
		$topic = ob_get_clean();

		remove_filter( 'comment_reply_link', 'anticonferences_topic_support', 10, 3 );

		$this->topic( $topic, true, $comment );
	}

	/**
	 * Outputs a single topic in the HTML5 format.
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		add_filter( 'comment_reply_link', 'anticonferences_topic_support', 10, 3 );
		$comment = get_comment( $comment );

		ob_start();
		parent::html5_comment( $comment, $depth, $args );
		$topic = ob_get_clean();

		remove_filter( 'comment_reply_link', 'anticonferences_topic_support', 10, 3 );

		$this->topic( $topic, true, $comment );
	}

	/**
	 * Replaces the awaiting approval message to fit topic context.
	 *
	 * @since  1.0.0
	 * @since  1.0.1 adds the $comment paremeter.
	 *
	 * @param  string     $topic   The topic output.
	 * @param  boolean    $echo    Whether to return or display the output.
	 * @param  WP_Comment $comment The comment object.
	 * @return string              The topic output.
	 */
	public function topic( $topic, $echo = false, WP_Comment $comment ) {
		$topic = preg_replace(
			'/<p class=\"comment-awaiting-moderation\">(.*?)<\/p>/',
			sprintf ( '<p class="topic-awaiting-moderation">%s</p>',
				__( 'Your topic is awaiting moderation. Only you can see it for now.', 'anticonferences' )
			),
			$topic
		);

		if ( anticonferences_camp_ended( (int) $comment->comment_post_ID ) ) {
			$support_count = anticonferences_topic_get_support_count( $comment );

			$topic = str_replace( '<!-- .comment-content -->', sprintf( '<!-- .comment-content -->
				<div class="reply">
					<span class="ac-loved"></span>
					<span class="ac-support-count">%d</span>
				</div>
			', $support_count ), $topic );
		}

		if ( ! $echo ) {
			return $topic;
		}

		echo $topic;
	}
}
