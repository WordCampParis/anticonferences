<?php
/**
 * Plugin's functions.
 *
 * @since  1.0.0
 *
 * @package  AntiConferences\inc
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Gets the default Camps Metadatas.
 *
 * @since  1.0.0
 *
 * @return array Camp Metadatas.
 */
function anticonferences_get_default_metas() {
	return array(
		'_camp_closing_date' => array(
			'sanitize_callback'  => 'anticonferences_sanitize_metas',
			'type'               => 'string',
			'description'        => __( 'Closing date for the suggested topics.', 'anticonferences' ),
			'single'             => true,
			'show_in_rest'       => array(
				'name' => 'closing_date',
			),
		),
		'_camp_votes_amount' => array(
			'sanitize_callback'  => 'anticonferences_sanitize_metas',
			'type'               => 'integer',
			'description'        => __( 'Number of votes each user can give to a topic.', 'anticonferences' ),
			'single'             => true,
			'show_in_rest'       => array(
				'name' => 'votes_amount',
			),
		),
		'_camp_slack_webhook' => array(
			'sanitize_callback'   => 'anticonferences_sanitize_metas',
			'type'                => 'string',
			'description'         => __( 'Notify about pending topics using Slack.', 'anticonferences' ),
			'single'              => true,
		),
	);
}

/**
 * Registers Camps/Posts Metadatas.
 *
 * @since  1.0.0
 *
 * @param  string $post_type The post type to register Metadatas for.
 */
function anticonferences_register_post_metas( $post_type = 'camps' ) {
	$default_metas = anticonferences_get_default_metas();

	foreach ( $default_metas as $key_meta => $meta_args ) {
		register_meta(
			$post_type,
			$key_meta,
			$meta_args
		);
	}
}

/**
 * Registers Camps Post type, Specific supports and Metadatas.
 *
 * @since 1.0.0
 */
function anticonferences_register_objects() {
	// Post type
	$labels = array(
		'name'                  => __( 'Camps',                               'anticonferences' ),
		'menu_name'             => _x( 'AntiConférences', 'Main Plugin menu', 'anticonferences' ),
		'all_items'             => __( 'All camps',                           'anticonferences' ),
		'singular_name'         => __( 'Camp',                                'anticonferences' ),
		'add_new'               => __( 'Add',                                 'anticonferences' ),
		'add_new_item'          => __( 'Add a new camp',                      'anticonferences' ),
		'edit_item'             => __( 'Edit camp',                           'anticonferences' ),
		'new_item'              => __( 'New camp',                            'anticonferences' ),
		'view_item'             => __( 'View camp',                           'anticonferences' ),
		'search_items'          => __( 'Search camps',                        'anticonferences' ),
		'not_found'             => __( 'No camps found',                      'anticonferences' ),
		'not_found_in_trash'    => __( 'No camps found in trash',             'anticonferences' ),
		'insert_into_item'      => __( 'Insert into camp',                    'anticonferences' ),
		'uploaded_to_this_item' => __( 'Attached to camp',                    'anticonferences' ),
		'filter_items_list'     => __( 'Filter camps',                        'anticonferences' ),
		'items_list_navigation' => __( 'Camps navigation',                    'anticonferences' ),
		'items_list'            => __( 'Camps list',                          'anticonferences' ),
		'name_admin_bar'        => _x( 'Camp', 'Name Admin Bar',              'anticonferences' ),
	);

	$params = array(
		'labels'               => $labels,
		'description'          => __( 'Camps inform about the rules of their noconferences', 'anticonferences' ),
		'public'               => true,
		'query_var'            => 'ac_camp',
		'rewrite'              => array(
			'slug'             => 'a-c/camp',
			'with_front'       => false
		),
		'has_archive'          =>'a-c',
		'exclude_from_search'  => true,
		'show_in_nav_menus'    => true,
		'show_in_admin_bar'    => current_user_can( 'edit_posts' ),
		'register_meta_box_cb' => 'anticonferences_admin_register_metabox',
		'menu_icon'            => 'dashicons-marker',
		'supports'             => array( 'title', 'editor', 'comments', 'revisions', 'post-formats', 'thumbnail' ),
		'map_meta_cap'         => true,
		'delete_with_user'     => false,
		'can_export'           => true,
		'show_in_rest'         => true,
		'rest_base'            => 'ac-camps',
	);

	register_post_type( 'camps', $params );
	add_post_type_support( 'camps', 'post-formats', array( 'aside', 'quote', 'status' ) );

	// Custom Fields
	anticonferences_register_post_metas( 'camps' );
}
add_action( 'init', 'anticonferences_register_objects' );

