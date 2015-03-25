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

if ( ! class_exists( 'YITH_Vendor' ) ) {

	/**
	 * The main class for the Vendor
	 *
	 * @class      YITH_Vendor
	 * @package    Yithemes
	 * @since      1.0.0
	 * @author     Your Inspiration Themes
	 *
	 * @property    string $name
	 * @property    string $slug
	 * @property    string $description
	 * @property    string $url
	 * @property    string $paypal_email
	 * @property    string $enable_selling
	 * @property    array $admins
	 * @property    int|string $commission
	 */
    class YITH_Vendor {

	    /**
	     * The vendor ID.
	     *
	     * @var int
	     */
	    public $id = 0;

	    /**
	     * $post Stores term data of vendor
	     *
	     * @var $term object
	     */
	    public $term = null;

	    /**
	     * $post Stores term data of vendor
	     *
	     * @var $term string
	     */
	    protected $_usermetaKey = '';

	    /**
	     * $post Stores term data of vendor
	     *
	     * @var $term string
	     */
	    protected $_usermetaOwner = '';

	    /**
	     * Construct
	     *
         * @param mixed $vendor The vendor object
	     * @param string $obj What object is if is numeric (vendor|user|product)
	     * @return bool|YITH_Vendor
	     */
	    public function __construct( $vendor = false, $obj = 'vendor' ) {
		    $taxonomy             = YITH_Vendors()->get_taxonomy_name();
		    $this->_usermetaKey   = YITH_Vendors()->get_user_meta_key();
		    $this->_usermetaOwner = YITH_Vendors()->get_user_meta_owner();
		    $this->_taxonomy      = YITH_Vendors()->get_taxonomy_name();

		    // change value 'current' to false for $vendor, to make it more rock!
		    if ( 'current' == $vendor ) {
			    $vendor = false;
		    }

		    // Get by user
		    if ( 'user' == $obj ) {

			    // get vendor of actual user if nothind passed
			    if ( false === $vendor ) {
				    $vendor = get_user_meta( get_current_user_id(), $this->_usermetaKey, true );
			    }

			    // Get Vendor ID by user ID passed by $vendor and set the getter to 'vendor'
			    else {
				    $vendor = get_user_meta( $vendor, $this->_usermetaKey, true );
			    }

			    $obj    = 'vendor';
		    }

		    // Get by product
		    elseif ( 'product' == $obj ) {

			    // get vendor of actual product if nothind passed
			    if ( false === $vendor ) {
				    global $post;
				    $vendor = $post->ID;
			    } elseif ( $vendor instanceof WP_Post ) {
				    $vendor = $vendor->ID;
			    } elseif ( $vendor instanceof WC_Product ) {
				    $vendor = $vendor->id;
			    }

			    $terms = wp_get_post_terms( $vendor, $this->_taxonomy );

			    if ( empty( $terms ) ) {
				    return false;
			    }

			    $this->term = array_shift( $terms );
			    $this->id = $this->term->term_id;

			    $this->_populate();
			    return $this;

		    }

		    // exit if any object is retrieved
		    if ( empty( $vendor ) ) {
			    return false;
		    }

		    // RETRIEVE OBJECT

		    // Get vendor by Vendor ID
		    if ( is_numeric( $vendor ) && 'vendor' == $obj ) {
			    $this->id   = absint( $vendor );
			    $this->term = get_term_by( 'term_id', $this->id, $taxonomy );
		    }

		    // get vendor by Vendor slug or name
		    elseif ( is_string( $vendor ) ) {
			    $this->term = get_term_by( 'slug', $vendor, $taxonomy );
			    if ( empty( $this->term ) || is_wp_error( $this->term ) ) {
				    $this->term = get_term_by( 'name', $vendor, $taxonomy );
			    }
			    if ( empty( $this->term ) || is_wp_error( $this->term ) ) {
				    return false;
			    }
			    $this->id   = $this->term->term_id;
		    }

		    // get vendor by object vendor
		    elseif ( $vendor instanceof YITH_Vendor ) {
			    $this->id   = absint( $vendor->id );
			    $this->term = $vendor->term;
			    return $vendor;
		    }

		    // get vendor by term object
		    elseif ( isset( $vendor->slug ) && term_exists( $vendor->slug, $taxonomy ) ) {
			    $this->id   = absint( $vendor->term_id );
			    $this->term = $vendor;
		    }

		    // no vendor found
		    else {
			    return false;
		    }

		    // return false is there is a term associated
		    if ( empty( $this->term ) ) {
			    return false;
		    }

		    // populate
		    $this->_populate();

		    return $this;
	    }

	    /**
	     * Populate information of vendor
	     *
	     * @since 1.0
	     */
	    protected function _populate() {
		    $this->name        = $this->term->name;
		    $this->slug        = $this->term->slug;
		    $this->description = $this->term->description;
		    $this->url         = get_term_link( $this->term );
	    }

	    /**
	     * __get function.
	     *
	     * @param string $key
	     * @return mixed
	     */
	    public function __get( $key ) {
		    $value = get_woocommerce_term_meta( $this->id, $key );

		    // Get values or default if not set
		    if ( 'admins' === $key ) {
			    $value = $this->get_admins();

		    } elseif ( 'commission' === $key ) {
			    $value = $this->get_commission();
		    }

		    if ( ! empty( $value ) ) {
			    $this->$key = $value;
		    }

		    return $value;
	    }

	    /**
	     * __isset function.
	     *
	     * @param mixed $key
	     * @return bool
	     */
	    public function __isset( $key ) {
		    return metadata_exists( 'woocommerce_term', $this->id, $key );
	    }

	    /**
	     * Get the vendor commission
	     *
	     * @Author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @return string The vendor commission
	     * @fire yith_vendor_commission filter
	     */
	    public function get_commission() {
		    $base_commission = YITH_Vendors()->get_base_commission();
		    return apply_filters( 'yith_vendor_commission', $base_commission, $this->id, $this );
	    }

         /**
	     * Get vendor owner
	     *
	     * @return   int The owner user id
	     * @since    1.0
	     * @author   Andrea Grillo <andrea.grillo@yithemes.com>
	     */
	    public function get_owner() {
            $args   = array(
                'meta_key'     => $this->_usermetaOwner,
                'meta_value'   => $this->id,
                'meta_compare' => '=',
                'fields'       => 'ids',
                'number'       => 1
		    );
		    $owner = get_users( $args );

		    return ! empty( $owner ) ? array_shift( $owner ) : 0;
	    }

	    /**
	     * Get admins for vendor
	     *
	     * @return   Array of user IDs
	     * @since    1.0
	     * @author   Andrea Grillo <andrea.grillo@yithemes.com>
	     */
	    public function get_admins() {
		    $args   = array(
			    'meta_key'     => $this->_usermetaKey,
			    'meta_value'   => $this->id,
			    'meta_compare' => '=',
			    'fields'       => 'ids'
		    );
		    $admins = get_users( $args );

		    return $admins;
	    }

	    /**
	     * Check if the user passed in parameter is admin
	     *
	     * @param bool $user_id The user to check
	     *
	     * @return bool
	     * @since 1.0
	     */
	    public function is_super_user( $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

		    // if the user is shop manager or administrator, return true
		    return user_can( $user_id, 'manage_woocommerce' );
	    }

	    /**
	     * Check if the user passed in parameter is admin
	     *
	     * @param bool $user_id The user to check
	     *
	     * @return bool
	     * @since 1.0
	     */
	    public function is_user_admin( $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

		    // if the user is shop manager or administrator, return true
		    if ( $this->is_super_user( $user_id ) ) {
			    return true;
		    }

		    foreach ( $this->get_admins() as $admin_id ) {
			    if ( $admin_id == $user_id ) {
				    return true;
			    }
		    }

		    return false;
	    }

	    /**
	     * Check if the user has limited access to admin dashboard, valid only for Vendor Admin
	     *
         * @param bool|int $user_id
	     *
	     * @return bool
	     * @since 1.0
	     */
	    public function has_limited_access( $user_id = false ) {
		    return (bool) ( ! $this->is_super_user( $user_id ) && $this->is_user_admin( $user_id ) );
	    }

	    /**
	     * Return the arguments to make a query for the posts of this vendor
	     *
	     * @param array $extra More arguments to append
	     *
	     * @return array
	     */
	    public function get_query_products_args( $extra = array() ) {
		    return wp_parse_args( $extra, array(
			    'post_type' => 'product',
			    'tax_query' => array(
				    array(
					    'taxonomy' => $this->_taxonomy,
					    'field'    => 'id',
					    'terms'    => $this->id
				    )
			    )
		    ) );
	    }

	    /**
	     * Get query results of this vendor
	     *
	     * @param array $extra More arguments to append
	     *
	     * @return array
	     */
	    public function get_products( $extra = array() ) {
		    $args = wp_parse_args( $extra, array(
			    'posts_per_page' => -1,
			    'fields' => 'ids'
		    ) );

		    $args = $this->get_query_products_args( $args );

		    return get_posts( $args );
	    }

        /**
         * Check if the current object is a valid vendor
         *
         * @since 1.0
         *
         * @author Andrea Grillo    <andrea.grillo@yithemes.com>
         * @author Antonino Scarfi  <antonino.scarfi@yithemes.com>
         * @return bool
         */
        public function is_valid() {
            return ! empty( $this->id ) && ! empty( $this->term );
        }

         /**
         * Check if the current user is the vendor owner
         *
         * @since 1.0
         *
         * @author Andrea Grillo    <andrea.grillo@yithemes.com>
         * @author Antonino Scarfi  <antonino.scarfi@yithemes.com>
         * @return bool
         */
        public function is_owner( $user_id = false ) {
            if( ! $user_id ) {
                $user_id = get_current_user_id();
            }

            return get_user_meta( $user_id, $this->_usermetaOwner, true ) == $this->id;
        }

	    /**
	     * Get the frontend URL
	     *
	     * @param string $context
	     * @return string
	     */
	    public function get_url( $context = 'frontend' ) {
		    $url = '';

		    if ( 'frontend' == $context ) {
			    if ( $url = get_term_link( $this->term ) && is_wp_error( $url ) ) {
				    $url = false;
			    }
		    }
		    else if ( 'admin' == $context ) {
			    $url = get_edit_term_link( $this->id, $this->_taxonomy );
		    }

		    return apply_filters( 'yith_vendor_url', $url, $this, $context );
	    }
    }
}

if ( ! function_exists( 'yith_get_vendor' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @param mixed $vendor
	 * @param string $obj
	 *
	 * @return YITH_Vendor
	 * @since  1.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
    function yith_get_vendor( $vendor = false, $obj = 'vendor' ) {
        return new YITH_Vendor( $vendor, $obj );
    }
}
