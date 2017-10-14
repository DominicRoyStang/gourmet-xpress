<?php
$gourmet_meal_mapping = new GourmetMealMapping();

class GourmetMealMapping {

  function __construct() {
    add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

  function admin_init() {
  	add_meta_box( 'gourmet_order_meta_box', 'Meal Ordered', array( $this, 'order_meta_box' ), 'shop_order', 'main', 'high' );
  }

// add table

  function order_meta_box() {
    global $post;
    $order_id = $post->ID;
    $user_id = get_post_meta( $order_id, '_customer_user', true );
    $order_date = get_post_meta( $order_id, 'order_date', true );
    echo '<p><strong>'.__('Date').':</strong> ' . esc_html( get_post_meta( $order_id, 'order_date', true ) ) . '</p>';
    echo '<p><strong>'.__('Notes').':</strong> ' . esc_html( $post->post_excerpt ) . '</p>';
    echo '<p><strong>'.__('Meal').':</strong> ';
    $items = $this->get_order_items( $order_id );
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
      echo esc_html( $meal_name . ' x ' . $quantity );
    }
    echo '</p>';
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
