<?php
/*
Plugin Name: Be API - Clean WP Forms expired entries.
Version: 1.0.0
Plugin URI: https://beapi.fr
Description: Clean the WP Forms entries regarding a given expiration date.
Author: Be API
Author URI: https://beapi.fr

----

Copyright 2024 Be API Technical team (humans@beapi.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class Clean_Wpforms_Entries extends \WP_CLI_Command {

	/**
	 * Update forms to set settings
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * <expire_time>
	 * : specifies the length of time entries must be kept.
	 *
	 * [--dry-run]
	 *  : Perform a dry run, showing which entries would be deleted without actually deleting them.
	 *
	 * ## EXAMPLES
	 *
	 * wp clean-wpforms-entries 6months
	 * wp clean-wpforms-entries 6months --dry-run
	 *
	 * @return void
	 * @author Jules Fell
	 */
	public function __invoke( $args, $assoc_args ): void {
		global $wpdb;

		// Get assoc_args to set entries expire limit and dry run.
		$dry_run       = isset( $assoc_args['dry-run'] );
		$expire_period = isset( $args[0] ) ? '-' . $args[0] : '';

		if ( empty ( $expire_period ) ) {
			WP_CLI::error( 'Expired time is empty. Please give an expired time. Ex : "6months", "1year", "90days".' );

			return;
		}

		// Check if $expired_time is convertible as a timestamp.
		$expired_time = strtotime( $expire_period, time() );
		if ( false === $expired_time ) {
			WP_CLI::error( 'Expired time is not readable. Use a valid format like "6months", "1year", "90days".' );

			return;
		}

		$formatted_expired_time = date( 'Y-m-d H:i:s', $expired_time );
		WP_CLI::log( 'Start the cleaning process for entries before : ' . $formatted_expired_time );

		// Get the entries to delete
		$entries_query     = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wpforms_entries WHERE date < %s",
			$formatted_expired_time
		);
		$entries_to_delete = $wpdb->get_results( $entries_query );
		$total             = count( $entries_to_delete );

		if ( 0 === $total ) {
			WP_CLI::warning( 'No entries to delete.' );

			return;
		}

		// If dry run, returns the entries to be deleted. Else, delete the entries.
		if ( $dry_run ) {
			WP_CLI::warning( sprintf( 'Dry run : %d entries would be deleted.', $total ) );
		} else {
			$delete_query = $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}wpforms_entries WHERE date < %s",
				$formatted_expired_time
			);
			$wpdb->get_results( $delete_query );
			WP_CLI::success( sprintf( '%d wpforms entries deleted', $total ) );

		}

		WP_CLI::success( 'End cleaning forms expired entries' );
	}

}

WP_CLI::add_command( 'clean-wpforms-entries', Clean_Wpforms_Entries::class );