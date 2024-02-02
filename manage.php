<?php global $wp_locale; ?>
<?php if (isset($saved)) : ?>
<div id="message" class="updated fade">
	<p><?php _e('Availability saved'); ?>.</p>
</div>
<?php endif; ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php esc_html_e( $this->name ); ?> <?php _e( 'Calendar' ); ?></h2>
	<form method="get" action="" id="availability-year">
		<input type="hidden" name="post_type" value="page" />
		<input type="hidden" name="page" value="<?php esc_attr_e( $this->tag ); ?>" />
		<div class="tablenav">
			<div class="alignleft actions">
				<?php if (count($this->options['calendars']) > 1) : ?>
				<select name="c">
					<option value="">Calendar</option>
					<?php foreach ($this->options['calendars'] AS $key => $calendar) : ?>
					<option<?php echo ( $key == $this->calendar ? ' selected="selected"' : null ); ?> value="<?php esc_attr_e( $key ); ?>"><?php esc_attr_e( $calendar ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php endif; ?>
				<select name="y">
					<option value=""><?php esc_attr_e( __( 'Select Year' ) ); ?></option>
					<?php for ( $year = $this->start; $year <= $this->end; $year++ ) : ?>
					<option<?php echo ($year == $this->year ? ' selected="selected"' : null); ?>><?php esc_attr_e( $year ); ?></option>
					<?php endfor; ?>
				</select>
				<input type="submit" class="button" value="<?php _e( 'View' ); ?>" name="action"/>
			</div>
		</div>
	</form>
	<form id="calendar" action="" method="post">
		<table class="widefat availability" cellspacing="0">
			<thead>
				<tr>
					<th class="year"><?php esc_html_e( $this->year ); ?></th>
					<?php for ($day = 1; $day <= 31; $day++) : ?>
					<th><?php echo zeroise($day, 2); ?></th>
					<?php endfor; ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="year"><?php echo $this->year; ?></th>
					<?php for ($day = 1; $day <= 31; $day++) : ?>
					<th><?php echo zeroise($day, 2); ?></th>
					<?php endfor; ?>
				</tr>
			</tfoot>
			<tbody>
			<?php for ($month = 1; $month <= 12; $month++) : ?>
				<?php $total = intval( date( 't', mktime( 0, 0, 0, $month, 1, $this->year ) ) ); ?>
				<tr<?php echo ( $month % 2 == 0 ? ' class="alternate"' : '' ); ?>>
					<th><?php esc_html_e( $wp_locale->get_month( $month ) ); ?></th>
					<?php for ( $grid = 1; $grid <= 31; $grid++ ) : ?>
					<?php $checked = ( isset($booked[$month] ) && array_key_exists( $grid, $booked[$month] ) ? true : false ); ?>
					<td<?php echo ( $checked == true ? ' class="booked"' : '' ); ?>>
						<?php if ( $grid <= $total ) : ?>
						<?php $label = date( 'l jS F Y', mktime(0, 0, 0, $month, $grid, $this->year ) ); ?>
						<input type="checkbox" name="<?php esc_attr_e( $this->tag ); ?>_booked[<?php esc_attr_e( $month ); ?>][<?php esc_attr_e( $grid ); ?>]" <?php echo ( $checked == true ? ' checked="checked"' : '' ); ?> value="true" title="<?php esc_attr_e( $label); ?>" alt="<?php esc_attr_e( $label ); ?>" />
						<?php endif; ?>
					</td>
					<?php endfor; ?>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="availability_submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
		</p>
	</form>
</div>
<style type="text/css">
#availability-year select {
	width: 150px;
}
table.widefat.availability thead th,
table.widefat.availability tfoot th {
	padding: 7px 3px 8px;
	text-align: center;
}
table.widefat.availability th.year {
	text-align: left;
	padding: 7px 7px 8px;
}
table.widefat.availability td {
	vertical-align: middle;
	padding: 0;
	text-align: center;
}
table.widefat.availability td.booked {
 	background-color: #FFFEEB; /* #FFFBCC */
}
</style>