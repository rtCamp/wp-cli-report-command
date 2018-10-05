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

	$format = ! empty( $args['format'] ) ? $args['format'] : 'table';

	// Create and Populate report for themes.
	if ( ! empty( $args['themes'] ) && $args['themes'] ) {
		theme_report( $format );
	}

	// Create and Populate report for plugins.
	if ( ! empty( $args['plugins'] ) && $args['plugins'] ) {
		plugin_report( $format );
	}
}

function theme_report( $format ) {
	$sites  = get_sites();
	$themes = wp_get_themes();
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
		)
	);
}

function plugin_report( $format ) {

	$sites                  = get_sites();
	$all_plugin_names       = array_map( 'format_plugin_name', array_keys( get_plugins() ) );
	$network_active_plugins = array_map( 'format_plugin_name', array_keys( get_site_option( 'active_sitewide_plugins' ) ) );
	$data                   = array();
	$header_data            = array_merge( array( 'blog_id', 'domain' ), $all_plugin_names );

	foreach ( $sites as $site ) {
		$active_plugins = array_map( 'format_plugin_name', array_values( get_blog_option( $site->blog_id, 'active_plugins' ) ) );
		$plugin_data    = array( 'blog_id' => $site->blog_id, 'domain' => $site->domain );
		foreach ( $all_plugin_names as $plugin_name ) {
			if ( in_array( $plugin_name, $network_active_plugins ) ) {
				$plugin_data[ $plugin_name ] = 'network active';
			} elseif ( in_array( $plugin_name, $active_plugins ) ) {
				$plugin_data[ $plugin_name ] = 'active';
			} else {
				$plugin_data[ $plugin_name ] = 'inactive';
			}
		}
		array_push( $data, $plugin_data );
	}

	WP_CLI\Utils\format_items(
		$format,
		$data,
		$header_data
	);
}

function format_plugin_name( $param ) {
	if ( false !== strpos( $param, '/' ) ) {
		return substr( $param, 0, strpos( $param, '/' ) );
	} else {
		return str_replace( '.php', '', $param );
	}
}
