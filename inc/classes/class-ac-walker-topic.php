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

		ob_start();
		parent::comment( $comment, $depth, $args );
		$topic = ob_get_clean();

		remove_filter( 'comment_reply_link', 'anticonferences_topic_support', 10, 3 );

		$this->topic( $topic, true );
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

		ob_start();
		parent::html5_comment( $comment, $depth, $args );
		$topic = ob_get_clean();

		remove_filter( 'comment_reply_link', 'anticonferences_topic_support', 10, 3 );

		$this->topic( $topic, true );
	}

	/**
	 * Replaces the awaiting approval message to fit topic context.
	 *
	 * @since  1.0.0
	 *
	 * @param  [type]  $topic [description]
	 * @param  boolean $echo     [description]
	 * @return [type]            [description]
	 */
	public function topic( $topic, $echo = false ) {
		$topic = preg_replace(
			'/<p class=\"comment-awaiting-moderation\">(.*?)<\/p>/',
			sprintf ( '<p class="topic-awaiting-moderation">%s</p>',
				__( 'Your topic is awaiting moderation. Only you can see it for now.', 'anticonferences' )
			),
			$topic
		);

		if ( ! $echo ) {
			return $topic;
		}

		echo $topic;
	}
}
