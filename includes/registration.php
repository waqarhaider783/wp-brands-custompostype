<?php

/**
 * Register Brands custom post type with the name
 * v8-brand to avoid conflicts with any past or
 * future plugin installations
 */
function v8_init_brands_post_type() {

  /**
   * Supported and relevant labels as defined by WP
   */
  $labels = array(
    'name' => _x('Brands', 'brand'),
    'singular_name' => _x('Brand', 'brand'),
    'add_new' => __('Add New'),
    'add_new_item' => __('Add New Brand'),
    'edit_item' => __('Edit Brand'),
    'new_item' => __('New Brand'),
    'view_item' => __('View Brand'),
    'search_items' => __('Search Brands'),
    'not_found' => __('No brands found'),
    'not_found_in_trash' => __('No brands found in trash'),
    'parent_item_colon' => '-',
    'menu_name' => 'Brands'
  );

  /**
   * Supported and relevant args as defined by WP
   */
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-networking',
    'has_archive' => false,
    'hierarchical' => true,
    'rewrite' => array(
      'slug' => 'brand',
      'with_front' => false
    ),
    'supports' => array('title', 'thumbnail', 'page-attributes')
  );

  /**
   * Call WP's native function to register the post type
   */
  register_post_type('brand', $args);

}

/**
 * Function to set up a single post template for v8-movies
 */
function v8_single_post_template($template) {

  global $post;

  /**
   * Check to see if the current post type is v8-brand
   */
  if('brand' === $post->post_type && locate_template(array('single-brand.php')) !== $template):
    return plugin_dir_path(__FILE__) . 'templates/single-brand.php';
  endif;

  return $template;

}

/**
 * Function to add a "Sync" button to the top of this
 * custom post type's post-list page.
 */
function v8_add_sync_button() {
  global $current_screen;
  if('brand' !== $current_screen->post_type) return;
  ?>
  <script type='text/javascript'>
    (() => {

      /**
       * Programmatically creating a button to start
       * the sync process
       */
      if(document.body.classList.contains('edit-php')) {
        const wrap = document.querySelector('.wrap');
        const button = document.createElement('a');
        button.classList.add('page-title-action');
        button.classList.add('brands-sync-modal-opener');
        button.href = '#';
        button.innerText = 'Sync Now';
        wrap.insertBefore(button, wrap.children[2]);

        /**
         * Modal styles for the Sync operation.
         */
        const modalStyles = `
          <style>
            .brands-sync-modal-container {
              position: fixed;
              display: none;
              place-items: center;
              place-content: center;
              background: rgba(0,0,0,0.4);
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              z-index: 99999;
            }
            .brands-sync-modal-container.active {
              display: flex;
            }
            .brands-sync-modal {
              max-width: 50%;
              padding: 25px;
              background: white;
              box-shadow: 0 0 10px rgba(0,0,0,0.5);
              position: relative;
            }
            .brands-sync-modal-closer {
              font-size: 24px;
              font-weight: 900;
              color: #000;
              padding: 10px;
              position: absolute;
              top: 0;
              right: 0;
            }
            .brands-sync-modal h3 {
              font-size: 36px;
              line-height: 1.5em;
            }
            .brands-sync-modal p, .brands-sync-modal li {
              font-size: 18px;
              line-height: 1.5em;
            }
            .brands-sync-modal ul {
              padding-left: 20px;
            }
            .brands-sync-modal li {
              list-style: disc;
            }
            .brands-sync-modal button {
              padding: 10px 20px;
              font-size: 24px;
              border-width: 2px;
              border-style: solid;
              margin-top: 20px;
            }
            .brands-sync-positive {
              background-color: #2271b1;
              color: white;
            }
            .brands-sync-negative {
              background-color: transparent;
              border-color: red;
              color: red;
            }
          </style>
        `

        /**
         * HTML structure and content for Sync opration modal
         */
        const modal = `
          <section class='brands-sync-modal-container'>
            <div class='brands-sync-modal'>
              <div class='brands-sync-modal-content'>
                <h3>Are you sure you want to sync data now?</h3>
                <p>This process can take anywhere between a few seconds to several minutes depending on how many products
                and categories you have. Here are some potential use cases for the sync button:</p>
                <ul>
                  <li>If you've deleted a whole product category</li>
                  <li>If you've renamed a product category</li>
                  <li>If the changes you expected to occur automatically did not take place</li>
                </ul>
                <p>Do you want to proceed with this action now?</p>
                <button class='brands-sync-positive brands-sync-confirm'>Yes</button>
                <button class='brands-sync-negative'>No</button>
              </div> <!-- .brands-sync-modal-content -->
            </div> <!-- .brands-sync-modal -->
            </section> <!-- .brands-sync-modal-contianer -->
            `

        wrap.innerHTML+= modalStyles;
        wrap.innerHTML+= modal;

        /**
         * modalContent definition
         */
        const modalContent = () => document.querySelector('.brands-sync-modal-content');

        /**
         * Open modal event handler
         */
        const openSyncModal = e => {
          e.preventDefault();
          document.querySelector('.brands-sync-modal-container').classList.add('active');
        }

        /**
         * Close Modal event handler
         */
        const closeSyncModal = e => {
          e.preventDefault();
          document.querySelector('.brands-sync-modal-container').classList.remove('active');
        }

        /**
           * Event handler to initiate asyncronouse data fetching
           * to then refresh the page with sync brands and brand
           * categories
           */
        const syncBrandCategories = async e => {
          e.preventDefault();
          const currentModalContent = modalContent().innerHTML;
          modalContent().innerHTML = `
            <h3>Syncing data now. If the sync succeeds, the page will reload, otherwise an error will be displayed here.</h3>
          `
          let _data = await fetch("<?php echo admin_url('admin-ajax.php?action=sync_brand_categories') ?>");
          _data = await _data.json();
          if(_data.error) {
            modalContent().innerHTML = `
              <h3>Sync failed!</h3>
              <p>Here's what went wrong:</p>
              <pre>${JSON.stringify(_data.response, null, 2)}</pre>
              <button class='brands-sync-positive brands-sync-confirm'>Try Again</button>
              <button class='brands-sync-negative'>Exit</button>
            `
          } else {
            location.reload();
          }
        }

        /**
         * Aggregation of all events that can trigger within
         * sync lifecycle
         */
        const syncEvents = async e => {
          if(e.target.classList.contains('brands-sync-modal-opener')) openSyncModal(e);
          if(e.target.classList.contains('brands-sync-modal-container')) closeSyncModal(e);
          if(e.target.classList.contains('brands-sync-negative')) closeSyncModal(e);
          if(e.target.classList.contains('brands-sync-confirm')) syncBrandCategories(e);
        }

        wrap.addEventListener('click', syncEvents);
      }

    })();
  </script>
  <?php
}