<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Vendors_Admin
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_Vendors_Admin' ) ) {

    class YITH_Vendors_Admin {

        /**
         * @var string The taxonomy name
         */
        protected $_taxonomy_name = '';

        /**
         * Construct
         */
        public function __construct() {
	        new YITH_Vendors_Backend();

	        $this->_taxonomy_name = YITH_Vendors()->get_taxonomy_name();

	        /* Taxonomy management */
	        add_action( $this->_taxonomy_name . '_add_form_fields', array( $this, 'add_taxonomy_fields' ), 1, 1 );
	        add_action( $this->_taxonomy_name . '_edit_form_fields', array( $this, 'edit_taxonomy_fields' ), 1, 1 );
	        add_filter( 'pre_insert_term', array( $this, 'check_duplicate_term_name' ), 10, 2 );
	        add_filter( 'edit_terms', array( $this, 'check_duplicate_term_name' ), 10, 2 );
	        add_action( 'edited_' . $this->_taxonomy_name, array( $this, 'save_taxonomy_fields' ), 10, 2 );
	        add_action( 'created_' . $this->_taxonomy_name, array( $this, 'save_taxonomy_fields' ), 10, 2 );
	        add_action( 'pre_delete_term', array( $this, 'remove_vendor_data' ), 10, 2 );
	        add_action( 'add_meta_boxes', array( $this, 'single_value_taxonomy' ) );
            add_filter( "manage_edit-{$this->_taxonomy_name}_columns", array( $this, 'get_columns' ) );

	        /* Allow html in taxonomy descriptions */
	        remove_filter( 'pre_term_description', 'wp_filter_kses' );
	        remove_filter( 'term_description', 'wp_kses_data' );

	        /* WooCommerce */
	        add_action( 'woocommerce_update_option', array( $this, 'change_product_vendors_admin_role' ), 10, 1 );
	        add_action( 'pre_user_query', array( $this, 'json_search_customer_name' ), 15 );

	        /* Vendor products management */
	        add_filter( 'request', array( $this, 'filter_product_list' ) );
	        add_filter( 'wp_count_posts', array( $this, 'vendor_count_posts' ), 10, 3 );
	        add_action( 'save_post', array( $this, 'add_vendor_taxonomy_to_product' ), 10, 2 );
	        add_action( 'current_screen', array( $this, 'disabled_manage_other_vendors_product' ) );

	        /* Grouped Products */
	        add_action( 'pre_get_posts', array( $this, 'filter_vendor_linked_products' ), 10, 1 );

	        /* Vendor media management */
	        add_filter( 'pre_get_posts', array( $this, 'remove_attachments' ), 10, 1 );

	        /* Vendor menu */
	        add_action( 'admin_menu', array( $this, 'menu_items' ) );

	        /* Vendor information management */
	        add_action( 'admin_init', array( $this, 'update_vendor_information' ) );

            /* Prvente WooCommerce Access Admin */
            add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ) );
        }

        /**
         * Only show vendor's products
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @param  arr $request Current request
         *
         * @return arr          Modified request
         * @since  1.0
         */
        public function filter_product_list( $request ) {
            global $typenow;

	        $vendor = yith_get_vendor( 'current', 'user' );

            if ( is_admin() && ! $vendor->is_super_user() && $vendor->is_user_admin() && 'product' == $typenow ) {
	            $request[ $vendor->term->taxonomy ] = $vendor->name;
            }

            return $request;
        }

        /**
         * Filter the post count for vendor
         *
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @param $counts   The post count
         * @param $type     Post type
         * @param $perm     The read permission
         *
         * @return arr  Modified request
         * @since    1.0
         * @use wp_post_count action
         */
        public function vendor_count_posts( $counts, $type, $perm ) {
	        $vendor = yith_get_vendor( 'current', 'user' );

            if ( ! $vendor || 'product' != $type || $vendor->is_super_user() || ! $vendor->is_user_admin() ) {
                return $counts;
            }

            /**
             * Get a list of post statuses.
             */
            $stati = get_post_stati();

            // Update count object
            foreach ( $stati as $status ) {
                $posts               = $vendor->get_products( "post_status=$status" );
                $counts->$status     = count( $posts );
            }

            return $counts;
        }

        /**
         * Add vendor to product
         *
         * @param       int $post_id Product ID
         *
         * @author      Andrea Grillo <andrea.grillo@yithemes.com>
         * @return      void
         * @since       1.0
         * @use         save_post action
         */
        public function add_vendor_taxonomy_to_product( $post_id, $post ) {
	        $vendor = yith_get_vendor( 'current', 'user' );

            if ( 'product' == $post->post_type && current_user_can( 'edit_post', $post_id ) && $vendor->has_limited_access() ) {
	            wp_set_object_terms( $post_id, $vendor->term->slug, $vendor->term->taxonomy, false );
            }
        }

        /**
         * Restrict vendors from editing other vendors' products
         *
         * @author      Andrea Grillo <andrea.grillo@yithemes.com>
         * @return      void
         * @since       1.0
         * @use         current_screen filter
         */
        public function disabled_manage_other_vendors_product() {
	        if ( isset( $_POST['post_ID'] ) || ! isset( $_GET['post'] ) ) {
		        return;
	        }

	        $vendor = yith_get_vendor( 'current', 'user' );
	        $product_vendor = yith_get_vendor( $_GET['post'], 'product' );  // If false, the product hasn't any vendor set

            if ( $vendor->has_limited_access() && false !== $product_vendor && $vendor->id != $product_vendor->id ) {
		        wp_die( sprintf( __( 'You do not have permission to edit this product. %1$sClick here to view and edit your products%2$s.', 'yith_wc_product_vendors' ), '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' ) );
	        }
        }

        /**
         * Remove the 'Unattached' media
         *
         * @param array $query The Query
         *
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return array         Modified views
         * @since    1.0
         */
        public function remove_attachments( $query = array() ) {
	        $vendor = yith_get_vendor( 'current', 'user' );

	        if ( $vendor->is_super_user() || ! $vendor->is_user_admin() ) {
				return;
	        }

	        global $pagenow, $wpdb;

	        $mode               = isset( $_GET['mode'] ) ? $_GET['mode'] : false;
	        $restrict           = false;
	        $is_attachment_page = isset( $_GET['attachment_id'] ) ? true : false;

	        /**
	         * Request Media Gallery List View by query string
	         */
	        if ( $mode && 'list' == $mode && 'upload.php' == $pagenow ) {
		        $restrict = true;
	        }

	        /**
	         * If not set $_GET['mode']:
	         *
	         * 1. Grid View Requested by query string
	         * 2. Last used View ( grid or list )
	         */
	        if ( ! $mode && ! $is_attachment_page ) {

		        $wp_list_table = _get_list_table( 'WP_Media_List_Table', array( 'screen' => 'upload' ) );

		        if ( 'query-attachments' == $wp_list_table->current_action() || 'upload.php' == $pagenow ) {
			        $restrict = true;
		        }
	        }

	        if ( $restrict ) {
		        $vendor_admin_ids = $vendor->get_admins();

		        if ( ! empty( $vendor_admin_ids ) ) {
			        $query->set( 'author__in', $vendor_admin_ids );
		        }
	        }
        }

        /**
         * Add items to dashboard menu
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @return void
         */
        public function menu_items() {
	        $vendor = yith_get_vendor( 'current', 'user' );

	        if ( ! $vendor->is_valid() || ! $vendor->has_limited_access() || ! $vendor->is_owner() ) {
		        return;
	        }

	        $args = apply_filters( 'yith_wc_product_vendors_details_menu_items',
		        array(
			        'parent_slug' => 'edit.php?post_type=product',
			        'page_title'  => __( 'Vendor Details', 'yith_wc_product_vendors' ),
			        'menu_title'  => __( 'Vendor Details', 'yith_wc_product_vendors' ),
			        'capability'  => 'edit_products',
			        'menu_slug'   => 'yith_vendor_details',
			        'function'    => array( $this, 'admin_details_page' )
		        )
	        );

	        extract( $args );

	        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, 'dashicons-id-alt', 30 );

            /* Remove Media Library */
            remove_menu_page( 'upload.php' );
        }

        /**
         * Get vendor admin template
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @return void
         */
        public function admin_details_page() {
            $vendor = yith_get_vendor( 'current', 'user' );
            yith_wcpv_get_template( 'vendor-admin', array( 'vendor' => $vendor ), 'admin' );
        }

        /**
         * Update vendor information
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @return void
         * @fire product_vendors_details_fields_save action
         */
        public function update_vendor_information() {
            if ( isset( $_POST['yith_vendor_admin_update_nonce'] ) && isset( $_POST['update_vendor_id'] ) ) {
                if ( ! wp_verify_nonce( $_POST['yith_vendor_admin_update_nonce'], 'yith_vendor_admin_update' ) ) {
                    wp_die( __( 'Cheatin&#8217; uh?' ) );
                }

                $vendor_id = $_POST['update_vendor_id'];

                if ( ! $vendor_id ) {
                    return;
                }

                $to_update_options = array();
                $taxonomy_name     = YITH_Vendors()->get_taxonomy_name();
                $option_name       = $taxonomy_name . '_' . $vendor_id;
                $vendor            = get_option( $option_name );

                /* === Option fields to update === */
                $to_update_options['admins']         = $vendor['admins'];
                $to_update_options['paypal_email']   = sanitize_email( $_POST['vendor_paypal_address'] );
                $to_update_options['enable_selling'] = $vendor['enable_selling'];

                update_option( $option_name, $vendor );

                /* === Update Taxonomy === */
                $args = array(
                    'name'        => sanitize_text_field( $_POST['vendor_name'] ),
                    'slug'        => sanitize_text_field( $_POST['vendor_slug'] ),
                    'description' => sanitize_text_field( $_POST['vendor_description'] ),
                );

                wp_update_term( $vendor_id, $taxonomy_name, $args );

                do_action( 'product_vendors_details_fields_save', $vendor_id, $_POST );

                $redirect = add_query_arg( 'message', 1, $_POST['_wp_http_referer'] );
                wp_safe_redirect( $redirect );
                exit;
            }
        }

	    /**
	     * Update vendor information
	     *
	     * @param $query object The query object
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since  1.0.0
	     * @fire product_vendors_details_fields_save action
	     */
        public function filter_vendor_linked_products( $query ) {
            global $pagenow, $post;

	        $vendor = yith_get_vendor( 'current', 'user' );
	        $action = isset( $_GET['action'] ) ? $_GET['action'] : false;

	        if (
		        $vendor->has_limited_access()
		        && (
			        ( is_ajax() && 'woocommerce_json_search_products' == $action )
			        || ( ! empty( $post ) && 'product' == $post->post_type && ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) )
		        )
	        ) {
	            $query_args = $vendor->get_query_products_args();
	            $query->set( 'tax_query', $query_args['tax_query'] );
            }
        }

	    /**
	     * Add fields to vendor taxonomy (add new vendor screen)
	     *
	     * @param  str $taxonomy Current taxonomy name
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @return void
	     */
	    public function add_taxonomy_fields( $taxonomy ) {

		    $args = array( 'commission' => YITH_Vendors()->get_base_commission() );

		    $this->enqueue_ajax_choosen();

		    yith_wcpv_get_template( 'add-product-vendors-taxonomy', $args, 'admin' );

		    $this->add_select_customer_script();
	    }

	    /**
	     * Edit fields to vendor taxonomy
	     *
	     * @param  WP_Post $vendor Current vendor information
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @return void
	     */
	    public function edit_taxonomy_fields( $vendor ) {

		    $vendor         = yith_get_vendor( $vendor );
		    $vendor_admins  = '';

		    //Get Vendor Owner
		    $owner          = get_userdata( $vendor->get_owner() );
		    $owner_display  = is_a( $owner, 'WP_User' ) ? $owner->display_name . '(#' . $owner->ID . ' - ' . $owner->user_email . ')' : '';
		    $vendor_owner   = ! empty( $owner_display ) ? '<option value="' . esc_attr( $owner->ID ) . '" selected="selected">' . $owner_display . '</option>' : '<option></option>';

		    foreach ( $vendor->get_admins() as $user_id ) {
			    $user         = get_userdata( $user_id );
			    $user_display = is_object( $user ) ? $user->display_name . '(#' . $user_id . ' - ' . $user->user_email . ')' : '';
			    $vendor_admins .= '<option value="' . esc_attr( $user_id ) . '" selected="selected">' . $user_display . '</option>';
		    }

		    $this->enqueue_ajax_choosen();

		    $args = array(
			    'owner'          => $vendor_owner,
			    'vendor_admins'  => $vendor_admins,
                'vendor'         => $vendor
		    );

		    yith_wcpv_get_template( 'edit-product-vendors-taxonomy', $args, 'admin' );

		    $this->add_select_customer_script();
	    }

	    /**
	     * Check for duplicate vendor name
	     *
	     * @author   Andrea Grillo <andrea.grillo@yithemes.com>
	     *
	     * @param $term     string The term name
	     * @param $taxonomy string The taxonomy name
	     *
	     * @return mixed term object | WP_Error
	     * @since    1.0
	     */
	    public function check_duplicate_term_name( $term, $taxonomy ) {

		    if ( $this->_taxonomy_name != $taxonomy ) {
			    return $term;
		    }

		    if ( 'edit_terms' == current_action() ) {
			    $duplicate = get_term_by( 'name', $_POST['name'], $taxonomy );

			    /**
			     * If the vendor name exist -> check if is the edited item or not
			     */
			    if ( $duplicate && $duplicate->term_id == $term ) {
				    $duplicate = false;
			    }

			    $message   = __( 'A vendor with this name already exists.', 'yith_wc_product_vendors' );
			    $title     = __( 'Vendor name already exists', 'yith_wc_product_vendors' );
			    $back_link = admin_url( 'edit-tag.php' );

			    $back_link = add_query_arg( $back_link, array(
					    'action'    => 'edit',
					    'taxonomy'  => $_POST['taxonomy'],
					    'tag_ID'    => $_POST['tag_ID'],
					    'post_type' => 'product'
				    )
			    );

			    $args = array( 'back_link' => $back_link );

			    return ! $duplicate ? $term : wp_die( $message, $title, $args );

		    } else {
			    $duplicate = get_term_by( 'name', $term, $taxonomy );

			    return ! $duplicate ? $term : new WP_Error( 'term_exists', __( 'A vendor with this name already exists.', 'yith_wc_product_vendors' ), $duplicate );
		    }
	    }

	    /**
	     * Save extra taxonomy fields for product vendors taxonomy
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     *
	     * @param $vendor_id string The vendor id
	     *
	     * @return void
	     * @since  1.0
	     */
	    public function save_taxonomy_fields( $vendor_id ) {
            if ( ! isset( $_POST['yith_vendor_data'] ) ) {
			    return;
		    }

		    $post_value = $_POST['yith_vendor_data'];
		    $vendor     = yith_get_vendor( $vendor_id );
		    $usermeta_owner = YITH_Vendors()->get_user_meta_owner();
		    $usermeta_admin = YITH_Vendors()->get_user_meta_key();

		    if ( ! isset( $post_value['enable_selling'] ) ) {
			    $post_value['enable_selling'] = 'no';
		    }

		    // set values
		    foreach ( $post_value as $key => $value ) {
			    update_woocommerce_term_meta( $vendor_id, $key, $value );
		    }

		    // Get current vendor admins and owner
		    $admins = $vendor->get_admins();
		    $owner  = $vendor->get_owner();

		    // Remove all current admins (user meta)
		    foreach ( $admins as $user_id ) {
			    delete_user_meta( $user_id, $usermeta_admin );
			    $this->_manage_vendor_caps( $user_id, 'remove' );
		    }

		    // Remove current owner and update it
		    if ( $owner != $post_value['owner'] ) {
			    delete_user_meta( $owner, $usermeta_owner );
			    update_user_meta( $post_value['owner'], $usermeta_owner, $vendor->id );
		    }

		    //Add Vendor Owner
		    if ( ! isset( $post_value['admins'] ) ) {
			    $post_value['admins'] = array( $post_value['owner'] );
		    } else {
			    $post_value['admins'][] = array( $post_value['owner'] );
		    }

		    // Only add selected admins
		    if ( isset( $post_value['admins'] ) && ! empty( $post_value['admins'] ) ) {
			    foreach ( $post_value['admins'] as $user_id ) {
				    update_user_meta( $user_id, $usermeta_admin, $vendor->id );
				    $this->_manage_vendor_caps( $user_id, 'add' );
			    }
		    }
	    }

	    /**
	     * Add or Remove capabilities to vendor admins
	     *
	     * @param int $user_id User ID of vendor admin
	     * @param string $method The method to call: add to call add_cap method or remove to call remove_cap
	     * @param string $caps The capabilities to add or remove
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since  1.0
	     * @access protected
	     */
	    protected function _manage_vendor_caps( $user_id = 0, $method, $caps = '' ) {
		    if ( $user_id > 0 && ( 'remove' == $method || 'add' == $method ) ) {

			    $method .= '_cap';
			    $user = new WP_User( $user_id );

			    if ( '' == $caps ) {
				    $caps = YITH_Vendors()->vendor_enabled_capabilities();
			    }

			    foreach ( $caps as $cap ) {
				    $user->$method( $cap );
			    }
		    }
	    }

	    /**
	     * Remove admin usermeta info
	     *
	     * @param $term
	     * @param $taxonomy
	     *
	     * @since 1.0.0
	     * @return void
	     */
	    public function remove_admin_usermeta_info( $term, $taxonomy ) {

		    if ( $this->_taxonomy_name != $taxonomy ) {
			    return;
		    }

		    global $wpdb;

		    $sql     = $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_value=%d", $term );
		    $user_id = $wpdb->query( $sql );

		    if ( $user_id > 0 ) {
			    /**
			     * Remove admin caps to user
			     */
			    add_filter( 'yith_vendor_admin_publish_products_capabilities', '__return_true' );
			    $this->_manage_vendor_caps( $user_id, 'remove' );

			    /**
			     * Remove vendor admin
			     */
			    delete_user_meta( $user_id, YITH_Vendors()->get_user_meta_key() );
			    delete_user_meta( $user_id, YITH_Vendors()->get_user_meta_owner() );
		    }
	    }

        /**
         * Set vendor products to draft
         *
         * @param        $term
         *
         * @param string $post_status
         *
         * @since 1.0.0
         * @return void
         */
        public function set_product_to_orphan( $term, $post_status = 'draft' ){

            $vendor      = yith_get_vendor( $term );
            $product_ids = $vendor->get_products();

            foreach( $product_ids as $product_id ){
                wp_update_post( array( 'ID' => $product_id, 'post_status' => $post_status ) );
            }
        }

        /**
         * Remove all data linked to vendor
         *
         * When an admin delete a vendor call this method to delete all vendor data
         *
         * @param $term
         * @param $taxonomy
         *
         * @since 1.0.0
         * @return void
         */
        public function remove_vendor_data( $term, $taxonomy ) {
            /**
             * Remove vendor admin user meta
             */
            $this->remove_admin_usermeta_info( $term, $taxonomy );

            /**
             * Set vendor's products to draft
             */
            $this->set_product_to_orphan( $term );
        }

	    /**
	     * Remove the WooCommerce taxonomy Metabox and add a new Metabox for single taxonomy management
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since  1.0.0
	     * @return void
	     */
	    public function single_value_taxonomy() {

		    $id              = 'tagsdiv-' . $this->_taxonomy_name;
		    $taxonomy        = get_taxonomies( array( 'show_ui' => true ), 'object' );
		    $product_vendors = $taxonomy[ $this->_taxonomy_name ];
		    $page            = 'product';
		    $context         = 'side';
		    $callback        = array( $this, 'single_taxonomy_meta_box' );
		    $callback_args   = array( 'taxonomy' => $this->_taxonomy_name );
		    $priority        = 'default';

		    remove_meta_box( $id, $page, $context );
		    add_meta_box( $id, $product_vendors->labels->name, $callback, $page, $context, $priority, $callback_args );
	    }

	    /**
	     * Add select customer scripts
	     *
	     * @Author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since 1.0.0
	     * @return void
	     * @use woocommerce_json_search_customers action
	     */
	    public function add_select_customer_script() {

		    $inline_js = "
                jQuery('select.ajax_chosen_select_customer').ajaxChosen({
                    method: 		'GET',
                    url: 			'" . admin_url( 'admin-ajax.php' ) . "',
                    dataType: 		'json',
                    afterTypeDelay: 100,
                    minTermLength: 	1,
                    data:		{
                        action: 	'woocommerce_json_search_customers',
                        security: 	'" . wp_create_nonce( "search-customers" ) . "',
                        default: 	'',
                        plugin:     '" . YITH_WPV_SLUG . "'
                    }
                }, function (data) {

                    var terms = {};

                    $.each(data, function (i, val) {
                        terms[i] = val;
                    });

                    return terms;
                });
            ";

		    wc_enqueue_js( $inline_js );
	    }

	    /**
	     * Add or Remove publish_products capabilitie to vendor admins when global option change
	     *
	     * @param $value string The value of yith_wpv_vendors_options_admin_reviews admin option
	     *
	     * @return   void|string
	     * @author   Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since    1.0
	     */
	    public function change_product_vendors_admin_role( $value ) {
		    if ( 'yith_wpv_vendors_option_skip_review' != $value['id'] ) {
			    return $value;
		    }

		    $old_value = get_option( $value['id'] );
		    $new_value = isset( $_POST[ $value['id'] ] ) ? 'yes' : 'no';

		    if ( $old_value != $new_value ) {
			    //on my signal unleash hell
			    global $wpdb;
			    $wp_capabilities = "{$wpdb->prefix}capabilities";

			    $sql = $wpdb->prepare( "SELECT user_id
                        FROM {$wpdb->usermeta}
                        WHERE meta_key = %s
                        AND user_id IN (
                            SELECT user_id
                            FROM {$wpdb->usermeta}
                            WHERE meta_key = %s
                            )", $wp_capabilities, YITH_Vendors()->get_user_meta_key() );

			    $user_ids = $wpdb->get_col( $sql );

			    if ( 'no' == $old_value ) {
				    foreach ( $user_ids as $user_id ) {
					    $this->_manage_vendor_caps( $user_id, 'add', array( 'publish_products' ) );
				    }
			    } else {
				    foreach ( $user_ids as $user_id ) {
					    $this->_manage_vendor_caps( $user_id, 'remove', array( 'publish_products' ) );
				    }
			    }
		    }

		    return $value;
	    }

	    /**
	     * When searching using the WP_User_Query, search names (user meta) too
	     *
	     * @param  object $query The current query object
	     *
	     * @return void
	     * @see WP_User_Query Class wp-includes/user.php
	     */
	    public function json_search_customer_name( $query ) {

		    if ( isset( $_GET['plugin'] ) && YITH_WPV_SLUG == $_GET['plugin'] ) {
			    global $wpdb;

			    $term = wc_clean( stripslashes( $_GET['term'] ) );
			    $term = $wpdb->esc_like( $term );

			    $where_old          = $wpdb->prepare( ") OR user_name.meta_value LIKE %s ", '%' . $term . '%' );
			    $where_new          = $wpdb->prepare( " OR user_name.meta_value LIKE %s) ", '%' . $term . '%' );
			    $query->query_where = str_replace( $where_old, $where_new, $query->query_where );
			    $query->query_where .= $wpdb->prepare( "AND ID NOT IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s)", YITH_Vendors()->get_user_meta_key() );
		    }
	    }

	    /**
	     * Print the Single Taxonomy Metabox
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since  1.0.0
	     *
	     * @param $taxonomy     string Taxonomy Name
	     * @param $taxonomy_box string Taxonomy Box
	     *
	     * @return void
	     */
        public function single_taxonomy_meta_box( $taxonomy, $taxonomy_box ) {
            $taxonomy_label = YITH_Vendors()->get_vendors_taxonomy_label();
            $vendor         = yith_get_vendor( 'current', 'product' );

            $args = array(
                'id'                => 'tax-input-yith_shop_vendor',
                'name'              => 'tax_input[yith_shop_vendor]',
                'taxonomy'          => $this->_taxonomy_name,
                'show_option_none'  => ! $vendor->is_super_user() ? '' : sprintf( __( 'No %s' ), strtolower( $taxonomy_label['singular_name'] ) ),
                'hide_empty'        => ! $vendor->is_super_user(),
                'selected'          => $vendor ? $vendor->id : 0,
                'walker'            => YITH_Walker_CategoryDropdown(),
                'option_none_value' => '', // Avoid to save -1 as new vendor when you create a new product
            );

            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() && $vendor->is_user_admin() ) {
                echo $vendor->name;
            }
            else {
                wp_dropdown_categories( $args );
            }
        }

	    /**
	     * Add ajax-chosen javascript libr
	     *
	     * @Author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since 1.0.0
	     * @return void
	     */
	    public function enqueue_ajax_choosen() {
		    wp_enqueue_script( 'ajax-chosen' );
	    }

        /**
	     * If an user is a vendor admin remove the woocommerce prevent admin access
	     *
	     * @Author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since 1.0.0
	     * @return bool
         * @use woocommerce_prevent_admin_access hooks
	     */
        public function prevent_admin_access( $prevent_access ){
            $vendor = yith_get_vendor( 'current', 'user' );

            return $vendor->is_valid() && $vendor->has_limited_access() && $vendor->is_user_admin() ? false : $prevent_access;
        }

        /**
         * Remove the description column from taxonomy table
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0.0
         *
         * @param $columns The columns
         *
         * @return array The columns list
         * @use manage_{$this->screen->id}_columns
         */
        public function get_columns( $columns ) {
            unset( $columns['description'] );
            return $columns;
        }

    }
}
