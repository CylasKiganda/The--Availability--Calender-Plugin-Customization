<?php
/*
Plugin Name: Availability Calendar
Plugin URI: http://wordpress.org/extend/plugins/availability/
Description: A simple to use and manage availability calendar.
Version: 0.2.4
Author: StvWhtly
Author URI: http://stv.whtly.com
*/
if ( ! class_exists( 'Availability' ) ) {
	class Availability
	{
		var $name = 'Availability';
		var $tag = 'availability';
		var $options = array();
		var $db = '0.2.3';
		var $calendar, $year, $start, $end, $table;
		function Availability()
		{
			global $wpdb;
			$this->table = $wpdb->prefix . $this->tag;
			if ( $options = get_option( $this->tag ) ) {
				$this->options = $options;
				$this->start = $this->year = gmdate( 'Y', current_time( 'timestamp' ) );
				$this->end = $this->start + $this->options['years'] - 1;
			}
			if ( isset( $_GET['y'] ) && is_numeric( $_GET['y'] ) ) {
				if ( $_GET['y'] <= $this->start ) {
					$this->year = $this->start;
				} else if ( $_GET['y'] >= $this->end ) {
					$this->year = $this->end;
				} else {
					$this->year = $_GET['y'];
				}
			}
			if ( isset( $_GET['c'] ) && array_key_exists( $_GET['c'], $this->options['calendars'] ) ) {
				$this->calendar = $_GET['c'];
			} else {
				$this->calendar = 'default';
			}
			if ( is_admin() ) {
				add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
				add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
				register_activation_hook( __FILE__, array( &$this, 'upgrade' ) );
				$this->upgrade();
			} else {
				add_action( 'get_header', array( &$this, 'css'));
				add_shortcode( $this->tag, array( &$this, 'shortcode'));
				add_filter( 'availability_dropdown', array( &$this, 'dropdown'), 1);
				add_filter( 'availability_option', array( &$this, 'option'), 1);
			}			
		}
		function upgrade()
		{
			$upgrade = false;
			if ( ! isset( $this->options['years'] ) ) {
				$this->options['years'] = 5;
				$upgrade = true;
			}
			if ( ! isset( $this->options['calendars'] ) ) {
				$this->options['calendars'] = array(
					'default' => 'Default'
				);
				$upgrade = true;
			}
			if ( ! isset( $this->options['db'] ) || $this->options['db'] != $this->db ) {
				global $wpdb;
				if ($wpdb->get_var("SHOW TABLES LIKE '$this->table'") == $this->table) {
					$wpdb->query("ALTER TABLE " . $this->table . " DROP INDEX date");
				}
				$wpdb->hide_errors();
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( "CREATE TABLE $this->table (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					calendar varchar(255) NOT NULL default 'default',
					year smallint(5) NOT NULL,
					month tinyint(3) NOT NULL,
					day tinyint(3) NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY date (year,month,day,calendar)
				);" );
				$this->options['db'] = $this->db;
				$wpdb->show_errors();
				$upgrade = true;
			}
			if ( $upgrade === true ) {
				update_option( $this->tag, $this->options );
			}
		}
		function option( $name = false )
		{
			if ( is_string( $name ) && array_key_exists( $name, $this->options ) ) {
				return $this->options[$name];
			}
			return $this->options;
		}
		function css()
		{
			if ( ! array_key_exists( 'css', $this->options ) || $this->options['css'] == false ) {
				wp_enqueue_style(
					$this->tag,
					WP_PLUGIN_URL . '/' . $this->tag . '/style.css',
					false,
					false,
					'screen'
				);
			}
		}
		function shortcode( $atts )
		{
			extract( shortcode_atts( array(
				'display' => false,
				'calendar' => false,
				'year' => false
			), $atts ) );
			switch ( $display ) {
				case 'dropdown':
					$atts['display'] = false;
					return $this->dropdown( $atts );
				break;
				case 'name':
					return $this->options['calendars'][$this->calendar];
				break;
				case 'year':
					return $this->year;
				break;
				default:
					add_action( 'get_header', array( &$this, 'css' ) );
					$args = array( 'year' => $this->year );
					if ( is_string( $calendar ) && array_key_exists( $calendar, $this->options['calendars'] ) ) {
						$args['calendar'] = $calendar;
					}
					if ( isset( $year ) && ! isset( $_GET['y'] ) ) {
						if ( $year == 'all' ) {
							$output = '';
							for ( $y = $this->start; $y <= $this->end; $y++ ) {
								$args['year'] = $y;
								$output .= $this->output( 'calendar.php', array_merge(
									$args, array( 'booked' => $this->booked( $args ) )
								) );
							}
							return $output;
						} else if ( is_numeric( $year ) ) {
							if ( $year >= $this->start && $year <= $this->end ) {
								$args['year'] = $year;
							}
						}
					}					
					return $this->output( 'calendar.php', array_merge(
						$args, array( 'booked' => $this->booked( $args ) )
					) );
				break;
			}
		}
		function dropdown( $atts = array() )
		{
			extract( shortcode_atts( array(
				'before' => '',
				'after' => '',
				'display' => false,
				'page' => false,
				'id' => false
			), $atts ) );
			if ( $page === false ) {
				global $post;
				$atts['page'] = $post->ID;
			}
			if ( is_numeric( $page ) &&get_option( 'permalink_structure' ) ) {
				$atts['action'] = get_permalink( $page );
			}
			$dropdown = $before . $this->output( 'dropdown.php', $atts ) . $after;
			if ( $display == true ) {
				echo $dropdown;
			} else {
				return $dropdown;
			}
			return null;
		}
		function booked( $args = false )
		{
			$calendar = ( isset( $args['calendar'] ) && is_string( $args['calendar'] ) ? $args['calendar'] : $this->calendar );
			$year = ( is_numeric( $args['year'] ) ? $args['year'] : $this->year );
			global $wpdb;
  			$result = $wpdb->get_results( "
  				SELECT *
  				FROM $this->table
				WHERE year = ".$wpdb->escape($year)."
				  AND calendar = '".$wpdb->escape($calendar)."'
			" );
  			$booked = array();
			if ( count( $result ) > 0 ) {
				foreach ( $result AS $date ) {
					$booked[$date->month][$date->day] = $date->id;
				}
			}
			return $booked;
		}
		function admin_menu()
		{
			add_submenu_page(
				'plugins.php',
				'Manage ' . $this->name,
				$this->name,
				'edit_plugins',
				$this->tag,
				array( &$this, 'settings' )
			);
			add_submenu_page(
				'edit.php?post_type=page',
				'Manage ' . $this->name,
				$this->name,
				'edit_pages',
				$this->tag,
				array( &$this, 'manage' )
			);		
		}
		function settings()
		{
			if ( isset( $_POST['add'] ) && ! empty( $_POST['add'] ) ) {
				$id = sanitize_title( $_POST['add'] );
				if ( array_key_exists( $id, $this->options['calendars'] ) ) {
					$suffix = 2;
					do {
						$id = sanitize_title( $_POST['add'] . '-' . $suffix );
						$check = array_key_exists( $id, $this->options['calendars'] );
						$suffix++;
					} while ( $check );
				}
				$this->options['calendars'][$id] = ucfirst( $_POST['add'] );
				$message = 'Availability Calendar <strong>' . ucfirst( $_POST['add'] ) . '</strong> added.';
			} else if ( isset( $_POST['delete'] ) ) {
				if ( is_array( $_POST['delete'] ) ) { 
					$id = array_shift( array_keys( $_POST['delete'] ) );
					if ( ( $id != 'default' ) && array_key_exists( $id, $this->options['calendars'] ) ) {
						global $wpdb;
						$wpdb->query( "
							DELETE FROM ".$this->table."
							WHERE `calendar` = '".$wpdb->escape($id)."'
						" );
						unset( $this->options['calendars'][$id] );
						$message = 'Availability Calendar deleted.';
					}
				}
			} else if ( isset( $_POST['update'] ) ) {
				if ( is_array( $_POST['calendars'] ) ) {
					foreach ( $_POST['calendars'] AS $id => $name ) {
						if ( isset( $this->options['calendars'][$id] ) ) {						
							$this->options['calendars'][$id] = $name;
						}
					}
				}
				$message = 'Availability Calendars updated.';
			} else if ( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
				$this->options['css'] = false;
				foreach ( $_POST['options'] AS $key => $value ) {
					switch ( $key ) {
						case 'css':
							$this->options['css'] = true;
						break;
						case 'years':
							if ( $value > 1 && $value < 11 ) {
								$this->options['years'] = $value;
							}
						break;
					}
					
				}
				$message = 'Availability options updated.';
			}
			asort( $this->options['calendars'] );
			update_option( $this->tag, $this->options );
			include_once( 'settings.php' );
		}
		function manage()
		{
			global $wpdb;
			$booked = $this->booked();
			if ( isset( $_POST['availability_submit'] ) ) {
				$updates = &$_POST[$this->tag . '_booked'];
				$bookings = array();
				if (is_array($updates)) {
					foreach ( $updates AS $month => $days ) {
						foreach ( $days AS $day => $status ) {
							if ( ! isset($booked[$month][$day] )  ) {
								$bookings[$month . '-' . $day] = "'" . $this->calendar . "', " . $this->year . ", " . $month . ", " . $day;
								$booked[$month][$day] = true;
							}
						}
					}
				}
				if ( count( $bookings ) > 0 ) {
					$updateSQL = "INSERT IGNORE INTO " . $this->table . "
					              (calendar, year, month, day)
					              VALUES (" . implode( "),(", $bookings ) . ")";
					if ( $wpdb->query( $updateSQL ) ) {
						$saved = true;
					}
				}
				$remove = array();
				foreach ( $booked AS $month => $days ) {
					foreach ( $days AS $day => $status ) {
						if ( ! isset( $updates[$month][$day] ) && ! isset( $bookings[$month.'-'.$day] ) ) {
							$remove[] = $booked[$month][$day];
							unset( $booked[$month][$day] );
						}
					}
				}
				if ( count( $remove ) > 0 ) {
					$removeSQL = "DELETE FROM " . $this->table . "
					              WHERE `id` IN (" . implode( ",", $remove ) . ")";
					if ( $wpdb->query( $removeSQL ) ) {
						$saved = true;
					}
				}
			}
			include_once( 'manage.php' );
		}
		function plugin_row_meta( $links, $file )
		{
			$plugin = plugin_basename( __FILE__ );
			if ( $file == $plugin ) {
				return array_merge(
					$links,
					array( sprintf(
						'<a href="plugins.php?page=%s">%s</a>',
						$this->tag,
						__( 'Settings' )
					) )
				);
			}
			return $links;
		}
		function output( $file, $args = false )
		{
			ob_start();
			include( basename( $file ) );
			$content = ob_get_contents(); ob_end_clean();
			return $content;
		}
	}
	$availability = new Availability();
	function availability_dropdown( $before = '', $after = '', $display = true, $page = false )
	{
		return apply_filters(
			'availability_dropdown',
			array(
				'before' => $before,
				'after' => $after,
				'display' => $display,
				'page' => $page
			)
		);
	}
	function availability_option( $name = false )
	{
		return apply_filters( 'availability_option', $name );
	}
}
