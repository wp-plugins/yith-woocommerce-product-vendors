<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>
<div class="wrap yith-vendor-admin-informations-wrap" id="vendor_details">
    <div class="icon32" id="icon-options-general"><br /></div>
    <h2><?php _e( 'Vendor Details', 'yith_wc_product_vendors' ) ?></h2>

    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="update_vendor_id" value="<?php echo $id ?>" />
        <?php echo wp_nonce_field( 'yith_vendor_admin_update', 'yith_vendor_admin_update_nonce', true, false ) ?>

        <p class="form-field">
            <label for="vendor_name"><?php _e( 'Name:', 'yith_wc_product_vendors' ) ?></label>
            <input id="vendor_name" type="text" name="vendor_name" value="<?php echo $name ?>" class="regular-text" style="width:auto;" />
        </p>

        <p class="form-field">
            <label for="vendor_slug"><?php _e( 'Slug:', 'yith_wc_product_vendors' ) ?></label>
            <input id="vendor_slug" type="text" name="vendor_slug" value="<?php echo $slug ?>" class="regular-text" style="width:auto;" />
        </p>

        <p class="form-field">
            <label for="vendor_paypal_address"><?php _e( 'PayPal email address:', 'yith_wc_product_vendors' ) ?></label>
            <input id="vendor_paypal_address" type="text" name="vendor_paypal_address" value="<?php echo $paypal_email ?>" class="regular-text" style="width:auto;" />
        </p>

        <p class="form-field">
            <label for="vendor_description"><?php  _e( 'Description:', 'yith_wc_product_vendors' ) ?></label>
            <textarea id="vendor_description" name="vendor_description" rows="10" cols="50" class="large-text"><?php echo $description ?></textarea>
        </p>

        <p class="form-field">
            <span class="vendor-extra-info">
                <?php _e( 'Commission Rate: ', 'yith_wc_product_vendors' ); ?>
            </span>
            <?php echo $commission ?>%
        </p>

        <p class="form-field">
            <span class="vendor-extra-info">
                <?php _e( "Vendor's selling status: ", 'yith_wc_product_vendors' ); ?>
            </span>
            <?php 'yes' == $enable_selling ? _e( 'Enabled', 'yith_wc_product_vendors' ) : _e( 'Disabled', 'yith_wc_product_vendors' ) ?>
        </p>

        <?php do_action( 'yith_product_vendors_details_fields', $id ); ?>

        <p class="submit">
            <input name="Submit" type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Save Vendor Information', 'yith_wc_product_vendors' ) ) ?>" />
        </p>
    </form>
</div>