/**
 * Checks Camps Post type support.
 *
 * This allowes to check for "non boolean" features.
 *
 * @since  1.0.0
 *
 * @param  string $feature The name of the feature.
 * @return mixed           The value for the feature.
 */
function anticonferences_get_support( $feature = '' ) {
	$supports = get_all_post_type_supports( 'camps' );

	if ( ! isset( $supports[ $feature ] ) ) {
		return false;
	}

	if ( 'post-formats' === $feature && is_array( $supports[ $feature ] ) ) {
		return reset( $supports[ $feature ] );
	}

	return $supports[ $feature ];
}

/**
 * Sanitizes the value for the registered Camps Metadatas.
 *
 * @since  1.0.0
 *
 * @param  mixed $value     The value of the Metadata.
 * @param  string $meta_key The key of the Metadata.
 * @return mixed            The sanitized value for the Metadata.
 */
function anticonferences_sanitize_metas( $value = '', $meta_key = '' ) {
	if ( '_camp_closing_date' === $meta_key ) {
		if ( ! empty( $value ) ) {
			$value = strtotime( $value );
		}

	} elseif ( '_camp_votes_amount' === $meta_key ) {
		$value = absint( $value );

	} elseif ( '_camp_slack_webhook' === $meta_key  ) {
		$value = esc_url_raw( $value );
	}

	return $value;
}

/**
 * Temporarly registers Camps Metadatas to the Post's post type.
 *
 * @since  1.0.0
 *
 * @param  array  $data The post data to be inserted.
 * @return array        The post data to be inserted.
 */
function anticonferences_register_temporary_post_metas( $data = array() ) {
	// Add post metas temporarly.
	if ( ! empty( $data['post_type'] ) && 'camps' === $data['post_type'] ) {
		anticonferences_register_post_metas( 'post' );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'anticonferences_register_temporary_post_metas', 10, 1 );

/**
 * Unregisters temporary Camps Metadatas from the Post's post type.
 *
 * @since  1.0.0
 */
function anticonferences_unregister_temporary_post_metas() {
	$default_metas = anticonferences_get_default_metas();

	// Remove the temporary post metas.
	foreach ( array_keys( $default_metas ) as $meta_key ) {
		unregister_meta_key( 'post', $meta_key );
	}
}
add_action( 'save_post_camps', 'anticonferences_unregister_temporary_post_metas' );

/**
 * Loads a Camps template part.
 *
 * @since  1.0.0
 *
 * @param  string $slug The template slug.
 * @param  string $name The template name.
 */
function anticonferences_template_part( $slug, $name = '' ) {
	$templates = array();
	$name = (string) $name;

	if ( '' !== $name ) {
		$templates[] = sprintf( '%1$s-%2$s.php', $slug, $name );
	}

	$templates[] = sprintf( '%s.php', $slug );
	$located = locate_template( $templates, false );

	if ( ! $located ) {
		$located = anticonferences()->tpl_dir . reset( $templates );

		if ( ! file_exists( $located ) ) {
			return;
		}
	}

	load_template( $located, false );
}

/**
 * Get minified suffix.
 *
 * @since 1.0.0
 *
 * @return string The minified suffix.
 */
function anticonferences_min_suffix() {
	$min = 'min.';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	/**
	 * Use this filter to toggle the minified suffix.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $min The minified suffix.
	 */
	return apply_filters( 'anticonferences_min_suffix', $min );
}

/**
 * Gets a template asset (CSS or JavaScript).
 *
 * @since  1.0.0
 *
 * @param  string $context Whether the assets needs to be loaded in Admin or Front.
 * @param  string $type    Whether it's a `css` or `js` asset.
 * @return string          The absolute URL to the asset.
 */
function anticonferences_get_asset( $context = 'front', $type = 'css' ) {
	$type    = anticonferences_min_suffix() . $type;
	$located = locate_template( "anticonferences/{$context}.{$type}", false );

	if ( ! $located ) {
		$located = anticonferences()->tpl_dir . "{$context}.{$type}";
	}

	// Make sure Microsoft is happy...
	$slashed_located     = str_replace( '\\', '/', $located );
	$slashed_content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );
	$slashed_plugin_dir  = str_replace( '\\', '/', anticonferences()->dir );

	// Should allways be the case for regular configs
	if ( false !== strpos( $slashed_located, $slashed_content_dir ) ) {
		$located = str_replace( $slashed_content_dir, content_url(), $slashed_located );

	// If not, Plugin might be symlinked, so let's try this
	} else {
		$located = str_replace( $slashed_plugin_dir, anticonferences()->url, $slashed_located );
	}

	return $located;
}

