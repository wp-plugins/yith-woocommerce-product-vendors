<?php

function yith_vendors_update_1_0_2() {
    $vendors_db_option = get_option( 'yith_product_vendors_db_version' );

    //Add support to YITH Product Vendors 1.0.2
    if ( $vendors_db_option && version_compare( $vendors_db_option, '1.0.1', '<' ) ) {
        global $wpdb;

        $sql = "SELECT woocommerce_term_id as vendor_id, meta_value as user_id
                    FROM {$wpdb->woocommerce_termmeta} as wtm
                    WHERE wtm.meta_key = %s
                    AND woocommerce_term_id IN (
                        SELECT DISTINCT term_id as vendor_id
                        FROM {$wpdb->term_taxonomy} as tt
                        WHERE tt.taxonomy = %s
                    )";

        $results = $wpdb->get_results( $wpdb->prepare( $sql, 'owner', YITH_Vendors()->get_taxonomy_name() ) );

        foreach ( $results as $result ) {
            $user = get_user_by( 'id', $result->user_id );

            if ( $user ) {
                update_woocommerce_term_meta( $result->vendor_id, 'registration_date', get_date_from_gmt( $user->user_registered ) );
                update_woocommerce_term_meta( $result->vendor_id, 'registration_date_gmt', $user->user_registered );
                if( defined( 'YITH_WPV_PREMIUM' ) ){
                    $user->add_cap( 'view_woocommerce_reports' );
                }
            }
        }

        $sql = "ALTER TABLE `{$wpdb->prefix}yith_vendors_commissions` CHANGE `rate` `rate` DECIMAL(5,4) NOT NULL";
        $wpdb->query( $sql );

        update_option( 'yith_product_vendors_db_version', '1.0.1' );
    }
}
add_action( 'admin_init', 'yith_vendors_update_1_0_2' );