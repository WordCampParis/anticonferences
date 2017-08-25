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
	if ( ! empty( $data['post_type'] ) && 'festivals' === $data['post_type'] ) {
		anticonferences_register_post_metas( 'post' );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'anticonferences_register_temporary_post_metas', 10, 1 );

function anticonferences_unregister_temporary_post_metas() {
	$default_metas = anticonferences_get_default_metas();

	foreach ( array_keys( $default_metas ) as $meta_key ) {
		unregister_meta_key( 'post', $meta_key );
	}
}
add_action( 'save_post_festivals', 'anticonferences_unregister_temporary_post_metas' );
