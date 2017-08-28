<?php
/**
 * Subject Walker
 *
 * Adapts the Comments Walker to our subject needs.
 *
 * @since  1.0.0
 *
 * @package AntiConférences
 * @subpackage inc\classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Walker_Comment' ) ) {
	require_once ABSPATH . WPINC . '/class-walker-comment.php';
}

class AC_Walker_Comment extends Walker_Comment {
	/**
	 * Outputs a single subject.
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function comment( $comment, $depth, $args ) {
		add_filter( 'comment_reply_link', 'anticonferences_subject_support', 10, 3 );

		ob_start();
		parent::comment( $comment, $depth, $args );
		$subject = ob_get_clean();

		remove_filter( 'comment_reply_link', 'anticonferences_subject_support', 10, 3 );

		$this->subject( $subject, true );
	}

	/**
	 * Outputs a single subject in the HTML5 format.
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		add_filter( 'comment_reply_link', 'anticonferences_subject_support', 10, 3 );

		ob_start();
		parent::html5_comment( $comment, $depth, $args );
		$subject = ob_get_clean();

		remove_filter( 'comment_reply_link', 'anticonferences_subject_support', 10, 3 );

		$this->subject( $subject, true );
	}

	/**
	 * Replaces the awaiting approval message to fit subject context.
	 *
	 * @since  1.0.0
	 *
	 * @param  [type]  $subject [description]
	 * @param  boolean $echo     [description]
	 * @return [type]            [description]
	 */
	public function subject( $subject, $echo = false ) {
		$subject = preg_replace(
			'/<p class=\"comment-awaiting-moderation\">(.*?)<\/p>/',
			sprintf ( '<p class="subject-awaiting-moderation">%s</p>',
				__( 'Votre sujet est en attente de modération. Vous seul pouvez le voir pour le moment.', 'anticonferences' )
			),
			$subject
		);

		if ( ! $echo ) {
			return $subject;
		}

		echo $subject;
	}
}
