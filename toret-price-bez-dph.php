<?php

/**

 * Plugin Name:       Toret Custom Price
 * Plugin URI:        http://toret.cz
 * Description:       Plugin pro zobrazení­ ceny s a bez DPH
 * Version:           1.0
 * Author:            Vladislav Musílek
 * Author URI:        http://toret.cz
 * Text Domain:       toret-custom-price
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
	die;
}


add_filter( 'woocommerce_get_price_html', 'toret_display_both_prices', 99999, 2 );

function toret_display_both_prices( $price, $product ){

	if( $product->product_type == 'variable' ){
		$price = toret_price_for_variable_product( $price, $product );
	}else{
		$price = toret_price_for_simple_product( $price, $product );
	}
	return $price;	
}

function toret_price_for_simple_product( $price, $product ){

	$display_price         = $product->get_display_price();
	$display_regular_price = $product->get_display_price( $product->get_regular_price() );

	$to   = $product->get_price_including_tax( 1, $product->get_price() ); 
	$from = $product->get_price_including_tax( 1, $product->get_regular_price() );

	$to_without   = $product->get_price_excluding_tax( 1, $product->get_price() ); 
	$from_without = $product->get_price_excluding_tax( 1, $product->get_regular_price() );

	$display_price_with    = $product->get_price_including_tax( 1, $product->get_price() );
	$display_price_without = $product->get_price_excluding_tax( 1, $product->get_price() );

		if ( $product->get_price() > 0 ) {

			if ( $product->is_on_sale() && $product->get_regular_price() ) {

				$price = '<span class="custom-price-with-vat"><del>' . ( ( is_numeric( $from ) ) ? wc_price( $from ) : $from ) . ' vč DPH</del> <ins>' . ( ( is_numeric( $to ) ) ? wc_price( $to ) : $to ) . ' vč DPH</ins></span>';

				$price .= '<br /><span class="custom-price-without-vat"><del>' . ( ( is_numeric( $from_without ) ) ? wc_price( $from_without ) : $from_without ) . ' bez DPH</del> <ins>' . ( ( is_numeric( $to_without ) ) ? wc_price( $to_without ) : $to_without ) . ' bez DPH</ins></span>';

				//$price .= $product->get_price_html_from_to( $display_regular_price, $display_price ) . $product->get_price_suffix();

				$price = apply_filters( 'woocommerce_sale_price_html', $price, $product );

			} else {

				//$price .= wc_price( $display_price ) . $product->get_price_suffix();

				$price = '<span class="custom-price-with-vat">' . wc_price( $display_price_with ) . ' vč DPH</span>';
				$price .= '<br /><span class="custom-price-without-vat">' . wc_price( $display_price_without ) . ' bez DPH</span>';

				$price = apply_filters( 'woocommerce_price_html', $price, $product );

			}

		} elseif ( $product->get_price() === '' ) {

			$price = apply_filters( 'woocommerce_empty_price_html', '', $product );

		} elseif ( $product->get_price() == 0 ) {

			if ( $product->is_on_sale() && $product->get_regular_price() ) {

				$price .= $product->get_price_html_from_to( $display_regular_price, __( 'Free!', 'woocommerce' ) );
				$price = apply_filters( 'woocommerce_free_sale_price_html', $price, $product );

			} else {

				$price = '<span class="amount">' . __( 'Free!', 'woocommerce' ) . '</span>';
				$price = apply_filters( 'woocommerce_free_price_html', $price, $product );

			}

		}

	return $price;
}







function toret_price_for_variable_product( $price, $product ){

	$prices = $product->get_variation_prices( true );
	$prices_with = toret_get_variation_prices( $product, true, 'incl' );
	$prices_without = toret_get_variation_prices( $product, true, 'excl' );

		// No variations, or no active variation prices

		if ( $product->get_price() === '' || empty( $prices['price'] ) ) {

			$price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );

		} else {

			$min_price = current( $prices['price'] );
			$max_price = end( $prices['price'] );

			$min_price_with = current( $prices_with['price'] );
			$max_price_with = end( $prices_with['price'] );

			$min_price_without = current( $prices_without['price'] );
			$max_price_without = end( $prices_without['price'] );

			$price     = $min_price !== $max_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price ), wc_price( $max_price ) ) : wc_price( $min_price );

			$price_with     = $min_price_with !== $max_price_with ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price_with ), wc_price( $max_price_with ) ) : wc_price( $min_price_with );

			$price_without     = $min_price_without !== $max_price_without ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price_without ), wc_price( $max_price_without ) ) : wc_price( $min_price_without );

			$is_free   = $min_price == 0 && $max_price == 0;

			if ( $product->is_on_sale() ) {

				$min_regular_price = current( $prices['regular_price'] );
				$max_regular_price = end( $prices['regular_price'] );

				$min_regular_price_with = current( $prices_with['regular_price'] );
				$max_regular_price_with = end( $prices_with['regular_price'] );

				$min_regular_price_without = current( $prices_without['regular_price'] );
				$max_regular_price_without = end( $prices_without['regular_price'] );

				$regular_price_with     = $min_regular_price_with !== $max_regular_price_with ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_regular_price_with ), wc_price( $max_regular_price_with ) ) : wc_price( $min_regular_price_with );

				$regular_price_without     = $min_regular_price_without !== $max_regular_price_without ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_regular_price_without ), wc_price( $max_regular_price_without ) ) : wc_price( $min_regular_price_without );

				$price = '<span class="custom-price-with-vat"><del>' . ( ( is_numeric( $regular_price_with ) ) ? wc_price( $regular_price_with ) : $regular_price_with ) . ' vÄŤ DPH</del> <ins>' . ( ( is_numeric( $price_with ) ) ? wc_price( $price_with ) : $price_with ) . ' vÄŤ DPH</ins></span>';

				$price .= '<br /><span class="custom-price-without-vat"><del>' . ( ( is_numeric( $regular_price_without ) ) ? wc_price( $regular_price_without ) : $regular_price_without ) . ' bez DPH</del> <ins>' . ( ( is_numeric( $price_without ) ) ? wc_price( $price_without ) : $price_without ) . ' bez DPH</ins></span>';

			} elseif ( $is_free ) {

				$price = apply_filters( 'woocommerce_variable_free_price_html', __( 'Free!', 'woocommerce' ), $product );

			} else {

				$price_custom_with = $min_price !== $max_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price_with ), wc_price( $max_price_with ) ) : wc_price( $min_price_with );

				$price_custom_without = $min_price !== $max_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price_without ), wc_price( $max_price_without ) ) : wc_price( $min_price_without );

				$price = '<span class="custom-price-with-vat">' . $price_custom_with . ' vč DPH</span>';
				$price .= '<br /><span class="custom-price-without-vat">' . $price_custom_without . ' bez DPH</span>';

			}
		}

		return $price;

}


function toret_get_variation_prices( $product, $display = false, $tax_mod = 'incl' ) {

				$prices         = array();
				$regular_prices = array();
				$sale_prices    = array();
				$variation_ids  = $product->get_children( true );

				foreach ( $variation_ids as $variation_id ) {

					if ( $variation = $product->get_child( $variation_id ) ) {

						$price         = $variation->price;
						$regular_price = $variation->regular_price;
						$sale_price    = $variation->sale_price;

						// Skip empty prices
						if ( '' === $price ) {
							continue;
						}

						// If sale price does not equal price, the product is not yet on sale
						if ( $sale_price === $regular_price || $sale_price !== $price ) {
							$sale_price = $regular_price;
						}

						// If we are getting prices for display, we need to account for taxes
						if ( $display ) {

							if ( 'incl' === $tax_mod ) {
								$price         = '' === $price ? ''         : $variation->get_price_including_tax( 1, $price );
								$regular_price = '' === $regular_price ? '' : $variation->get_price_including_tax( 1, $regular_price );
								$sale_price    = '' === $sale_price ? ''    : $variation->get_price_including_tax( 1, $sale_price );
							} else {
								$price         = '' === $price ? ''         : $variation->get_price_excluding_tax( 1, $price );
								$regular_price = '' === $regular_price ? '' : $variation->get_price_excluding_tax( 1, $regular_price );
								$sale_price    = '' === $sale_price ? ''    : $variation->get_price_excluding_tax( 1, $sale_price );
							}
						}

						$prices[ $variation_id ]         = wc_format_decimal( $price, wc_get_price_decimals() );
						$regular_prices[ $variation_id ] = wc_format_decimal( $regular_price, wc_get_price_decimals() );
						$sale_prices[ $variation_id ]    = wc_format_decimal( $sale_price . '.00', wc_get_price_decimals() );
					}
				}

				asort( $prices );
				asort( $regular_prices );
				asort( $sale_prices );

				$prices_array = array(
					'price'         => $prices,
					'regular_price' => $regular_prices,
					'sale_price'    => $sale_prices,
				);

		/**

		 * Return the values.

		 */

		return $prices_array;

	}




