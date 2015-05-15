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
         * @var YIT_Plugin_Panel_Woocommerce instance
         */
        protected $_panel;

        /**
         * @var YIT_Plugin_Panel_Woocommerce instance
         */
        protected $_panel_page = 'yith_wpv_panel';

//TODO: Cambiare link doc e landing

        /**
         * @var string Official plugin documentation
         */
        protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-woocommerce-product-vendors/' ;

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_landing = 'http://yithemes.com/docs-plugins/yith-woocommerce-product-vendors/' ;

        /**
         * Construct
         */
        public function __construct() {
	        $this->_taxonomy_name   = YITH_Vendors()->get_taxonomy_name();

             /* Panel Settings */
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            /* Plugin Informations */
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WPV_PATH . '/' . basename( YITH_WPV_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
            add_action( 'yith_wc_multi_vendor_premium_tab', array( $this, 'show_premium_tab' ) );

	        /* Taxonomy management */
	        add_action( $this->_taxonomy_name . '_add_form_fields', array( $this, 'add_taxonomy_fields' ), 1, 1 );
	        add_action( $this->_taxonomy_name . '_edit_form_fields', array( $this, 'edit_taxonomy_fields' ), 1, 1 );
	        add_filter( 'pre_insert_term', array( $this, 'check_duplicate_term_name' ), 10, 2 );
	        add_filter( 'edit_terms', array( $this, 'check_duplicate_term_name' ), 10, 2 );
	        add_action( 'edited_' . $this->_taxonomy_name, array( $this, 'save_taxonomy_fields' ), 10, 2 );
	        add_action( 'created_' . $this->_taxonomy_name, array( $this, 'save_taxonomy_fields' ), 10, 2 );
	        add_action( 'pre_delete_term', array( $this, 'remove_vendor_data' ), 10, 2 );
	        add_action( 'add_meta_boxes', array( $this, 'single_value_taxonomy' ) );

            /* Taxonomy Table Management */
            add_filter( "bulk_actions-edit-{$this->_taxonomy_name}", '__return_empty_array' );

	        /* Allow html in taxonomy descriptions */
	        remove_filter( 'pre_term_description', 'wp_filter_kses' );
	        remove_filter( 'term_description', 'wp_kses_data' );

	        /* WooCommerce */
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
	        add_action( 'admin_menu', array( $this, 'remove_media_page' ) );
	        add_action( 'admin_menu', array( $this, 'remove_dashboard_widgets' ) );

	        /* Vendor information management */
	        add_action( 'admin_action_yith_admin_save_fields', array( $this, 'save_taxonomy_fields' ) );

            /* Prevent WooCommerce Access Admin */
            add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ) );
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = apply_filters( 'yith_vendors_admin_tabs', array(
                    'commissions' => __( 'Commissions', 'yith_wc_product_vendors' ),
                    'vendors'     => __( 'Vendors', 'yith_wc_product_vendors' ),
                    'premium'     => __( 'Premium Version', 'yith_wc_product_vendors' ),
                )
            );

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Multi Vendor', 'yith_wc_product_vendors' ),
                'menu_title'       => __( 'Multi Vendor', 'yith_wc_product_vendors' ),
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_WPV_PATH . 'plugin-options'
            );


            /* === Fixed: not updated theme/old plugin framework  === */
            if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }

        /**
         * Enqueue Style and Scripts
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function enqueue_scripts() {
            wp_enqueue_style( 'yith-wc-product-vendors-admin', YITH_WPV_ASSETS_URL . 'css/admin.css', array( 'jquery-chosen' ) );
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
	            $request[ $vendor->term->taxonomy ] = $vendor->slug;
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

	        $menu_args = apply_filters( 'yith_wc_product_vendors_details_menu_items',
		        array(
			        'page_title'  => __( 'Vendor Details', 'yith_wc_product_vendors' ),
			        'menu_title'  => __( 'Vendor Details', 'yith_wc_product_vendors' ),
			        'capability'  => 'edit_products',
			        'menu_slug'   => 'yith_vendor_details',
			        'function'    => array( $this, 'admin_details_page' )
		        )
	        );

	        extract( $menu_args );

	        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, 'dashicons-id-alt', 30 );
        }

        /**
         * Remove upload.php page
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @return void
         */
        public function remove_media_page(){
            /* Remove Media Library */
            $vendor = yith_get_vendor( 'current', 'user' );
            if( $vendor->is_valid() && $vendor->has_limited_access() ){
                remove_menu_page( 'upload.php' );
            }
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
		    $vendor_admins  = $this->vendor_admins_chosen( $vendor );

		    //Get Vendor Owner
		    $owner          = get_userdata( $vendor->get_owner() );
		    $owner_display  = is_a( $owner, 'WP_User' ) ? $owner->display_name . '(#' . $owner->ID . ' - ' . $owner->user_email . ')' : '';
		    $vendor_owner   = ! empty( $owner_display ) ? '<option value="' . esc_attr( $owner->ID ) . '" selected="selected">' . $owner_display . '</option>' : '<option></option>';

		    $this->enqueue_ajax_choosen();

		    $args = apply_filters( 'yith_edit_taxonomy_args', array(
			    'owner'          => $vendor_owner,
			    'vendor_admins'  => $vendor_admins,
                'vendor'         => $vendor
                )
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

		    if ( 'edit_terms' == current_action() && isset( $_POST['name'] ) ) {
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

                $back_link = esc_url( add_query_arg( $back_link, array(
                            'action'    => 'edit',
                            'taxonomy'  => $_POST['taxonomy'],
                            'tag_ID'    => $_POST['tag_ID'],
                            'post_type' => 'product'
                        )
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
	    public function save_taxonomy_fields( $vendor_id = 0 ) {

            if ( ! isset( $_POST['yith_vendor_data'] ) ) {
                return;
            }

            $is_new = strpos( current_action(), 'created_' ) !== false;

            // if not is set $vendor_id check if there is the update_vendor_id field inside the $_POST array
            if( empty( $vendor_id ) && isset( $_POST['update_vendor_id'] ) ) {
                $vendor_id = $_POST['update_vendor_id'];
            }

            $vendor = yith_get_vendor( $vendor_id );

            if( ! $vendor->is_valid() ) {
                return;
            }

		    $post_value     = $_POST['yith_vendor_data'];
		    $usermeta_owner = YITH_Vendors()->get_user_meta_owner();
		    $usermeta_admin = YITH_Vendors()->get_user_meta_key();

		    if ( ! $vendor->has_limited_access() ) {
                foreach ( apply_filters('yith_wpv_save_checkboxes', array( 'enable_selling' ) ) as $key ) {
                    ! isset( $post_value[ $key ] ) && $post_value[ $key ] = 'no';
                }
            }

            // set values
            foreach ( $post_value as $key => $value ) {
                $vendor->$key = ! is_array( $value ) ? wc_clean( $value ) : $value;
            }

            // add vendor registrantion date
            if( $is_new ) {
                $vendor->registration_date      = current_time( 'mysql' );
                $vendor->registration_date_gmt  = current_time( 'mysql', 1 );
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
		    if ( ! empty( $post_value['owner'] ) && $owner != $post_value['owner'] ) {
			    delete_user_meta( $owner, $usermeta_owner );
			    update_user_meta(intval( $post_value['owner'] ), $usermeta_owner, $vendor->id );
                $owner = intval( $post_value['owner'] );
		    }

		    //Add Vendor Owner
		    if ( ! isset( $post_value['admins'] ) ) {
			    $post_value['admins'] = array( $owner );
		    } else {
			    $post_value['admins'][] = $owner;
		    }

		    // Only add selected admins
		    if ( isset( $post_value['admins'] ) && ! empty( $post_value['admins'] ) ) {
			    foreach ( $post_value['admins'] as $user_id ) {
				    update_user_meta( $user_id, $usermeta_admin, $vendor->id );
				    $this->_manage_vendor_caps( $user_id, 'add' );
			    }
		    }

            do_action( 'yith_wpv_after_save_taxonomy', $vendor, $post_value );

            if( 'admin_action_yith_admin_save_fields' == current_action() ){
                wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $_POST['page'], 'tab' => $_POST['tab'] ) ) ) );
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
                    if( 'remove' == $method ){
                        $caps[] = 'publish_products';
                    }
			    } elseif(  is_string( $caps )  ) {
                    $caps = array( $caps );
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
         * Get vendors admin array for choosen select
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0.0
         *
         * @param $vendor The vendor object
         *
         * @return string The admins
         */
        public function vendor_admins_chosen( $vendor = '' ){
            if( empty( $vendor ) ){
                $vendor = yith_get_vendor( 'current', 'user' );
            }

            $admins = '';
             foreach ( $vendor->get_admins() as $user_id ) {
                if( $vendor->owner != $user_id ){
                    $user         = get_userdata( $user_id );
                    $user_display = is_object( $user ) ? $user->display_name . '(#' . $user_id . ' - ' . $user->user_email . ')' : '';
                    $admins .= '<option value="' . esc_attr( $user_id ) . '" selected="selected">' . $user_display . '</option>';
                }
		    }
            return $admins;
        }

        		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $links | links plugin array
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return mixed
		 * @use plugin_action_links_{$plugin_file_name}
		 */
        public function action_links( $links ) {
			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'ywpi' ) . '</a>';

			if ( defined( 'YITH_WPV_FREE_INIT' ) ) {
				$links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'ywpi' ) . '</a>';
			}

			return $links;
		}

		/**
		 * plugin_row_meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if( ( defined( 'YITH_WPV_INIT' ) && YITH_WPV_INIT == $plugin_file ) || ( defined( 'YITH_WPV_FREE_INIT' ) && YITH_WPV_FREE_INIT == $plugin_file ) ){
                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'ywpi' ) . '</a>';
            }
			return $plugin_meta;
		}

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
        }

        /**
         * Remove Dashboard Widgets
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function remove_dashboard_widgets(){
            $to_removes = array(
                array(
                    'id'        => 'woocommerce_dashboard_status',
                    'screen'    => 'dashboard',
                    'context'   => 'normal'
                ),
                array(
                    'id'        => 'dashboard_activity',
                    'screen'    => 'dashboard',
                    'context'   => 'normal'
                ),
                array(
                    'id'        => 'woocommerce_dashboard_recent_reviews',
                    'screen'    => 'dashboard',
                    'context'   => 'normal'
                ),
            );

            foreach( $to_removes as $widget ){
                remove_meta_box( $widget['id'], $widget['screen'], $widget['context'] );
            }
        }

         /**
         * Show the premium tabs
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function show_premium_tab(){
            yith_wcpv_get_template( 'premium', array(), 'admin' );
        }
    }
}
