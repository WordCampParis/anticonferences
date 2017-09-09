<?php
/**
 * Slack Payload
 *
 * @since  1.0.0
 *
 * @package  AntiConferences\inc\classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class AC_Slack_Payload {
	/**
	 * The Slack Attachments
	 *
	 * @var array
	 */
	public $attachments = array();

	/**
	 * The Constructor
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Comment $topic The topic Object.
	 */
	public function __construct( WP_Comment $topic ) {
		$camp = get_post_field( 'post_title', $topic->comment_post_ID );

		$title = sprintf(
			__( '[%1$s] New topic posted: %2$s', 'anticonferences' ),
			$camp,
			sprintf( '<%1$s|%2$s>',
				esc_url_raw( add_query_arg( array(
					'action' => 'editcomment',
					'c' => $topic->comment_ID
				), admin_url( 'comment.php' ) ) ),
				esc_html__( 'Moderate', 'anticonferences')
			)
		);

		$this->attachments[] = (object) array(
			'fallback' => $title,
			'pretext'  => $title,
			'color'    => '#006494',
			'fields'   => array(),
		);

		$this->attachments[0]->fields[] = (object) array(
			'title' => sprintf( __( 'Author: %s', 'anticonferences' ), esc_html( $topic->comment_author ) ),
			'value' => wp_trim_words( $topic->comment_content, 30 ),
			'short' => false,
		);
	}

	/**
	 * Encodes the Payload in JSON.
	 *
	 * @since  1.0.0
	 *
	 * @return string The payload object encoded in JSON.
	 */
	public function get_json() {
		return json_encode( $this );
	}
}
