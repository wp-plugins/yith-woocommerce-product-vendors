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
 * @class      YITH_Vendors_Backend
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Vendors_Backend' ) ) {

    /**
     * Class YITH_Vendors_Backend
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    class YITH_Vendors_Backend {

        /**
         * @var YIT_Plugin_Panel_Woocommerce instance
         */
        private $_panel;

        /**
         * @var YIT_Plugin_Panel_Woocommerce instance
         */
        private $_panel_page = 'yith_wpv_panel';

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/';

        /**
         * @var string Plugin official documentation
         */
        protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-woocommerce-product-vendors/';

        /**
         * Constructor
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function __construct() {

            /* === Panel Settings === */
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            /* === Plugin Informations === */
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WPV_PATH . '/' . basename( YITH_WPV_FILE ) ), array( $this, 'action_links' ) );
            //add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
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

            $admin_tabs = array(
                'commissions' => __( 'Commissions', 'yith_wc_product_vendors' ),
                'vendors'     => __( 'Vendors', 'yith_wc_product_vendors' ),
                //'premium'  => __( 'Premium Version', 'yith_wc_product_vendors' ),
            );

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Product Vendors', 'yith_wc_product_vendors' ),
                'menu_title'       => __( 'Product Vendors', 'yith_wc_product_vendors' ),
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_WPV_PATH . '/plugin-options'
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

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith_wc_product_vendors' ) . '</a>';

            if ( defined( 'YITH_WCSTRIPE_FREE_INIT' ) ) {
                //$links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith_wc_product_vendors' ) . '</a>';
            }

            return $links;
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

    }
}

