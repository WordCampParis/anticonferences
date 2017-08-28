<?php
/**
 * Plugin's functions.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function anticonferences_get_default_metas() {
	return array(
		'_festival_closing_date' => array(
			'sanitize_callback' => 'anticonferences_sanitize_metas',
			'type'              => 'string',
			'description'       => __( 'Date de clôture pour le dépôt des sujets', 'anticonferences' ),
			'single'            => true,
			'show_in_rest'      => array(
				'name' => 'closing_date',
			),
		),
		'_festival_votes_amount' => array(
			'sanitize_callback' => 'anticonferences_sanitize_metas',
			'type'              => 'integer',
			'description'       => __( 'Nombre de votes dont les utilisateurs disposent', 'anticonferences' ),
			'single'            => true,
			'show_in_rest'      => array(
				'name' => 'votes_amount',
			),
		),
	);
}

function anticonferences_register_post_metas( $post_type = 'festivals' ) {
	$default_metas = anticonferences_get_default_metas();

	foreach ( $default_metas as $key_meta => $meta_args ) {
		register_meta(
			$post_type,
			$key_meta,
			$meta_args
		);
	}
}

function anticonferences_register_objects() {
	// Post type
	$labels = array(
		'name'                  => __( 'Festivals',                               'anticonferences' ),
		'menu_name'             => _x( 'AntiConférences', 'Main Plugin menu',     'anticonferences' ),
		'all_items'             => __( 'Tous les festivals',                      'anticonferences' ),
		'singular_name'         => __( 'Festival',                                'anticonferences' ),
		'add_new'               => __( 'Ajouter',                                 'anticonferences' ),
		'add_new_item'          => __( 'Ajouter un nouveau festival',             'anticonferences' ),
		'edit_item'             => __( 'Modifier le festival',                    'anticonferences' ),
		'new_item'              => __( 'Nouveau festival',                        'anticonferences' ),
		'view_item'             => __( 'Voir le festival',                        'anticonferences' ),
		'search_items'          => __( 'Rechercher un festival',                  'anticonferences' ),
		'not_found'             => __( 'Aucun festival trouvé',                   'anticonferences' ),
		'not_found_in_trash'    => __( 'Aucun festival trouvé dans la corbeille', 'anticonferences' ),
		'insert_into_item'      => __( 'Insérer dans le festival',                'anticonferences' ),
		'uploaded_to_this_item' => __( 'Attaché à ce festival',                   'anticonferences' ),
		'filter_items_list'     => __( 'Filtrer la liste des festivals',          'anticonferences' ),
		'items_list_navigation' => __( 'Navigation de la liste des festivals',    'anticonferences' ),
		'items_list'            => __( 'Liste des festivals',                     'anticonferences' ),
		'name_admin_bar'        => _x( 'Festival', 'Name Admin Bar',              'anticonferences' ),
	);

	$params = array(
		'labels'               => $labels,
		'description'          => __( 'Un festival présente les règles du jeu et la durée des AntiConférences', 'anticonferences' ),
		'public'               => true,
		'query_var'            => 'ac_festival',
		'rewrite'              => array(
			'slug'             => 'a-c/festival',
			'with_front'       => false
		),
		'has_archive'          =>'a-c',
		'exclude_from_search'  => true,
		'show_in_nav_menus'    => true,
		'show_in_admin_bar'    => current_user_can( 'edit_posts' ),
		'register_meta_box_cb' => 'anticonferences_admin_register_metabox',
		'menu_icon'            => 'dashicons-marker',
		'supports'             => array( 'title', 'editor', 'comments', 'revisions', 'post-formats' ),
		'map_meta_cap'         => true,
		'delete_with_user'     => false,
		'can_export'           => true,
		'show_in_rest'         => true,
		'rest_base'            => 'ac-festivals',
	);

	register_post_type( 'festivals', $params );
	add_post_type_support( 'festivals', 'post-formats', array( 'aside', 'quote', 'status' ) );

	// Custom Fields
	anticonferences_register_post_metas( 'festivals' );
}
add_action( 'init', 'anticonferences_register_objects' );

function anticonferences_get_support( $feature = '' ) {
	$supports = get_all_post_type_supports( 'festivals' );

	if ( ! isset( $supports[ $feature ] ) ) {
		return false;
	}

	if ( 'post-formats' === $feature && is_array( $supports[ $feature ] ) ) {
		return reset( $supports[ $feature ] );
	}

	return $supports[ $feature ];
}

function anticonferences_sanitize_metas( $value = '', $meta_key = '' ) {
	if ( '_festival_closing_date' === $meta_key ) {
		if ( ! empty( $value ) ) {
			$value = strtotime( $value );
		}

	} elseif ( '_festival_votes_amount' === $meta_key ) {
		$value = absint( $value );
	}

	return $value;
}

function anticonferences_register_temporary_post_metas( $data = array() ) {
	// Add post metas temporarly.
	if ( ! empty( $data['post_type'] ) && 'festivals' === $data['post_type'] ) {
		anticonferences_register_post_metas( 'post' );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'anticonferences_register_temporary_post_metas', 10, 1 );

function anticonferences_unregister_temporary_post_metas() {
	$default_metas = anticonferences_get_default_metas();

	// Remove the temporary post metas.
	foreach ( array_keys( $default_metas ) as $meta_key ) {
		unregister_meta_key( 'post', $meta_key );
	}
}
add_action( 'save_post_festivals', 'anticonferences_unregister_temporary_post_metas' );

/**
 * Probably not needed..
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

function anticonferences_subjects_template() {
	// Remove the temporary filter immediately.
	remove_filter( 'comments_template', 'anticonferences_subjects_template', 0 );

	return anticonferences()->tpl_dir . 'subjects-template.php';
}

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
	) );
}

function anticonferences_preprocess_comment( $comment_data = array() ) {
	if ( isset( $_POST['ac_comment_type'] ) && in_array( $_POST['ac_comment_type'], array( 'ac_subject', 'ac_support' ), true ) ) {
		$comment_data['comment_type'] = $_POST['ac_comment_type'];
	}

	return $comment_data;
}
add_filter( 'preprocess_comment', 'anticonferences_preprocess_comment', 10, 1 );

function anticonferences_subject_form_fields( $fields = array() ) {
	unset( $fields['fields']['url'] );
	$fields['comment_field'] = anticonferences_subject_get_editor();

	return $fields;
}

function anticonferences_comments_open( $return = false, $post_id = 0 ) {
	$post = get_queried_object();

	if ( ! isset( $post->ID ) || (int) $post_id !== (int) $post->ID ) {
		$post = get_post( $post_id );
	}

	if ( 'festivals' === get_post_type( $post ) ) {
		// Temporary filters
		if ( is_single() ) {
			add_filter( 'comments_template',  'anticonferences_subjects_template',  0    );
			add_filter( 'comment_id_fields',  'anticonferences_subject_type'             );
		}

		$return = true;
	}

	return $return;
}
add_filter( 'comments_open', 'anticonferences_comments_open', 10, 2 );

function anticonferences_all_comments_count_query( $query = '' ) {
	global $wpdb;

	// Remove the temporary filter immediately.
	remove_filter( 'query', 'anticonferences_all_comments_count_query' );

	$comments_count_query = str_replace( array( "\n", "\t" ), '', $query );
	$comments_count_query = trim( $comments_count_query );
	$sql = array(
		'select'  => 'SELECT comment_approved, COUNT( * ) AS total',
		'from'    => "FROM {$wpdb->comments}",
		'groupby' => 'GROUP BY comment_approved',
	);

	if ( $comments_count_query === join( '', $sql ) ) {
		$query = str_replace( $sql['groupby'], sprintf( 'WHERE comment_type NOT IN( "ac_subject", "ac_support" ) %s', $sql['groupby'] ), $query );
	}

	return $query;
}

function anticonferences_count_all_comments( $stats = array(), $post_id = 0 ) {
	// Filter the query to remove AntiConférences comment types.
	if ( ! $post_id ) {
		add_filter( 'query', 'anticonferences_all_comments_count_query', 10, 1 );
	}

	return $stats;
}
add_filter( 'wp_count_comments', 'anticonferences_count_all_comments', 10, 2 );

function anticonferences_parse_comment_query( WP_Comment_Query $comment_query ) {
	if ( ! $comment_query->query_vars['post_ID'] && ! $comment_query->query_vars['post_id'] ) {
		$not_in = array( 'ac_subject', 'ac_support' );

		if ( ! $comment_query->query_vars['type__not_in'] || ! is_array( $comment_query->query_vars['type__not_in'] ) ) {
			$comment_query->query_vars['type__not_in'] = explode( ',', $comment_query->query_vars['type__not_in'] );
		}

		$comment_query->query_vars['type__not_in'] = array_merge( (array) $comment_query->query_vars['type__not_in'], $not_in );
	}
}
add_action( 'parse_comment_query', 'anticonferences_parse_comment_query' );
