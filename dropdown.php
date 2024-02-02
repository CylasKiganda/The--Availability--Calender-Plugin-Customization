<form id="availability_form" action="<?php if ( isset( $args['action'] ) ) { _e( $args['action'] ); } ?>" method="get">
	<?php if ( ! isset( $args['action'] ) ) : ?>
	<input type="hidden" name="page_id" value="<?php esc_attr_e($args['page']); ?>" />
	<?php endif; ?>
	<?php if ( isset( $args['id'] ) || count( $this->options['calendars']) == 1 ) : ?>
	<input type="hidden" name="c" value="<?php if ( isset($args['id'] ) ) { echo $args['id']; } else { echo array_shift( array_keys( $this->options['calendars'] ) ); } ?>" />
	<?php else: ?>
	<select id="availability_name" name="c">
		<?php foreach ( $this->options['calendars'] AS $key => $calendar ) : ?>
		<option<?php if ( $key == $this->calendar ) { echo ' selected="selected"'; } ?> value="<?php echo $key; ?>"><?php esc_html_e( $calendar ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php endif; ?>
	<select id="availability_year" name="y">
		<?php for ( $year = $this->start; $year <= $this->end; $year++ ) : ?>
		<option<?php if ( $year == $this->year ) { echo ' selected="selected"'; } ?>><?php esc_html_e( $year ); ?></option>
		<?php endfor; ?>
	</select>
	<button id="availability_submit" type="submit"><?php _e( 'Go' ); ?></button>
</form>