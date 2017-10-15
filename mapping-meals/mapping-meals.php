<?php
/*
* Event: Random Hacks of Kindness, Ottawa 2017
* Should be a separate plugin
* some ugly hacks but it's a hackathon
* db work is messy and not very secure
* UI is rough
* to install run $this->create_table();
*/

$gourmet_meal_mapping = new GourmetMealMapping();

class GourmetMealMapping {
  // hard-coded - hackathon - you know... these should be in Settings or the meals should be dynamically selected based on some parameters like category
  private $lunch = 19;
  private $dinner = 33;

  function __construct() {
    add_action( 'init', array( $this, 'init' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ) );
  }

  function init() {
    add_action( 'admin_menu', array( $this, 'create_menu' ) );
    add_action( 'woocommerce_after_order_notes', array( $this, 'add_field_order_date' ) );
    add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_order_date' ) );
    add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'admin_show_order_date' ), 10, 1 );
  }

  function admin_init() {
  	add_meta_box( 'gourmet_order_meta_box', 'Meal Ordered', array( $this, 'order_meta_box' ), 'shop_order', 'side', 'high' );
    // this should be tied to plugin registration but it could also just be run once then commented out - cause... hackathon...
    // $this->create_table();
  }

  /*
  * Add a date selection field to the checkout process so customers can specify when they want the meals for
  */
  function add_field_order_date( $checkout ) {
  	echo '<div id="order_date"><h2>' . __( 'Order Date' ) . '</h2>';
  	$_args = array(
  		'type'          => 'select',
  		'class'         => array( 'my-field-class form-row-wide' ),
  		'label'         => __( 'Date'),
  		'placeholder'   => __( 'Enter date' ),
  		'options'				=> array(),
  	);
  	$_time = time();
  	$_day = 3600 * 24;
  	for ( $x = 0; $x < 10; $x ++ ) {
  		$_date = date( 'D M d', $_time + $x * $_day);
  		$_args['options'][ $_date ] = $_date;
  	}
  	woocommerce_form_field( 'order_date', $_args, $checkout->get_value( 'order_date' ) );
  	echo '</div>';
  }

  /*
  * Save the order date from add_field_order_date as meta data on the order
  */
  function save_order_date( $order_id ) {
    if ( $_POST['order_date'] ) {
      update_post_meta( $order_id, 'order_date', esc_attr( $_POST['order_date'] ) );
    }
  }

  /*
  * Show the date from add_field_order_date in the admin on the order screen - in Woocommerce's standard order data metabox
  */
  function admin_show_order_date($order){
    echo '<p><strong>' . __( 'Date' ) . ':</strong> ' . get_post_meta( $order->get_id(), 'order_date', true ) . '</p>';
  }

  /*
  * Create a menu item in the admin sidebar under Woocommerce that displays a page using the meal_calendar method
  */
  function create_menu() {
    add_submenu_page(
      'woocommerce',
      'Meal Calendar',
      'Meal Calendar',
      'manage_options',
      'meal-calendar',
      array( $this, 'meal_calendar' )
    );
	}

  /*
  * Render a page in the admin allowing staff to map products to meals by client and data (e.g. Lunch on Oct 18, 2017 for Nursing Home XYZ = Sandwiches)
  */
  function meal_calendar() {
    $client_id = isset( $_REQUEST['cid'] ) ? intval( $_REQUEST['cid'] ) : 0;
    if ( 0 === $client_id ) {
      // list clients in the first screen so staff can select one to set meals for
      $this->list_clients();
    } else {
      // list next 5 weeks, one line each, with a drop menu of mappable products for lunch and dinner
      $this->list_date_lines( $client_id );
    }
  }

  /*
  * Save the form from list_date_lines
  */
  function save_meals( $client_id ) {
    global $wpdb;
    if ( isset( $_POST['submitted'] ) )  {
      $sql = "DELETE FROM gourmet_mapping WHERE user_id = " . intval( $client_id );
      $wpdb->query( $sql );
      $start = time();
      foreach( $_POST as $key => $val ) {
        // Hack: there's a better, more WP-compliant way to do insert to db but running out of time at hackathon so cutting corners
        if ( false !== strpos( $key, 'meal_' ) ) {
          $meal_id = intval( $val );
          if ( 0 < $meal_id ) {
            // should have used a 2-dim array in HTML name param
            $keys = str_replace( 'meal_', '', $key );
            $args = explode( '_', $keys );
            $day = $start + ( 60 * 60 * 24 * intval( $args[0] ) );
            $order_date = date( 'Y-m-d 00:00:00', $day );
            $product_id = intval( $args[1] );
            // insert meal mapping into custome table: gourmet_mapping
            $sql = "INSERT INTO gourmet_mapping ( user_id, product_id, meal_id, order_date )
              VALUES ( " . intval( $client_id ) . ", " . intval( $product_id ) . ", " . intval( $meal_id ) . ", '" . $order_date . "' ) ";
            $wpdb->query( $sql );
          }
        }
      }
    }
  }

  /*
  * Retrieve data saved in save_meals to prepopulate form in list_date_lines
  */
  function get_saved_meals( $client_id ) {
    global $wpdb;
    $sql = "SELECT * FROM gourmet_mapping WHERE user_id = " . intval( $client_id );
    $meals = $wpdb->get_results( $sql );
    $saved_meals = array();
    foreach ( $meals as $meal ) {
      $saved_meals[ $meal->product_id ][ $meal->order_date ] = $meal->meal_id;
    }
    return $saved_meals;
  }

  /*
  * Iterate through 5 weeks of days and display date and product drop menus for lunch and dinner
  */
  function list_date_lines( $client_id ) {
    $this->save_meals( $client_id );
    $saved_meals = $this->get_saved_meals( $client_id );
    $meals = $this->get_meals();
    $client = get_userdata( $client_id );
    echo '<h2>Client: ' . ( isset( $client->display_name ) ? esc_html( $client->display_name ) : 'N/A' ) . '</h2>';
    echo '<form method="POST" action="/wp-admin/admin.php?page=meal-calendar&cid=2&mo=1">' . "\n";
    echo '<input type="hidden" name="submitted" value="1" >';
    // echo '<input type="hidden" name="cid" value="' . $client_id . '" >';
    // echo '<input type="hidden" name="mo" value="' . $month . '" >';
    // echo '<input type="hidden" name="page" value="meal-calendar">' . "\n";
    //wp_nonce_field( PM_LAYOUT_URI, 'pm_layout_noncename' );
    echo '<table>' . "\n";
    echo '<tr>' . "\n";
    echo '<td><b>Date</b></td>' . "\n";
    echo '<td><b>Lunch</b></td>' . "\n";
    echo '<td><b>Dinner</b></td>' . "\n";
    echo '</tr>' . "\n";
    $start = time();
    // Hack: hardcoded product ids for lunch and dinner products - move to Settings or pull dynamically and loop from prods that qualify (to include breakfast, etc)
    // loop thru next 5 weeks & display meals that can be served to this client
    // Hack: should get any meals already saved from previous use of this form and populate, but it's a hackathon and we've run out of time
    for ( $x = 0; $x < 35; $x ++ ) {
      $day = $start + ( 60 * 60 * 24 * $x );
      $date = date( 'M d', $day );
      echo '<tr>' . "\n";
      echo '<td>' . $date . '</td>' . "\n";
      echo '<td>' . $this->drop_meals( $meals, $saved_meals, $this->lunch, $x ) . '</td>' . "\n";
      echo '<td>' . $this->drop_meals( $meals, $saved_meals, $this->dinner, $x ) . '</td>' . "\n";
      echo '</tr>' . "\n";
    }
    echo '</table>' . "\n";
    echo '<input type="submit" value="Save" class="button button-primary">' . "\n";
    echo '</form>' . "\n";
  }

  /*
  * Return drop menu of mappable meals
  */
  function drop_meals( $meals, $saved_meals, $product_id, $count ) {
    $day = time() + ( 60 * 60 * 24 * $count );
    $order_date = date( 'Y-m-d 00:00:00', $day );
    $meal_id = 0;
    if ( isset( $saved_meals[ $product_id ][ $order_date ] ) ) {
      $meal_id = intval( $saved_meals[ $product_id ][ $order_date ] );
    }
    $_output = '';
    // should be 2-dim array - sorry
    $_output .= '<select name="meal_' . $count . '_' . $product_id . '" id="">';
    $_output .= '<option value="0">-- None --</option>' . "\n";
    foreach ( $meals as $meal ) {
      $_output .= '<option value="' . $meal->ID . '"' . ( $meal_id === $meal->ID ? ' selected="selected"' : '' ) . '>' . $meal->post_title . '</option>' . "\n";
    }
    $_output .= '</select>';
    return $_output;
  }

  /*
  * Retrieve a list of all mappable meals
  */
  function get_meals() {
    $args = array(
      'post_status' => 'private',
      'post_type' => 'product',
      'posts_per_page' => 200,
    );
    $meals = get_posts( $args );
    return $meals;
  }

  /*
  * Display a list of all customer-role users
  */
  function list_clients() {
    $clients = $this->get_clients();
    echo '<table>' . "\n";
    foreach ( $clients as $client ) {
      echo '<tr>' . "\n";
      echo '<td>' . $client->display_name . '</td>' . "\n";
      echo '<td><a href="/wp-admin/admin.php?page=meal-calendar&cid=' . $client->ID . '">Menus</a></td>' . "\n";
      //echo '<td><a href="/wp-admin/admin.php?page=meal-calendar&cid=' . $client->ID . '&mo=1">Next Month</a></td>' . "\n";
      echo '</tr>' . "\n";
    }
    echo '</table>' . "\n";
  }

  /*
  * Retrieve a list of all customer-role users
  */
  function get_clients() {
    $args = array( 'role' => 'customer' );
    $clients = get_users( $args );
    return $clients;
  }

  /*
  * Display a new metabox on the order screen including data collected here and mapping meals
  */
  function order_meta_box() {
    global $post;
    $order_id = $post->ID;
    $user_id = get_post_meta( $order_id, '_customer_user', true );
    $order_date = get_post_meta( $order_id, 'order_date', true );
    $items = $this->get_order_items( $order_id );
    $lines = array();
    foreach ( $items as $item ) {
      $product_id = intval( $item['product_id'] );
      $quantity = intval( $item['quantity'] );
      $meal = $this->get_meal_data( $product_id, $user_id, $order_date );
      if ( is_null( $meal ) ) {
        $product = get_post( $product_id );
        $meal_name = $product->post_title;
      } else {
        $meal_name = $meal->post_title;
      }
      $lines[] = $meal_name . ' x ' . $quantity;
    }
    echo '<p><strong>' . __('Date') . ':</strong> ' . esc_html( get_post_meta( $order_id, 'order_date', true ) ) . '</p>';
    echo '<p><strong>' . __('Notes') . ':</strong> ' . esc_html( $post->post_excerpt ) . '</p>';
    echo '<p><strong>' . __('Meal') . ':</strong> <ul style="list-style-type: disc;margin-left: 20px;">';
    foreach ( $lines as $line ) {
      echo '<li>' . esc_html( $line ) . '</li>';
    }
    echo '</ul></p>';
  }

  /*
  * Retrieve data for the mapped meal (e.g. Sandwiches instead of Lunch)
  */
  function get_meal_data( $product_id, $user_id, $order_date ) {
    if ( 0 < $product_id ) {
      $_meal_id = $this->map_meal_product( $product_id, $user_id, $order_date );
      if ( 0 < $_meal_id ) {
        $product = get_post( $_meal_id );
        if ( isset( $product->post_title ) ) {
          return $product;
        }
      }
    }
    return null;
  }

  /*
  * Retrieve mapped meal (e.g. Lunch => Sandwiches) and retunr the product ID or 0
  */
  function map_meal_product( $product_id, $user_id, $order_date ) {
    // look up product in new table
    global $wpdb;
    $wpdb->hide_errors();
    $time = strtotime( $order_date );
    $date = date( 'Y-m-d 00:00:00', $time );
    $sql = "SELECT * FROM gourmet_mapping "
      . " WHERE product_id = " . intval( $product_id )
      . " AND user_id = " . intval( $user_id )
      . " AND order_date = '" . $date . "'";
    //echo $sql;
    $meal = $wpdb->get_results( $sql );
    if ( ! $wpdb->last_error ) {
      //print_r( $meal );
      if ( isset( $meal[0]->meal_id ) ) {
        return $meal[0]->meal_id;
      }
    }
    return 0; // stub
  }

  /*
  * Retrieve all items in a given Woocommerce order
  */
  function get_order_items( $order_id ) {
    $order = new WC_Order( $order_id );
    $items = $order->get_items();
    return $items;
  }

  /*
  * Create the custom table to map products to meals
  */
  function create_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE `gourmet_mapping` (
      `user_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `meal_id` int(11) NOT NULL,
      `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  /*
  * Seed the database with mapping data - not required
  */
  function seed_database() {
    global $wpdb;
    $sql = "DELETE FROM gourmet_mapping ";
    $wpdb->query( $sql );
    $client_id = 1;
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 98, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 17, 2017' );
    $client_id = 2;
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 98, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 17, 2017' );
    $client_id = 3;
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 98, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 17, 2017' );
    $client_id = 4;
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 98, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 17, 2017' );
    $client_id = 5;
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 17, 2017' );
    $client_id = 6;
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $this->lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $this->dinner, 66, 'Oct 17, 2017' );
  }

  /*
  * Seed a single row the database with mapping data - not required
  */
  function seed_db_row( $client_id, $product_id, $meal_id, $date ) {
    global $wpdb;
    $time = strtotime( $date );
    $order_date = date( 'Y-m-d 00:00:00', $time );
    $sql = "INSERT INTO gourmet_mapping ( user_id, product_id, meal_id, order_date )
      VALUES ( " . intval( $client_id ) . ", " . intval( $product_id ) . ", " . intval( $meal_id ) . ", '" . $order_date . "' ) ";
    // echo $sql;
    $wpdb->query( $sql );

  }

}


// Product variation workaround (== ugly hack) to allow dates on prods
// won't work atm because variations are effectively separate skus - breaks the woocommerce data model - square peg...
// add_filter( 'woocommerce_variation_option_name', 'woocommerce_variation_option_name_cmj', 10, 1 );
// function woocommerce_variation_option_name_cmj( $_val ) {
// 	if ( ( '0' === $_val ) || ( 0 < intval( $_val ) ) ) {
// 		$_time = time();
// 		$_day = 3600 * 24;
// 		$_val = intval( $_val );
// 		$_date = date( 'D M d', $_time + $_val * $_day);
// 		return $_date;
// 	} else {
// 		return $_val;
// 	}
// }
