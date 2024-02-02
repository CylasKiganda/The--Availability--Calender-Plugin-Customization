<?php if (isset($message)) : ?>
<div id="message" class="updated fade">
	<p><?php _e( $message ); ?>.</p>
</div>
<?php endif; ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( $this->name ); ?> <?php _e( 'Settings' ); ?></h2>
	<h3><?php _e( 'Calendars' ); ?></h3>
	<form action="" method="post">
		<table class="form-table">
			<tr>
				<td>
					<ol>
						<?php foreach ( $this->options['calendars'] AS $key => $calendar ) : ?>
						<li>
							<input type="text" name="calendars[<?php echo $key; ?>]" value="<?php esc_html_e( $calendar ); ?>" />
							<a href="edit.php?post_type=page&page=<?php echo $this->tag; ?>&c=<?php echo $key; ?>" class="button"><?php _e( 'View' ); ?></a>
							<?php if ( $key !== 'default' ) : ?>
							<a href="#delete" class="delete"><?php _e( 'Delete' ); ?></a>
							<?php endif; ?>
							<span class="description">(<?php echo $key; ?>)</span>
							<?php if ( $key !== 'default' ) : ?>
							<span class="confirm">
								You are about to delete <strong><?php _e( $calendar ); ?></strong>.
								<input type="submit" name="delete[<?php echo $key; ?>]" class="button" value="<?php _e( 'Delete' ); ?>" />
								<input type="submit" name="cancel" class="button cancel" value="<?php _e( 'Cancel' ); ?>" />
							</span>
							<?php endif; ?>
						</li>
						<?php endforeach; ?>
					</ol>
					<p>
						<input type="submit" name="update" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
						<label>or add another</label>
						<input type="text" name="add" value="" />
						<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Go' ); ?>" />
					</p>
				</td>
			</tr>
		</table>
	</form>
	<form action="" method="post">
		<h3><?php _e( 'Additional Options' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Calendar Stylesheet</th>
				<td>
					<label>
						<input type="checkbox" value="1" name="options[css]" <?php if ( array_key_exists( 'css', $this->options ) ) { checked( '1', $this->options['css'] ); } ?> />
						Do not include the default stylesheet.
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Number of Years</th>
				<td>
					<select name="options[years]" style="width: 50px;">
						<?php for ( $i = 2; $i <= 10; $i++ ) : ?>
						<option<?php if ( $i == $this->options['years'] ) { echo ' selected="selected"'; } ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<span class="description">The default is 5 years.</span>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
		</p>
	</form>
</div>
<style type="text/css">
.delete,
.cancel {
	display: none;
}
</style>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.confirm').hide();
	$('.delete, .cancel').show();
	$('.delete').click(function(e) {
		e.preventDefault();
		$(this).siblings('.confirm').toggle();
	});
	$('.confirm input[name="cancel"]').click(function(e) {
		e.preventDefault();
		$(this).parent().hide();
	});
});
</script>