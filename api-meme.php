<?php

/**
 * @package Api_meme
 * @version 1.0.0
 */
/*
Plugin Name: API meme
Description: Plugin d'affichage aléatoire de memes
Author: Aurélien, Marine, Grégory, Mehdi
Version: 1.0.0
*/

//// Create memes Custome Post Type (CPT)
function memes_post_type()
{
	register_post_type(
		'meme',
		array(
			'labels' => array(
				'name' => __('Memes'),
				'singular_name' => __('Meme')
			),
			'public' => true,
			'show_in_rest' => true,
			'supports' => array('title', 'thumbnail'),
			'has_archive' => true,
			'rewrite'   => array('slug' => 'my-memes'),
			'menu_position' => 5,
			'menu_icon' => 'dashicons-format-image',
			// 'taxonomies' => array('cuisines', 'post_tag') // this is IMPORTANT
		)
	);
}
add_action('init', 'memes_post_type');




/**
 * API Get one meme title by ID
 *
 */
function get_meme($data)
{
	$post = get_post($data['id']);

	if (empty($post)) {
		return new WP_Error('no_author', 'Invalid author', array('status' => 404));
	}

	# Récupère l'image du post
	$thumbnail = get_the_post_thumbnail_url($post->ID);

	return [
		'title' => $post->post_title,
		'image' => $thumbnail
	];
}



add_action('rest_api_init', function () {
	register_rest_route('memes/v1', '/meme/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'get_meme',
	));
});





/**
 * API Get all meme and shuffle them
 *
 */
function get_meme_random()
{
	# Récupère les posts de type "meme"
	$posts = get_posts([
		'post_type' => 'meme',
		'orderby' => 'rand' # Mélange aléatoire des posts
	]);

	$post = $posts[0];

	# Récupère l'image du post
	$thumbnail = get_the_post_thumbnail_url($post->ID);

	return [
		'title' => $post->post_title,
		'image' => $thumbnail
	];
}



add_action('rest_api_init', function () {
	register_rest_route('memes/v1', '/random', array(
		'methods' => 'GET',
		'callback' => 'get_meme_random',
	));
});



# Shortcode
function shortcode_meme()
{
	$image = wp_remote_get('http://localhost:10004/wp-json/memes/v1/random');
	$image = json_decode($image['body'])->image;

	return '<img src="' . $image . '" width=600 />';
}
add_shortcode('meme', 'shortcode_meme');