/**
 * Overrides the regular comments template for the Topics one when needed.
 *
 * @since  1.0.0
 *
 * @return string Absolute path to the Topics template.
 */
function anticonferences_topics_template() {
	// Remove the temporary filter immediately.
	remove_filter( 'comments_template', 'anticonferences_topics_template', 0 );

	return anticonferences()->tpl_dir . 'topics-template.php';
}

/**
 * Disables unused TinyMCE buttons.
 *
 * @since  1.0.0
 *
 * @param  array  $buttons The list of TinyMCE buttons.
 * @return array           The list of TinyMCE buttons.
 */
function anticonferences_mce_buttons( $buttons = array() ) {
	return array_diff( $buttons, array(
		'wp_more',
		'spellchecker',
		'wp_adv',
		'fullscreen',
		'formatselect',
		'bullist',
		'numlist',
		'alignleft',
		'alignright',
		'aligncenter',
		'link',
		'unlink',
	) );
}

/**
 * Sets the comment's type when a new topic or a new support is submitted.
 *
 * @since  1.0.0
 *
 * @param  array  $comment_data The posted datas for the comment.
 * @return array                The posted datas for the comment.
 */
function anticonferences_preprocess_comment( $comment_data = array() ) {
	if ( isset( $_POST['ac_comment_type'] ) && in_array( $_POST['ac_comment_type'], array( 'ac_topic', 'ac_support' ), true ) ) {
		$comment_data['comment_type'] = $_POST['ac_comment_type'];
	}

	return $comment_data;
}
add_filter( 'preprocess_comment', 'anticonferences_preprocess_comment', 10, 1 );

/**
 * Base64 encodes a string removing problematic characters when used in URLs.
 *
 * @since  1.0.0
 *
 * @param  string $string The string to encode.
 * @return string         The encoded string.
 */
function anticonferences_urlsafe_b64encode( $string ) {
    $data = base64_encode( $string );
    $data = str_replace( array( '+','/','=' ), array( '-','_','' ), $data );
    return $data;
}

/**
 * Base64 decodes a string removing problematic characters when used in URLs.
 *
 * @since  1.0.0
 *
 * @param  string $string The string to decode.
 * @return string         The decoded string.
 */
function anticonferences_urlsafe_b64decode( $string ) {
    $data = str_replace( array( '-','_' ),array( '+','/' ),$string );
    $mod4 = strlen( $data ) % 4;
    if ( $mod4 ) {
        $data .= substr( '====', $mod4 );
    }

    return base64_decode( $data );
}

/**
 * Checks if a user has validated his email.
 *
 * @since  1.0.0
 *
 * @param  string $email The email to check.
 * @return integer       The number of emails found.
 */
function anticonferences_support_awaiting_validation( $email = '' ) {
	global $wpdb;

	/**
	 * @todo cache
	 */

	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->commentmeta} WHERE meta_key='_ac_support_email' AND meta_value = %s", $email ) );
}

