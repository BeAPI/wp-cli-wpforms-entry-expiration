
# wp-cli-wpforms-entry-expiration

WP-CLI command to clean wpforms entries, by setting a conservation time limit, before which entries will be deleted.

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install BeAPI/wp-cli-wpforms-entry-expiration`

## Usage

`wp clean-wpforms-entries --expire={time}`

Time value must be without spaces.

Exemple :
`wp clean-wpforms-entries --expire=6months`

Optional parameters :
`--dry-run`

## Credits

Be API