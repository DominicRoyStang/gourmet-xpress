<?php
$gourmet_meal_mapping = new GourmetMealMapping();

class GourmetMealMapping {

  function __construct() {
    add_action( 'init', array( $this, 'init' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ) );
  }

  function init() {
    add_action( 'admin_menu', array( $this, 'create_menu' ) );
  }

  function admin_init() {
  	add_meta_box( 'gourmet_order_meta_box', 'Meal Ordered', array( $this, 'order_meta_box' ), 'shop_order', 'side', 'high' );
    //$this->create_table();
  }

  function seed_database() {
    global $wpdb;
    $sql = "DELETE FROM gourmet_mapping ";
    $wpdb->query( $sql );
    $lunch = 19;
    $dinner = 33;
    $client_id = 1;
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $dinner, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $dinner, 98, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 17, 2017' );
    $client_id = 2;
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $lunch, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $lunch, 98, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 17, 2017' );
    $client_id = 3;
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $dinner, 98, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $dinner, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 17, 2017' );
    $client_id = 4;
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $lunch, 98, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $lunch, 98, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 17, 2017' );
    $client_id = 5;
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 17, 2017' );
    $client_id = 6;
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 14, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 15, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 16, 2017' );
    $this->seed_db_row( $client_id, $lunch, 66, 'Oct 17, 2017' );
    $this->seed_db_row( $client_id, $dinner, 66, 'Oct 17, 2017' );
  }

  function seed_db_row( $client_id, $product_id, $meal_id, $date ) {
    global $wpdb;
    $time = strtotime( $date );
    $order_date = date( 'Y-m-d 00:00:00', $time );
    $sql = 'INSERT INTO gourmet_mapping ( user_id, product_id, meal_id, order_date )
      VALUES ( ' . $client_id . ', ' . $product_id . ', ' . $meal_id . ', "' . $order_date . '" ) ';
    // echo $sql;
    $wpdb->query( $sql );

  }

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

  function meal_calendar() {
    $client_id = isset( $_REQUEST['cid'] ) ? intval( $_REQUEST['cid'] ) : 0;
    if ( 0 === $client_id ) {
      $this->seed_database();
      $this->list_clients();
    } else {
      $this->list_date_lines( $client_id );
    }
  }

  function list_date_lines( $client_id ) {
    if ( isset( $_POST['meal_1'] ) )  {
      foreach( $_POST as $key => $val ) {
        $day = str_replace( 'meal_', '', $key );
        $date = '';
        // need this for lunch and dinner
        $sql = "INSERT INTO gourmet_mapping ( user_id, product_id, meal_id, order_date )
          VALUES ( ' . $client_id . ', ' . intval( $val ) . ', ' . intval( $val ) . ', ' . $date . ' ) ";
        echo $day . ' .. ' . $val . ', ';
      }
    }
    $month = isset( $_REQUEST['mo'] ) ? intval( $_REQUEST['mo'] ) : 0;
    $mo_now = date( 'M' );
    if ( 1 === $month ) {
      $mo_next = strtotime( $mo_now . ' 1, ' . date( 'Y' ) ) + 3600*24*45;
      $mo_now = date( 'M', $mo_next );
    }
    $meals = $this->get_meals();
    echo '<form method="POST" action="/wp-admin/admin.php?page=meal-calendar&cid=2&mo=1">' . "\n";
    // echo '<input type="hidden" name="cid" value="' . $client_id . '" >';
    // echo '<input type="hidden" name="mo" value="' . $month . '" >';
    // echo '<input type="hidden" name="page" value="meal-calendar">' . "\n";
    //wp_nonce_field( PM_LAYOUT_URI, 'pm_layout_noncename' );
    echo '<table>' . "\n";
    for ( $x = 1; $x <= 31; $x ++ ) {
      $day = strtotime( $mo_now . ' ' . $x . ', ' . date( 'Y' ) );//date( 'M d' );
      $date = date( 'M d', $day );
      $mo = date( 'M', $day );
      if ( $mo !== $mo_now ) {
        break;
      }
      echo '<tr>' . "\n";
      echo '<td>' . $date . '</td>' . "\n";
      echo '<td><select name="meal_' . $x . '" id="">';
      foreach ( $meals as $meal ) {
        echo '<option value="' . $meal->ID . '">' . $meal->post_title . '</option>' . "\n";
      }
      echo '</select></td>' . "\n";
      echo '</tr>' . "\n";
    }
    echo '</table>' . "\n";
    echo '<input type="submit" value="Search" class="button button-primary">' . "\n";
    echo '</form>' . "\n";
  }

  function get_meals() {
    $args = array(
      'post_status' => 'private',
      'post_type' => 'product',
      'posts_per_page' => 100,
    );
    $meals = get_posts( $args );
    return $meals;
  }

  function list_clients() {
    $clients = $this->get_clients();
    echo '<table>' . "\n";
    foreach ( $clients as $client ) {
      echo '<tr>' . "\n";
      echo '<td>' . $client->display_name . '</td>' . "\n";
      echo '<td><a href="/wp-admin/admin.php?page=meal-calendar&cid=' . $client->ID . '&mo=0">This Month</a></td>' . "\n";
      echo '<td><a href="/wp-admin/admin.php?page=meal-calendar&cid=' . $client->ID . '&mo=1">Next Month</a></td>' . "\n";
      echo '</tr>' . "\n";
    }
    echo '</table>' . "\n";
  }

  function get_clients() {
    $args = array( 'role' => 'customer' );
    $clients = get_users( $args );
    return $clients;
  }

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

// add table

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

  function get_order_items( $order_id ) {
    $order = new WC_Order( $order_id );
    $items = $order->get_items();
    return $items;
  }
}
