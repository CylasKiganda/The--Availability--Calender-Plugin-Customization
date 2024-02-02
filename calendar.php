<?php 
extract( shortcode_atts( array(
	'year' => $this->year,
	'booked' => array(),
), $args ) );

global $wp_locale;
	// Get details of todays date...
$current_month = get_the_time( 'n' );
$current_year = get_the_time( 'Y' );
	// Set the classes assigned to the calendar...
$year_classes = array(
	'wp-availability',
	'year-' . $current_year
);
if ( $current_year == $year ) {
	$year_classes[] = 'year-current';
} else if ( $current_year - 1 == $year ) {
	$year_classes[] = 'year-previous';
} else if ( $current_year + 1 == $year ) {
	$year_classes[] = 'year-next';
}
?>
<div id="wp-availability-<?php esc_attr_e( $year ); ?>" class="<?php esc_attr_e( implode( ' ', $year_classes ) ); ?>">
    <?php for ( $month = 1; $month <= 12; $month++ ) : ?>
    <?php
	// User friendly month name...
	$month_name = $wp_locale->get_month( $month );
	// Set the classes assigned to this month...
	$month_classes = array(
		'wp-availability-month',
		'month-' . $month,
		'month-' . strtolower( $wp_locale->get_month_abbrev( $month_name ) )
	);
	if ( $current_month == $month ) {
		$month_classes[] = 'month-current';
	} else if ( $current_month - 1 == $month ) {
		$month_classes[] = 'month-previous';
	} else if ( $current_month + 1 == $month ) {
		$month_classes[] = 'month-next';
	}
	?>
    <div class="<?php esc_attr_e( implode( ' ', $month_classes ) ); ?>">
        <table>
            <thead>
                <tr>
                    <th colspan="7">
                        <?php esc_html_e( $month_name ); ?>
                        <?php esc_html_e( $year ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
					$week_begins = intval( get_option( 'start_of_week' ) );
					$myweek = array();
					for ( $wdcount=0; $wdcount<7; $wdcount++ ) {
						$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
					}
					?>
                    <?php foreach ( $myweek AS $day ) : ?>
                    <th><?php esc_html_e( $wp_locale->get_weekday_initial( $day ) ); ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php
					$unixmonth = mktime( 0, 0, 0, $month, 1, $year );
					 // Total number of days in the month...
					$last_day = intval( date( 't', $unixmonth ) );
					 // Numerical value of start day...
					$starts = intval( calendar_week_mod( date( 'w', $unixmonth ) - $week_begins ) );
					 // Create month grid...
					for ( $grid = 0; $grid <= 41; $grid++ ) :
						$available = $day_class = null;
						if ( $grid < $starts || $grid > ( $last_day + $starts - 1 ) ) {
							$day = null;
							$day_class = 'wp-availability-disabled';
						} else {
							$day = ( $grid - $starts ) + 1;
							$day_class = 'day-'.$day;
							if ( isset( $booked[$month] ) && array_key_exists( $day, $booked[$month] ) ) {
								$available = false;
								$day_class .= ' wp-availability-booked';
							}
						}
				?>
                    <td<?php _e( $day_class !== null ? ' class="'.$day_class.'"' : '' ); ?>>
                        <?php _e( $day ? $day : '&nbsp;'); ?></td>
                        <?php if ( ( ( $grid + 1 ) % 7 == 0 ) && $grid < 42 ) : ?>
                </tr>
                <tr>
                    <?php endif; endfor; ?>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endfor; ?>
</div>