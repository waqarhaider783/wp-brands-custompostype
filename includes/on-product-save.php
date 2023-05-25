<?php

function update_brand_categories_on_product_save($PRODUCT_ID) {
  
  /**
   * @var WP_Term[] $_product_categories product categories
   */
  $_product_categories = get_the_terms($PRODUCT_ID, 'product_cat');
  /**
   * @var int $_product_brand brand ID
   */
  $_product_brand = get_field('brand', $PRODUCT_ID);
  /**
   * @var WP_Query $_brand_categories all child posts of the product's
   * brand
   */
  $_brand_categories = new WP_Query(
    array(
      'post_type'       => 'brand',
      'post_parent'     => $_product_brand,
      'posts_per_page'  => -1
    )
  );

  foreach($_product_categories as $_product_category):
    /**
     * @var boolean $_brand_category_exists
     */
    $_brand_category_exists = false;

    /**
     * If brand categories have already been created, loop through them
     * to see if the current cateory is already created. If not, move on
     */
    if(!empty($_brand_categories)):

      foreach($_brand_categories->posts as $_brand_category):
        
        if($_brand_category->post_title === $_product_category->name) $_brand_category_exists = true;

      endforeach; // $_brand_category

    endif;

    if(!$_brand_category_exists):

      /**
       * @var WP_Post new post object pertaining to current product category
       */
      $_new_category_post = wp_insert_post(
        array(
          'post_type'   => 'brand',
          'post_parent' => $_product_brand,
          'post_title'  => $_product_category->name,
          'post_name'   => $_product_category->slug
        )
      );

      wp_publish_post($_new_category_post);

    endif;

  endforeach; // $_product_category

}