/**
 * Checks if a support a user posted can be validated right away.
 *
 * @since  1.0.0
 *
 * @param  string $email The user email.
 * @return string        1 (Approved) or O (Pending).
 */
function anticonferences_support_allowed( $email = '' ) {
	global $wpdb;

	// Logged in user email doesn't need to be checked.
	if ( is_user_logged_in() ) {
		return 1;
	}

	/**
	 * @todo cache
	 */

	return $wpdb->get_var( $wpdb->prepare( "SELECT comment_approved FROM {$wpdb->comments} WHERE comment_type = 'ac_support' AND comment_author_email = %s LIMIT 1", $email ) );
}

/**
 * Checks if an email reached the support's quota for the Camp.
 *
 * @since  1.0.1
 *
 * @param  string  $email    The user email.
 * @param  integer $post_id  The Camp ID.
 * @return boolean           True if the quota is reached. False otherwise.
 */
function anticonferences_support_quota_reached( $email = '', $post_id = 0, $new_support = 0 ) {
	global $wpdb;

	if ( ! $post_id ) {
		return false;
	}

	$quota = (int) get_post_meta( $post_id, '_camp_votes_amount', true );

	if ( ! $quota ) {
		return false;
	}

	/**
	 * @todo cache
	 */

	$support_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(comment_content) FROM {$wpdb->comments} WHERE comment_type = 'ac_support' AND comment_author_email = %s AND comment_post_ID = %d", $email, $post_id ) );

	return $support_count + $new_support > $quota;
}

/**
 * Allowes dupe supports and supports to be posted more quickly.
 *
 * @since  1.0.0
 *
 * @param  boolean $retval Whether dupes or flooding is allowed.
 * @return boolean         Whether dupes or flooding is allowed.
 */
function anticonferences_support_can_flood_and_dupe( $retval ) {
	if ( isset( $_POST['ac_comment_type'] ) && 'ac_support' === $_POST['ac_comment_type'] ) {
		$retval = false;
	}

	return $retval;
}
add_filter( 'duplicate_comment_id', 'anticonferences_support_can_flood_and_dupe', 11, 1 );
add_filter( 'wp_is_comment_flood',  'anticonferences_support_can_flood_and_dupe', 11, 1 );

/**
 * Customize the Comments approval process for Topics and Supports.
 *
 * @since  1.0.0
 *
 * @param  integer $approved     Whether the comment should be approved or hold.
 * @param  array   $comment_data The comment's data.
 * @return integer               Whether the comment should be approved or hold.
 */
function anticonferences_pre_comment_approved( $approved = 0, $comment_data = array() ) {
	if ( ! isset( $comment_data['comment_type'] ) ) {
		return $approved;
	}

	// New topics require a moderation step.
	if ( 'ac_topic' === $comment_data['comment_type'] ) {
		$approved = 0;

	// New supports may require a validate step.
	} elseif ( 'ac_support' === $comment_data['comment_type'] ) {
		$votes = (int) $comment_data['comment_content'];

		// The support quota is reached !
		if ( anticonferences_support_quota_reached( $comment_data['comment_author_email'], $comment_data['comment_post_ID'], $votes ) ) {
			return new WP_Error( 'support_quota_reached', sprintf(
				__( 'You do not have enough votes left to add %s more.', 'anticonferences' ),
				number_format_i18n( $votes )
			), 403 );

		// The user hadn't validated his email yet!
		} elseif ( 0 !== (int) anticonferences_support_awaiting_validation( anticonferences_urlsafe_b64encode( $comment_data['comment_author_email'] ) ) ) {
			$approved = 0;

		// The user has at least a support validated and can safely vote.
		} else {
			$approved = (int) anticonferences_support_allowed( $comment_data['comment_author_email'] );
		}
	}

	return $approved;
}
add_filter( 'pre_comment_approved', 'anticonferences_pre_comment_approved', 10, 2 );

/**
 * Customizes the comment form to be used to post Topics.
 *
 * @since  1.0.0
 *
 * @param  array  $fields The comment form fields.
 * @return array          The topic form fields.
 */