add_action( 'woocommerce_variation_sale_price_html', 'toret_variation_sale_price', 99999, 2 );
function toret_variation_sale_price( $price, $product){

	$to   = $product->get_price_including_tax( 1, $product->get_price() ); 
	$from = $product->get_price_including_tax( 1, $product->get_regular_price() );

	$to_without   = $product->get_price_excluding_tax( 1, $product->get_price() ); 
	$from_without = $product->get_price_excluding_tax( 1, $product->get_regular_price() );

	$price = '<span class="custom-price-with-vat"><del>' . ( ( is_numeric( $from ) ) ? wc_price( $from ) : $from ) . ' vč DPH</del> <ins>' . ( ( is_numeric( $to ) ) ? wc_price( $to ) : $to ) . ' vč DPH</ins></span>';

	$price .= '<br /><span class="custom-price-without-vat"><del>' . ( ( is_numeric( $from_without ) ) ? wc_price( $from_without ) : $from_without ) . ' bez DPH</del> <ins>' . ( ( is_numeric( $to_without ) ) ? wc_price( $to_without ) : $to_without ) . ' bez DPH</ins></span>';

	return $price;

}



add_action( 'woocommerce_variation_price_html', 'toret_variation_price', 99999, 2 );
function toret_variation_price( $price, $product){

	$display_price_with    = $product->get_price_including_tax( 1, $product->get_price() );
	$display_price_without = $product->get_price_excluding_tax( 1, $product->get_price() );

	$price = '<span class="custom-price-with-vat">' . wc_price( $display_price_with ) . ' vč DPH</span>';
	$price .= '<br /><span class="custom-price-without-vat">' . wc_price( $display_price_without ) . ' bez DPH</span>';

	return $price;

}
