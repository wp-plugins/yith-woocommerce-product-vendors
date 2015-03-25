<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WPV_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Vendors_Frontend
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Vendors_Frontend' ) ) {

    /**
     * Class YITH_Vendors_Frontend
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    class YITH_Vendors_Frontend  {

        /**
         * Constructor
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function __construct() {

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            /* Shop Page */
            add_action( 'woocommerce_after_shop_loop_item', array( $this, 'woocommerce_template_vendor_name' ), 4  );
            add_action( 'woocommerce_product_query', array( $this, 'check_vendors_selling_capabilities' ), 10, 1 );

            /* Single Product */
            add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_vendor_tab' ) );
            add_action( 'woocommerce_single_product_summary', array( $this, 'woocommerce_template_vendor_name' ), 5 );
            add_action( 'template_redirect', array( $this, 'exit_direct_access_no_selling_capabilities' ) );
        }

        /**
         * Add product vendor tabs in single product page
         *
         * check if the product is property of a specific vendor and add a new tab "Vendor" with the vendor information
         *
         * @param $tabs array The single product tabs
         *
         * @return   array The tab array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use woocommerce_product_tabs filter
         */
        public function add_product_vendor_tab( $tabs ) {
            global $product;

            $vendor = yith_get_vendor( $product, 'product' );

            if ( $vendor->is_valid() ) {

                $args = array(
                    'title'    => YITH_Vendors()->get_vendors_taxonomy_label( 'singular_name' ),
                    'priority' => 99,
                    'callback' => array( $this, 'get_vendor_tab' )
                );

                /**
                 * Use yith_wc_vendor as array key. Not use vendor to prevent conflict with wc vendor extension
                 */
                $tabs['yith_wc_vendor'] = apply_filters( 'yith_woocommerce_product_vendor_tab', $args );
            }

            return $tabs;
        }

        /**
         * Get Vendor product tab template
         *
         * @return   array The tab array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @fire     yith_woocommerce_product_vendor_tab_template filter
         */
        public function get_vendor_tab() {
            global $product;

            $vendor = yith_get_vendor( $product, 'product' );

            $args = array(
                'vendor_name'        => $vendor->name,
                'vendor_description' => $vendor->description,
                'vendor_url'         => $vendor->url
            );

            $args = apply_filters( 'yith_woocommerce_product_vendor_tab_template', $args );

            yith_wcpv_get_template( 'vendor-tab', $args, 'woocommerce/single-product' );
        }

        /**
         * Add vendor name after product title
         *
         * @return   string The title
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     the_title filter
         */
        public function woocommerce_template_vendor_name() {
            global $product;

            if ( ! empty( $product ) && is_object( $product ) ) {
                $vendor = yith_get_vendor( $product, 'product' );

                if ( $vendor->is_valid() ) {
                    $args          = array( 'vendor_name' => $vendor->name );
                    $template_info = array(
                        'name'    => 'vendor-name-title',
                        'args'    => $args,
                        'section' => is_product() ? 'woocommerce/single-product' : 'woocommerce/loop',
                    );

                    $template_name = apply_filters( 'yith_woocommerce_vendor_name_template_info', $template_info );

                    extract( $template_info );

                    yith_wcpv_get_template( $name, $args, $section );
                }
            }
        }

        /**
         * check if vendor has selling capabilities
         *
         * @return   string The title
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     the_title filter
         */
        public function check_vendors_selling_capabilities( $query ) {

            $to_exclude = YITH_Vendors()->get_vendors(
                array(
                    'enabled_selling' => false,
                    'fields' => 'ids'
                )
            );

            if ( $to_exclude ) {
                $tax_query = array(
                    array(
                        'taxonomy' => YITH_Vendors()->get_taxonomy_name(),
                        'field'    => 'id',
                        'terms'    => $to_exclude,
                        'operator' => 'NOT IN'
                    )
                );

                $query->set( 'tax_query', $tax_query );
            }
        }

        /**
         * exit if the vendor account hasn't selling capabilities
         *
         * @return   string The title
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     template_redirect filter
         */
        public function exit_direct_access_no_selling_capabilities() {
            global $post;

            if ( is_singular( 'product' ) ) {
                $vendor = yith_get_vendor( $post, 'product' );

                if ( $vendor && 'no' == $vendor->enable_selling ) {
                    $this->redirect_404();
                }
            }
        }

        /**
         * exit if the vendor account hasn't selling capabilities
         *
         * @param    $exit bool Default: true. If true call exit function.
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function redirect_404( $exit = true ) {
            include( get_query_template( '404' ) );
            if ( $exit ) {
                exit;
            }
        }

        /**
         * Enqueue Style and Scripts
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function enqueue_scripts() {
            wp_enqueue_style( 'yith-wc-product-vendors', YITH_WPV_ASSETS_URL . 'css/style.css' );
        }
    }
}