function anticonferences_topic_form_fields( $fields = array() ) {
	unset( $fields['fields']['url'] );
	$fields['comment_field'] = anticonferences_topic_get_editor();

	return $fields;
}

/**
 * Checks if Topics and Supports submission is now ended.
 *
 * @since  1.0.1
 *
 * @param  integer $post_id The ID of the Camp.
 * @return boolean          True if Topics and Supports submission is ended.
 *                          False otherwise.
 */
function anticonferences_camp_ended( $post_id = 0 ) {
	$closed   = false;

	if ( ! $post_id ) {
		return $closed;
	}

	$end_date = (int) get_post_meta( $post_id, '_camp_closing_date', true );

	if ( ! $end_date ) {
		return $closed;
	}

	$now = strtotime( date_i18n( 'Y-m-d H:i' ) );

	if ( $end_date <= $now ) {
		$closed = true;
	}

	return $closed;
}

/**
 * Forces the comments to be open for Camps till the end date is reached.
 *
 * @since  1.0.0
 *
 * @param  boolean $return  Whether comments are open.
 * @param  integer $post_id The post ID.
 * @return boolean          Whether comments are open.
 */
function anticonferences_comments_open( $return = false, $post_id = 0 ) {
	$post = get_queried_object();

	if ( ! isset( $post->ID ) || (int) $post_id !== (int) $post->ID ) {
		$post = get_post( $post_id );
	}

	if ( 'camps' === get_post_type( $post ) ) {
		// Temporary filters
		if ( is_single() ) {
			add_filter( 'comments_template',  'anticonferences_topics_template', 0 );
			add_filter( 'comment_id_fields',  'anticonferences_topic_type'         );
		}

		$return = ! anticonferences_camp_ended( $post->ID );
	}

	return $return;
}
add_filter( 'comments_open', 'anticonferences_comments_open', 10, 2 );

/**
 * Edits the query to count comments to match Camps context.
 *
 * @since  1.0.0
 *
 * @param  string $query The SQL query.
 * @return string        The SQL query.
 */
function anticonferences_all_comments_count_query( $query = '' ) {
	global $wpdb;
	$ac = anticonferences();

	// Remove the temporary filter immediately.
	remove_filter( 'query', 'anticonferences_all_comments_count_query' );

	$comments_count_query = str_replace( array( "\n", "\t", "\r" ), '', $query );
	$comments_count_query = trim( $comments_count_query );
	$sql = array(
		'select'  => 'SELECT comment_approved, COUNT( * ) AS total',
		'from'    => "FROM {$wpdb->comments}",
		'groupby' => 'GROUP BY comment_approved',
	);

	if ( $comments_count_query === join( '', $sql ) ) {
		$query = str_replace( $sql['groupby'], sprintf( 'WHERE comment_type NOT IN( "ac_topic", "ac_support" ) %s', $sql['groupby'] ), $query );

	// On the edit-comment or edit camps screen, make sure support are not counted.
	} elseif ( ( isset( $_GET['comment_status'] ) && ! empty( $_GET['p'] ) ) || ! empty( $ac->camp_topics ) ) {
		$id = 0;
		if ( ! empty( $_GET['p'] ) ) {
			$id = $_GET['p'];
		} elseif ( ! empty( $ac->camp_topics ) ) {
			$id = $ac->camp_topics;
		}

		$sql_p = array_merge( array_slice( $sql, 0, 2, true ), array(
			'where' => $wpdb->prepare( 'WHERE comment_post_ID = %d', $id ),
		), array_slice( $sql, 2, 1, true ) );

		if ( $comments_count_query === join( '', $sql_p ) ) {
			$query = str_replace( $sql['groupby'], sprintf( 'AND comment_type != "ac_support" %s', $sql['groupby'] ), $query );
		}
	}

	return $query;
}

/**
 * Decides whether the Comments count query should be overriden or not.
 *
 * @since  1.0.0
 *
 * @param  array   $stats   The comments statistics.
 * @param  integer $post_id The Post ID statistics relate to.
 * @return array            The comments statistics.
 */
