<?php

/**
 * This file is responsible for handling all
 * of the logic for the single-brand template.
 * 
 * @package WordPress
 * @subpackage BrandsCustomPostType
 * @author Waqar Haider <waqarhaider783@yahoo.com>
 * @since Brands Custom Post Type 0.0.1
 */

/**
 * Function to fetch categories for a given product ID
 * 
 * @param int $PRODUCT_ID - Product ID to get categories for
 * @return string[]|bool - array of product category information
 */
function get_product_categories($PRODUCT_ID) {
  $_fetched_categories = get_the_terms($PRODUCT_ID, 'product_cat');
  if(empty($_fetched_categories)) return false;
  $_categories = array();
  foreach($_fetched_categories as $_category):
    $_category_data = array(
      'id'    => $_category->term_id,
      'name'  => $_category->name,
      'slug'  => $_category->slug
    );
    if($_category_data['name'] === 'Best Sellers'):
      array_unshift($_categories, $_category_data);
    else:
      array_push($_categories, $_category_data);
    endif;
  endforeach;
  return $_categories;
}
/**
 * Function to fetch product categories registered with the
 * current brand
 * 
 * @param int $PARENT_ID
 * @return array|bool - false if no categories are found, array otherwise
 */
function get_product_categories_from_parent($PARENT_ID) {
  $_children = array();
  $_fetched_children = new WP_Query(
    array(
      'post_type' => 'brand',
      'post_parent' => $PARENT_ID,
      'numberposts' => -1
    )
  );
  if(empty($_fetched_children->posts)) return false;
  foreach($_fetched_children->posts as $_child) {
    $_child_details = array(
      'title' => $_child->post_title,
      'slug'  => $_child->post_name
      // 'slug'  => $_child->post_name === 'best-sellers' ? '' : $_child->post_name
    );

    if($_child_details['slug'] !== 'best-sellers'):
      array_push($_children, $_child_details);
    else:
      array_unshift($_children, $_child_details);
    endif;
  }
  return $_children;
}
/**
 * Function to fetch all relevant products pertaining to a brand
 * 
 * @param string $POST_TYPE - CPT name for current post
 * @param int $POST_ID - current post ID
 * @return object - products related to the current brand
 */
function get_brand_products($POST_TYPE, $POST_ID) {
  $_fetched_posts =  new WP_Query(
    array(
      'post_type'   => 'product',
      'post_status' => 'publish',
      'meta_key'    => $POST_TYPE,
      'meta_value'  => $POST_ID,
    )
  );
  return $_fetched_posts->posts;
}
/**
 * Function to loop through all products related to a brand and find the ones
 * that need to be displayed for the current display category
 * 
 * @param $BRAND_SLUG
 * @param $POST_TYPE
 * @param $DISPLAY_CATEGORY
 * 
 * @return array - array of products
 */
function set_brand_products($BRAND_ID, $POST_TYPE, $DISPLAY_CATEGORY) {
  $_fetched_products = get_brand_products($POST_TYPE, $BRAND_ID);
  $_products = array();
  foreach($_fetched_products as $_product):
    $_product_categories = get_the_terms($_product->ID, 'product_cat');
    foreach($_product_categories as $_product_category):
      if($_product_category->slug === $DISPLAY_CATEGORY) array_push($_products, $_product->ID);
    endforeach;
  endforeach;
  return implode(',', $_products);
}
/**
 * Function to set post metadata for further processing prior
 * to rendering the page
 * 
 * @param WP_Post $CURRENT_POST
 * @return array - Mixed array containing meta data to be used by the page
 */
function get_post_data($CURRENT_POST) {
  $_has_parent = wp_get_post_parent_id($CURRENT_POST) ? true : false;
  $_post_id = $_has_parent ? wp_get_post_parent_id($CURRENT_POST) : $CURRENT_POST->ID;
  $_post_data = array(
    'post_id'             => $_post_id,
    'post_type'           => $CURRENT_POST->post_type,
    'post_name'           => get_post($_post_id)->post_name,
    'display_category'    => $_has_parent ? get_post($CURRENT_POST)->post_name : get_product_categories_from_parent($_post_id)[0]['slug'],
    'title'               => get_the_title($_post_id),
    'brand_logo'          => get_field('brand_logo', $_post_id)['url'],
    'brand_introduction'  => get_field('brand_introduction', $_post_id),
    'brand_details'       => array(
      'passage_one'       => get_field('passage_one', $_post_id),
      'passage_two'       => get_field('passage_two', $_post_id),
      'passage_three'     => get_field('passage_three', $_post_id),
      'brand_image_one'   => get_field('brand_image_one', $_post_id) ? get_field('brand_image_one')['url'] : null,
      'brand_image_two'   => get_field('brand_image_two', $_post_id) ? get_field('brand_image_two', $_post_id)['url'] : null,
      'brand_image_three' => get_field('brand_image_three', $_post_id) ? get_field('brand_image_three', $_post_id)['url'] : null,
      'brand_image_four'  => get_field('brand_image_four', $_post_id) ? get_field('brand_image_four', $_post_id)['url'] : null,
    ),
    'post_main_url'       => get_permalink($_post_id),
    'product_categories'  => get_product_categories_from_parent($_post_id),
    'products'            => set_brand_products(
      $_post_id,
      $CURRENT_POST->post_type,
      $_has_parent ? get_post($CURRENT_POST)->post_name : get_product_categories_from_parent($_post_id)[0]['slug']
    )
  );
  return $_post_data;
}
/**
 * Function to determine which category link is "active"
 * 
 * @param $DISPLAY_CATEGORY - display category slug
 * @param $CATEGORY_SLUG
 * 
 * @return bool - true if active, false otherwise
 */
function check_active_category($DISPLAY_CATEGORY, $CATEGORY_SLUG) {
  $_active = false;
  if($CATEGORY_SLUG === $DISPLAY_CATEGORY) $_active = true;
  if($DISPLAY_CATEGORY === 'best-sellers' && $CATEGORY_SLUG === '') $_active = true;
  return $_active;
}
/**
 * Put together a comprehensive data object to be used by the front-end
 */
$post_meta = get_post_data($post);