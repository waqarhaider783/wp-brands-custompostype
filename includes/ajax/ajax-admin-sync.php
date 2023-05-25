<?php
/**
 * This file declares a function to asyncronously sync
 * product categories with brands and create posts under
 * the "Brand" post type corresponding to each brand and
 * its associated products.
 * 
 * @author Waqar Haider <waqarhaider783@yahoo.com>
 * @since 1.0
 */

/**
 * Security Measure
 */
if(!defined('ABSPATH')) exit;

/**
 * Function that handles the syncing of brands to product categories.
 * This function only gets called via the admin panel.
 */
function v8_admin_ajax_sync() {

  if($_GET['action'] === 'sync_brand_categories'):

    try {

      /**
       * @var WP_Query $POSTS all brand posts
       */
      $POSTS = new WP_Query(
        array(
          'post_type'       => 'brand',
          'posts_per_page'  => -1
        )
      );
      /**
       * @var array $BRANDS array of brand parent posts
       */
      $BRANDS = array();
      /**
       * @var array $CATEGORIES array of brand child posts
       */
      $CATEGORIES = array();
      /**
       * Populate brand and brand category arrays
       */
      foreach($POSTS->posts as $_post):

        if(wp_get_post_parent_id($_post->ID)):

          array_push($CATEGORIES, $_post);

        else:

          array_push($BRANDS, $_post);

        endif;

      endforeach; // $_post
      /**
       * Flush all brand categories so that they can be
       * repopulated
       */
      foreach($CATEGORIES as $_category) wp_delete_post($_category->ID, true);

      /**
       * Loop through brands to fetch and set new brand
       * categories
       */
      foreach($BRANDS as $_brand):

        /**
         * @var array $_categories_already_added array of category ID's
         * that is checked every loop before deciding whether or not to
         * add a product category as a brand category
         */
        $_categories_already_added = array();
        /**
         * @var WP_Query $_brand_products all products associated with
         * the current brand
         */
        $_brand_products = new WP_Query(
          array(
            'post_type'       => 'product',
            'post_status'     => 'publish',
            'meta_name'       => $_brand->post_type,
            'meta_value'      => $_brand->ID,
            'posts_per_page'  => -1
          )
        );

        /**
         * Loop through all products assigned to the brand
         * to fetch categories and process them for
         * post creation
         */
        foreach($_brand_products->posts as $_brand_product):

          /**
           * @var array $_product_categories categories associated
           * with current product
           */
          $_product_categories = get_the_terms($_brand_product->ID, 'product_cat');

          /**
           * Loop through a single product's categories
           */
          foreach($_product_categories as $_product_category):
            /**
             * Skip this loop  if a post related to the brand category
             * has already been added
             */
            if(in_array($_product_category->term_id, $_categories_already_added)) continue;
            /**
             * Add the category to a running list of categories added to be
             * referenced in the next iteration of this loop
             */
            array_push($_categories_already_added, $_product_category->term_id);
            /**
             * @var WP_Post $_category_post new post object corresponding to
             * a brand category
             */
            $_new_category_post = wp_insert_post(
              array(
                'post_type'   => 'brand',
                'post_parent' => $_brand->ID,
                'post_name'   => $_product_category->slug,
                'post_title'  => $_product_category->name
              )
            );
            wp_publish_post($_new_category_post);

          endforeach; // $_product_category

        endforeach; // $_brand_product

      endforeach; // $_brand

      echo json_encode(
        array(
          'error' => false
        )
      );

    } catch (Exception $error) {

      echo json_encode(
        array(
          'error' => true,
          'data'  => $error
        )
      );

    } finally {

      wp_die();

    }

  endif; // sync_brand_categories

}