function anticonferences_count_all_comments( $stats = array(), $post_id = 0 ) {
	$screen = null;
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
	}

	$add_filter = ! $post_id;

	if ( ( isset( $screen->id ) && 'edit-comments' === $screen->id && 'camps' === $screen->post_type ) || ! empty( anticonferences()->camp_topics ) ) {
		$add_filter = true;
	}

	// Filter the query to remove AntiConférences comment types.
	if ( $add_filter ) {
		add_filter( 'query', 'anticonferences_all_comments_count_query', 10, 1 );
	}

	return $stats;
}
add_filter( 'wp_count_comments', 'anticonferences_count_all_comments', 10, 2 );

/**
 * Edits the Comment's query queryvars when needed.
 *
 * @since  1.0.0
 *
 * @param  WP_Comment_Query $comment_query The comment query object.
 */
function anticonferences_parse_comment_query( WP_Comment_Query $comment_query ) {
	$not_in = array( 'ac_topic', 'ac_support' );

	if ( ! $comment_query->query_vars['post_ID'] && ! $comment_query->query_vars['post_id'] && ! in_array( $comment_query->query_vars['type'], $not_in, true ) ) {

		if ( ! $comment_query->query_vars['type__not_in'] || ! is_array( $comment_query->query_vars['type__not_in'] ) ) {
			$comment_query->query_vars['type__not_in'] = explode( ',', $comment_query->query_vars['type__not_in'] );
		}

		$comment_query->query_vars['type__not_in'] = array_merge( (array) $comment_query->query_vars['type__not_in'], $not_in );
	} elseif ( is_singular( 'camps' ) ) {
		$orderby = get_query_var( 'orderby' );
		$order   = get_query_var( 'order' );

		if ( $order && $orderby && ! $comment_query->query_vars['parent'] ) {
			$supported_orders = anticonferences_get_order_options();

			if ( isset( $supported_orders[ $orderby ] ) ) {
				if ( in_array( $orderby, array( 'date_asc', 'date_desc' ), true ) ) {
					$comment_query->query_vars['orderby'] = 'comment_date_gmt';
				} elseif ( isset( $supported_orders[ $orderby ]['meta_key'] ) ) {
					$comment_query->query_vars['meta_key'] = $supported_orders[ $orderby ]['meta_key'];
					$comment_query->query_vars['orderby'] = 'meta_value_num';
				}

				$comment_query->query_vars['order'] = $order;
			}
		}

	}
}
add_action( 'parse_comment_query', 'anticonferences_parse_comment_query' );

/**
 * Notifies a support author he needs to validate it.
 *
 * @since  1.0.0
 *
 * @param  WP_Comment $support The support object.
 */
function anticonferences_notify_support_author( WP_Comment $support ) {
	$blogname        = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	$notify_message  = __( 'Please click on the following link to validate your support to the topic:', 'anticonferences' ) . "\r\n";
	$notify_message .= add_query_arg( 'key-support', anticonferences_urlsafe_b64encode( $support->comment_author_email ), get_post_permalink( $support->comment_post_ID ) ) . "\r\n";

	$camp_title = get_post_field( 'post_title', $support->comment_post_ID );
	$subject    = sprintf( __('[%s] Support validation', 'anticonferences' ), $camp_title );

	@wp_mail( $support->comment_author_email, wp_specialchars_decode( $subject ), $notify_message );
}

/**
 * Notifies a Camp moderator or a Slack channel about a new pending topic.
 *
 * @since  1.0.0
 *
 * @param  boolean $maybe_notify Whether to notify about the pending topic or not.
 * @param  integer $comment_ID   The topic ID.
 * @return boolean               Whether to notify about the pending topic or not.
 */
