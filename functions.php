function porus_child_change_product_price_display( $price , $product) {
    if ( (215 !== $product->id ) && (209 !== $product->id )) {
    $price .= ' per  dozen';
    
    } else {
		$price = $price;
	}
   return $price;
}
	
add_filter( 'woocommerce_get_price_html', 'porus_child_change_product_price_display', 10,2 );
add_filter( 'woocommerce_cart_item_price', 'porus_child_change_product_price_display', 10,2 );
