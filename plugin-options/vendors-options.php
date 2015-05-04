<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

return apply_filters( 'yith_wpv_panel_vendors_options', array(

        'vendors' => array(

            'vendors_options_start' => array(
                'type' => 'sectionstart',
                'id'   => 'yith_wpv_vendors_options_start'
            ),

            'vendors_options_title' => array(
                'title' => __( 'Multi vendor', 'yith_wc_product_vendors' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'yith_wpv_vendors_options_title'
            ),

            'vendors_color_name'    => array(
                'title'   => __( 'Vendor name label color', 'yith_wc_product_vendors' ),
                'type'    => 'color',
                'desc'    => __( 'Use in shop page and single product page', 'yith_wc_product_vendors' ),
                'id'      => 'yith_vendors_color_name',
                'default' => '#bc360a'
            ),

            'vendors_options_end'   => array(
                'type' => 'sectionend',
                'id'   => 'yith_wpv_vendors_options_end'
            ),
        )
    ), 'vendors'
);