function anticonferences_notify_moderator( $maybe_notify = true, $comment_ID = 0 ) {
	$topic = get_comment( $comment_ID );

	if ( ! isset( $topic->comment_type ) ) {
		return $maybe_notify;
	}

	if ( 'ac_topic' === $topic->comment_type ) {
		$slack_webhook = get_post_meta( $topic->comment_post_ID, '_camp_slack_webhook', true );

		if ( ! $slack_webhook ) {
			return $maybe_notify;
		}

		$payload = new AC_Slack_Payload( $topic );

		wp_remote_post( $slack_webhook, array(
			'body' => $payload->get_json(),
		) );

		$maybe_notify = false;

	// The first Support needs to be validated by an email check.
	} elseif ( 'ac_support' === $topic->comment_type ) {
		if ( ! (int) $topic->comment_approved ) {
			$support = clone $topic;

			$base_64_email = anticonferences_urlsafe_b64encode( $support->comment_author_email );

			if ( ! anticonferences_support_awaiting_validation( $base_64_email ) ) {
				update_comment_meta( $comment_ID, '_ac_support_email', $base_64_email );
				anticonferences_notify_support_author( $support );
			}
		}

		$maybe_notify = false;
	}

	return $maybe_notify;
}
add_filter( 'notify_moderator', 'anticonferences_notify_moderator', 10, 2 );

/**
 * Disable notifications for the Post author when a comment is a topic or a support.
 *
 * @since  1.0.0
 *
 * @param  boolean $maybe_notify Whether to notify the post author or not.
 * @param  integer $comment_ID   The comment ID.
 * @return [type]                Whether to notify the post author or not.
 */
function anticonferences_notify_post_author( $maybe_notify = true, $comment_ID = 0 ) {
	$ac = get_comment( $comment_ID );

	if ( ! isset( $ac->comment_type ) ) {
		return $maybe_notify;
	}

	if ( 'ac_topic' === $ac->comment_type || 'ac_support' === $ac->comment_type ) {
		$maybe_notify = false;
	}

	return $maybe_notify;
}
add_filter( 'notify_post_author', 'anticonferences_notify_post_author', 10, 2 );

/**
 * Validates user supports.
 *
 * @since  1.0.0
 */
function anticonferences_template_redirect() {
	if ( ! is_singular( 'camps' ) ){
		return;
	}

	if ( ! isset( $_GET['key-support'] ) ) {
		return;
	}

	if ( delete_metadata( 'comment', null, '_ac_support_email' , $_GET['key-support'], true ) ) {
		$back_link = remove_query_arg( 'key-support' );
		$email     = anticonferences_urlsafe_b64decode( $_GET['key-support'] );

		$supports = get_comments( array(
			'author_email'       => $email,
			'type'               => 'ac_support',
			'status'             => 'hold',
		) );

		foreach ( $supports as $support ) {
			wp_set_comment_status( $support->comment_ID, 'approve' );
		}

		wp_die( sprintf(
			__( 'Your email and pending support(s) has been validated. All your future supports to topics will automatically be published. %s', 'anticonferences' ),
			'<a href="' . esc_url( $back_link ). '">' . esc_html__( 'View topics', 'anticonferences' ) . '</a>.'
		) );
	} else {
		wp_die( __( 'An error occured while validating your email. It looks like it\'s been already validated.', 'anticonferences' ) );
	}
}
add_action( 'template_redirect', 'anticonferences_template_redirect', 8 );

/**
 * Redirects the user to the topics he supported.
 *
 * @since  1.0.0
 *
 * @param  string     $redirect The URL to redirect the user to.
 * @param  WP_Comment $support  The Support object.
 * @return string     $redirect The URL to redirect the user to.
 */
function anticonferences_topic_redirect( $redirect = '', WP_Comment $support ) {
	if ( 'ac_support' === $support->comment_type ) {
		$redirect = get_comment_link( $support->comment_parent );
	}

	return $redirect;
}
add_filter( 'comment_post_redirect', 'anticonferences_topic_redirect', 10, 2 );

/**
 * Updates the Supports count adding the new one.
 *
 * @since  1.0.0
 *
 * @param  integer    $comment_ID The Topic ID.
 * @param  WP_Comment $support    The support object.
 */
