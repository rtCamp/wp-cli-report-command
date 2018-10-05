<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$autoload = dirname( __FILE__ ) . '/vendor/autoload.php';

if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

try {
	WP_CLI::add_command( 'report', 'report' );
} catch ( Exception $e ) {
	echo 'Caught Exception: ', $e->getMessage(), "\n";
}

/**
 * Generates a report for themes and plugins in a Multisite environment.
 *
 * Available commands:
 *
 * wp report --all
 * wp report --themes
 * wp report --plugins
 */
function report( $assoc_args, $args ) {

	if ( ! is_multisite() ) {
		WP_CLI::error( 'This does not seems to be a MU Site.', true );
	}

	// Show help if no arguments are passed.
	if ( empty( $args ) ) {
		WP_CLI::runcommand( 'help report' );
	}

	// Create and Populate report for themes.
	if ( $args['themes'] ) {
		theme_report( $args );
	}

	// Create and Populate report for plugins.
	if ( $args['plugins'] ) {
		plugin_report( $args );
	}
}

function theme_report( $args ) {
	$sites  = get_sites();
	$themes = wp_get_themes();
	$format = ! empty( $args['format'] ) ? $args[ 'format' ] : 'table';
	$data   = array();
	foreach ( $sites as $site ) {
		$blog_id      = $site->blog_id;
		$domain       = $site->domain;
		$theme_name   = get_blog_option( $blog_id, 'stylesheet' );
		$parent_theme = $themes[ $theme_name ]->parent()->name;
		array_push( $data, array(
			'blog_id'       => $blog_id,
			'domain'        => $domain,
			'current_theme' => $theme_name,
			'parent_theme'  => $parent_theme,
		) );
	}
	WP_CLI\Utils\format_items(
		$format,
		$data,
		array(
			'blog_id',
			'domain',
			'current_theme',
			'parent_theme',
		) );
}

function plugin_report( $args ) {
	// Code here.
}