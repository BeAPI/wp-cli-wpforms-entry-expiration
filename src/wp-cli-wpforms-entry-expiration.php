<?php
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

		// Count entries to delete
		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) AS counter FROM {$wpdb->prefix}wpforms_entries WHERE date < %s",
			$formatted_expired_time
		) );

		if ( 0 === $total ) {
			WP_CLI::warning( 'No entries to delete.' );

			return;
		}

		// If dry run, returns the entries to be deleted. Else, delete the entries.
		if ( $dry_run ) {
			WP_CLI::warning( sprintf( 'Dry run : %d entries would be deleted.', $total ) );
		} else {
			$rows_affected = $wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}wpforms_entries WHERE date < %s",
				$formatted_expired_time
			) );
			WP_CLI::success( sprintf( '%d wpforms entries deleted', $rows_affected ) );

		}

		WP_CLI::log( 'End cleaning forms expired entries' );
	}

}

WP_CLI::add_command( 'clean-wpforms-entries', Clean_Wpforms_Entries::class );