function anticonferences_increment_support_count( $comment_ID, WP_Comment $support ) {
	if ( empty( $support->comment_approved ) ) {
		return;
	}

	$count  = (int) get_comment_meta( $support->comment_parent, '_ac_support_count', true );
	$count += (int) $support->comment_content;

	update_comment_meta( $support->comment_parent, '_ac_support_count', $count );
}
add_action( 'comment_approved_ac_support', 'anticonferences_increment_support_count', 10, 2 );
add_action( 'wp_insert_comment',           'anticonferences_increment_support_count', 10, 2 );

/**
 * Updates the Supports count removing the trashed/spammed one.
 *
 * @since  1.0.0
 *
 * @param  integer    $comment_ID The Topic ID.
 * @param  WP_Comment $support    The support object.
 */
function anticonferences_decrement_support_count( $comment_ID, WP_Comment $support ) {
	$count  = (int) get_comment_meta( $support->comment_parent, '_ac_support_count', true );
	$count -= (int) $support->comment_content;

	if ( 0 > $count ) {
		delete_comment_meta( $support->comment_parent, '_ac_support_count' );
	} else {
		update_comment_meta( $support->comment_parent, '_ac_support_count', $count );
	}
}
add_action( 'comment_trash_ac_support', 'anticonferences_decrement_support_count', 10, 2 );
add_action( 'comment_spam_ac_support',  'anticonferences_decrement_support_count', 10, 2 );

/**
 * Enqueues the displayed Camp assets.
 *
 * @since  1.0.0
 */
function anticonferences_enqueue_assets() {
	if ( ! is_singular( 'camps' ) ){
		return;
	}

	wp_enqueue_style( 'ac-front-style', anticonferences_get_asset(), array(), anticonferences()->version );
	wp_enqueue_script( 'ac-front-script', anticonferences_get_asset( 'front', 'js' ), array( 'jquery' ), anticonferences()->version, true );

	$post_id = get_queried_object_id();
	if ( $post_id ) {
		wp_localize_script( 'ac-front-script', 'AntiConferences', array(
			'votes' => (int) get_post_meta( $post_id, '_camp_votes_amount', true ),
		) );
	}

	if ( get_option( 'thread_comments' ) ) {
		wp_dequeue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'anticonferences_enqueue_assets', 20 );

/**
 * Gets the supports count for a Topic.
 *
 * @since  1.0.0
 *
 * @param  WP_Comment $comment The Topic object.
 * @return integer             The number of supports for the Topic.
 */
function anticonferences_topic_get_support_count( WP_Comment $comment ) {
	// Only count the approved supports
	$array_count = wp_filter_object_list( $comment->get_children( array( 'type' => 'ac_support' ) ), array( 'comment_approved' => 1 ), 'and','comment_content' );
	$array_count = array_map( 'absint', $array_count );

	if ( ! $array_count ) {
		return (int) get_comment_meta( $comment->comment_ID, '_ac_support_count', true );
	}

	return array_sum( $array_count );
}

/**
 * Gets the Camp's topics order options.
 *
 * @since  1.0.0
 *
 * @return array The Camp's topics order options.
 */
function anticonferences_get_order_options() {
	$order_options =  array(
		'date_asc'       => array(
			'label' => __( 'Oldest', 'anticonferences' ),
			'order' => 'ASC',
		),
		'date_desc'      => array(
			'label' => __( 'Newest', 'anticonferences' ),
			'order' => 'DESC',
		),
		'support_count'  => array(
			'label'    => __( 'Most supported', 'anticonferences' ),
			'order'    => 'DESC',
			'meta_key' => '_ac_support_count',
		),
	);

	/**
	 * Use this filter to add order options.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $order_options The Camp's topics order options.
	 */
	return apply_filters( 'anticonferences_get_order_options', $order_options );
}

/**
 * Loads translation if needed.
 *
 * @since  1.0.0
 */
function anticonferences_load_textdomain() {
	$ac = anticonferences();

	load_plugin_textdomain( $ac->domain, false, dirname( $ac->basename ) . '/languages' );
}
add_action( 'plugins_loaded', 'anticonferences_load_textdomain', 7 );
