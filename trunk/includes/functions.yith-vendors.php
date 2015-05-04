<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! function_exists( 'yith_wcpv_get_template' ) ) {
    /**
     * Get Plugin Template
     *
     * It's possible to overwrite the template from theme.
     * Put your custom template in woocommerce/product-vendors folder
     *
     * @param        $filename
     * @param array  $args
     * @param string $section
     * @use  wc_get_template()
     * @since 1.0
     * @return void
     */
    function yith_wcpv_get_template( $filename, $args = array(), $section = '' ) {

        $ext = strpos( $filename, '.php' ) === false ? '.php' : '';
        $template_name      = $section . '/' . $filename . $ext;
        $template_path      = WC()->template_path() . 'product-vendors/';
        $default_path       = YITH_WPV_TEMPLATE_PATH;

        if( defined( 'YITH_WPV_PREMIUM' ) ){
            $premium_template   = str_replace( '.php', '-premium.php', $template_name );
            $located_premium    = wc_locate_template( $premium_template, $template_path, $default_path );
            $template_name      = file_exists( $located_premium ) ?  $premium_template : $template_name;
        }

        wc_get_template( $template_name, $args, $template_path, $default_path );
    }
}