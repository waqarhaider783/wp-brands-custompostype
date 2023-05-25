<?php
/**
 * Template for displaying brand post type
 * 
 * @package WordPress
 * @subpackage BrandsCustomPostType
 * @author Waqar Haider <waqarhaider783@yahoo.com>
 * @since Brands Custom Post Type 0.0.1
 */
/**
* Generic site header
*/
get_header();
/**
 * Require the internals file to process data and expose
 * $post_meta to the front-end that contains all of the
 * information needed to render all brand pages
 */
require_once __DIR__ . '/internals.php';
?>
  <div class="boxed">
    <h1 class="brand__title">
      <?php echo $post_meta['title'] ?>
    </h1>
    <section class="page-opener brand">
      <div class="brand-logo-container brand__logo-container">
        <img src="<?php echo $post_meta['brand_logo'] ?>" alt="<?php echo $post_meta['title'] ?>" class="brand-logo brand__logo">
      </div> <!-- .brand__logo-container -->
      <div class="brand-introduction-container brand__introduction-container">
        <span class="brand__introduction">
          <?php echo $post_meta['brand_introduction'] ?>
        </span>
      </div> <!-- .brand__introduction-container -->
      <figure class="brand-background-accent-container brand__background-accent-container">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../../assets/background-accent.svg' ?>" alt="" class="brand-background-accent brand__background-accent">
      </figure> <!-- .brand__background-accent-container -->
    </section> <!-- .brand -->
    <section class="product-filter">
      <div class="product-filter__inner">
        <ul class="product-filter__list">
          <?php
          /**
           * Render product categories if categories
           * are found
           */
          if(!empty($post_meta['product_categories'])):
            foreach($post_meta['product_categories'] as $_category):
            ?>
              <li class="product-filter__list-item">
                <a
                  href="<?php echo $post_meta['post_main_url'] . $_category['slug'] ?>"
                  alt="<?php echo $_category['title'] ?>"
                  class="<?php echo check_active_category($post_meta['display_category'], $_category['slug']) ? 'active' : '' ?>"
                >
                  <?php echo $_category['title'] ?>
                </a>
              </li> <!-- .product-filter__list-item -->
            <?php endforeach; 
          endif; ?>
        </ul> <!-- .product-filter__list -->
      </div> <!-- .product-filter__inner -->
    </section> <!-- .product-filter -->
      <section class="products">
        <div class="products__inner">
        <?php
        /**
         * Render products or a notice that tells people there
         * are no products to display
         */
        if($post_meta['products']):
            /**
             * WP Bakery Page Builder shortcode to render products
             */
            echo do_shortcode("[products columns='4' ids={$post_meta['products']}]");
        else: ?>
          <h4 class="products__not-found">
            No Products to Display
          </h4>
        <?php endif; ?>
        </div> <!-- .products__inner -->
      </section> <!-- .products -->
    <section class="brand-details">
      <?php if($post_meta['brand_details']['passage_one']): ?>
        <?php echo $post_meta['brand_details']['passage_one'] ?>
      <?php endif; ?>
      <div class="brand-details__featured-images-container">
        <?php if($post_meta['brand_details']['brand_image_one']): ?>
          <figure class="brand-details__featured-image--full">
            <img src="<?php echo $post_meta['brand_details']['brand_image_one'] ?>" alt="Featured Image">
          </figure>
        <?php endif; ?>
      </div> <!-- .brand-details__featured-images-container -->
      <?php if($post_meta['brand_details']['passage_two']): ?>
        <?php echo $post_meta['brand_details']['passage_two'] ?>
      <?php endif; ?>
      <div class="brand-details__featured-images-container">
        <?php if($post_meta['brand_details']['brand_image_two']): ?>
          <figure class="brand-details__featured-image--half">
            <img src="<?php echo $post_meta['brand_details']['brand_image_two'] ?>" alt="Featured Image">
          </figure>
        <?php endif; ?>
        <?php if($post_meta['brand_details']['brand_image_three']): ?>
        <figure class="brand-details__featured-image--quarter">
          <img src="<?php echo $post_meta['brand_details']['brand_image_three'] ?>" alt="Featured Image">
        </figure>
        <?php endif; ?>
        <?php if($post_meta['brand_details']['brand_image_four']): ?>
        <figure class="brand-details__featured-image--quarter">
          <img src="<?php echo $post_meta['brand_details']['brand_image_four'] ?>" alt="Featured Image">
        </figure>
        <?php endif; ?>
      </div> <!-- .brand-details__featured-images-container -->
      <?php if($post_meta['brand_details']['passage_three']): ?>
        <?php echo $post_meta['brand_details']['passage_three'] ?>
      <?php endif; ?>
    </section> <!-- .brand-details -->
  </div> <!-- .boxed -->
<?php
/**
 * Generic site footer
 */
get_footer();