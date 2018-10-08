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
		WP_CLI::error( 'Oops! Looks like you running this in a non WPMU setup.', true );
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

	// Create and Populate report for all data including both plugins and themes.
	if ( ! empty( $args['all'] ) && $args['all'] ) {
		all_report( $format );
	}
}

/**
 * Gets the data for all the themes.
 *
 * @param $format
 */
function theme_report( $format ) {
	$sites  = report_get_sites();
	$themes = report_get_themes();
	$data   = array();
	foreach ( $sites as $site ) {
		$blog_id      = $site->blog_id;
		$domain       = $site->domain;
		$theme_name   = get_blog_option( $blog_id, 'stylesheet' );
		$parent_theme = ! empty( $themes[ $theme_name ] ) ? $themes[ $theme_name ]->parent()->name : null;
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

/**
 * Gets the data for all the plugins.
 *
 * @param string $format
 */
function plugin_report( $format ) {

	$sites                  = report_get_sites();
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

/**
 * Gets the data for Plugins as well as themes in a single report.
 *
 * @param string $format
 */
function all_report( $format ) {
	$sites                  = report_get_sites();
	$themes                 = report_get_themes();
	$data                   = array();
	$all_plugin_names       = array_map( 'format_plugin_name', array_keys( get_plugins() ) );
	$network_active_plugins = array_map( 'format_plugin_name', array_keys( get_site_option( 'active_sitewide_plugins' ) ) );
	$header_data            = array_merge( array(
		'blog_id',
		'domain',
		'current_theme',
		'parent_theme'
	), $all_plugin_names );

	foreach ( $sites as $site ) {
		$blog_id        = $site->blog_id;
		$domain         = $site->domain;
		$active_plugins = array_map( 'format_plugin_name', array_values( get_blog_option( $site->blog_id, 'active_plugins' ) ) );
		$theme_name     = get_blog_option( $blog_id, 'stylesheet' );
		$parent_theme   = ! empty( $themes[ $theme_name ] ) ? $themes[ $theme_name ]->parent()->name : null;
		$all_data       = array(
			'blog_id'       => $blog_id,
			'domain'        => $domain,
			'current_theme' => $theme_name,
			'parent_theme'  => $parent_theme,
		);

		foreach ( $all_plugin_names as $plugin_name ) {
			if ( in_array( $plugin_name, $network_active_plugins ) ) {
				$all_data[ $plugin_name ] = 'network active';
			} elseif ( in_array( $plugin_name, $active_plugins ) ) {
				$all_data[ $plugin_name ] = 'active';
			} else {
				$all_data[ $plugin_name ] = 'inactive';
			}
		}

		array_push( $data, $all_data );
	}

	WP_CLI\Utils\format_items(
		$format,
		$data,
		$header_data
	);
}

/**
 * Format the plugin name to show proper names in the report.
 *
 * @param string $param
 *
 * @return bool|mixed|string
 */
function format_plugin_name( $param ) {
	if ( false !== strpos( $param, '/' ) ) {
		return substr( $param, 0, strpos( $param, '/' ) );
	} else {
		return str_replace( '.php', '', $param );
	}
}

/**
 * Returns all the sites. The number is set to null to retrieve all
 * the sites in network.
 *
 * @return array|int
 */
function report_get_sites() {
	return get_sites( array(
		'number' => null,
	) );
}

/**
 * Returns all the themes present in the themes directory.
 * Includes themes with errors as well.
 *
 * @return WP_Theme[]
 */
function report_get_themes() {
	return wp_get_themes( array(
		'errors' => null,
	) );
}
