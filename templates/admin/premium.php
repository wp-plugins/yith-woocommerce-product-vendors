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

<style>
    .section {
        margin-left: -20px;
        margin-right: -20px;
        font-family: "Raleway", san-serif;
    }

    .section h1 {
        text-align: center;
        text-transform: uppercase;
        color: #808a97;
        font-size: 35px;
        font-weight: 700;
        line-height: normal;
        display: inline-block;
        width: 100%;
        margin: 50px 0 0;
    }

    .section:nth-child(even) {
        background-color: #fff;
    }

    .section:nth-child(odd) {
        background-color: #f1f1f1;
    }

    .section .section-title img {
        display: table-cell;
        vertical-align: middle;
        width: auto;
        margin-right: 15px;
    }

    .section h2,
    .section h3 {
        display: inline-block;
        vertical-align: middle;
        padding: 0;
        font-size: 24px;
        font-weight: 700;
        color: #808a97;
        text-transform: uppercase;
    }

    .section .section-title h2 {
        display: table-cell;
        vertical-align: middle;
    }

    .section-title {
        display: table;
    }

    .section h3 {
        font-size: 14px;
        line-height: 28px;
        margin-bottom: 0;
        display: block;
    }

    .section p {
        font-size: 13px;
        margin: 25px 0;
    }

    .section ul li {
        margin-bottom: 4px;
    }

    .landing-container {
        max-width: 750px;
        margin-left: auto;
        margin-right: auto;
        padding: 50px 0 30px;
    }

    .landing-container:after {
        display: block;
        clear: both;
        content: '';
    }

    .landing-container .col-1,
    .landing-container .col-2 {
        float: left;
        box-sizing: border-box;
        padding: 0 15px;
    }

    .landing-container .col-1 img {
        width: 100%;
    }

    .landing-container .col-1 {
        width: 55%;
    }

    .landing-container .col-2 {
        width: 45%;
    }

    .premium-cta {
        background-color: #808a97;
        color: #fff;
        border-radius: 6px;
        padding: 20px 15px;
    }

    .premium-cta:after {
        content: '';
        display: block;
        clear: both;
    }

    .premium-cta p {
        margin: 7px 0;
        font-size: 14px;
        font-weight: 500;
        display: inline-block;
        width: 60%;
    }

    .premium-cta a.button {
        border-radius: 6px;
        height: 60px;
        float: right;
        background: url('<?php echo YITH_WPV_URL?>assets/images/upgrade.png') #ff643f no-repeat 13px 13px;
        border-color: #ff643f;
        box-shadow: none;
        outline: none;
        color: #fff;
        position: relative;
        padding: 9px 50px 9px 70px;
    }

    .premium-cta a.button:hover,
    .premium-cta a.button:active,
    .premium-cta a.button:focus {
        color: #fff;
        background: url(<?php echo YITH_WPV_URL?>assets/images/upgrade.png) #971d00 no-repeat 13px 13px;
        border-color: #971d00;
        box-shadow: none;
        outline: none;
    }

    .premium-cta a.button:focus {
        top: 1px;
    }

    .premium-cta a.button span {
        line-height: 13px;
    }

    .premium-cta a.button .highlight {
        display: block;
        font-size: 20px;
        font-weight: 700;
        line-height: 20px;
    }

    .premium-cta .highlight {
        text-transform: uppercase;
        background: none;
        font-weight: 800;
        color: #fff;
    }

    @media (max-width: 768px) {
        .section {
            margin: 0
        }

        .premium-cta p {
            width: 100%;
        }

        .premium-cta {
            text-align: center;
        }

        .premium-cta a.button {
            float: none;
        }
    }

    @media (max-width: 480px) {
        .wrap {
            margin-right: 0;
        }

        .section {
            margin: 0;
        }

        .landing-container .col-1,
        .landing-container .col-2 {
            width: 100%;
            padding: 0 15px;
        }

        .section-odd .col-1 {
            float: left;
            margin-right: -100%;
        }

        .section-odd .col-2 {
            float: right;
            margin-top: 65%;
        }
    }

    @media (max-width: 320px) {
        .premium-cta a.button {
            padding: 9px 20px 9px 70px;
        }

        .section .section-title img {
            display: none;
        }
    }
</style>
<div class="landing">
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    Upgrade to the <span class="highlight">premium version</span>
                    of <span class="highlight">YITH WooCommerce Multi Vendor</span> to benefit from all features!
                </p>
                <a href="<?php echo YITH_Vendors()->admin->get_premium_landing_uri(); ?>" target="_blank"
                   class="premium-cta-button button btn">
                    <span class="highlight">UPGRADE</span>
                    <span>to the premium version</span>
                </a>
            </div>

        </div>
    </div>
    <div class="section section-even clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/01-bg.png) no-repeat #fff; background-position: 85% 75%">
        <h1>Premium Features</h1>

        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/01.png" alt="Socials icon"/>
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/01-icon.png" alt="Vendor shop page"/>
                    <h2>A richer vendor shop page</h2>
                </div>
                <p>
                    The vendor page gets more details!<br>
                    Vendors will be able to add their <b>contact details</b> (email, address, phone number) and the most common
                    social network profiles (Facebook, Twitter, Google+, LinkedIn and YouTube).
                </p>

                <p>These pieces of
                    information are gathered in the page of every vendor shop with a <b>header</b> where style and images can
                    be customized.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/02-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/02-icon.png" alt="admin shop"/>
                    <h2>One or more administrators for every vendor shop
                    </h2>
                </div>
                <p>
                    The administrators of the vendor shop are one of the most important new features.<br> They are simple
                    registered users of the shop that have been admitted to <b>manage the products</b> and all the information
                    about a vendor shop by the administrator of the store or the owner of the vendor shop.
                </p>
                <p>
                    The only
                    element they can't change is the <b>type of payment</b> that only administrator of the store or the owner
                    of the vendor shop can set.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/02.png" alt="icon"/>
            </div>
        </div>
    </div>
    <div class="section section-even clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/03-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/03.png" alt="Screenshot"/>
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/03-icon.png" alt="product sale"/>
                    <h2>Direct product sale</h2>
                </div>
                <p>
                    Thanks to the <b>“Skip Admin review”</b> option, vendors can publish new products without the store
                    administrator approval. This option can be applied to all the vendor shops, or just to the selected
                    ones.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/04-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/04-icon.png" alt="Actions"/>
                    <h2>Actions for commissions</h2>
                </div>
                <p>
                    A commissions table with more details to be always up-to-date. Every commission offers a set of
                    buttons to quickly proceed to the PayPal payments (if set as manual), or to change the state of the
                    commission.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/04.png" alt="Screenshot"/>
            </div>
        </div>
    </div>
    <div class="section section-even clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/05-bg.png) no-repeat #fff; background-position: 85% 75%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/05.png" alt="Screenshot"/>
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/05-icon.png" alt="Commission rate"/>
                    <h2>Variable commission rates</h2>
                </div>
                <p>
                    <b>No more single commission rate for all the vendor shops:</b> in the premium version of the plugin, this
                    rate can be modified for every single vendor shop and for every product associated.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/06-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/06-icon.png" alt="Coupon"/>
                    <h2>Organize coupon</h2>
                </div>
                <p>
                    If users use a coupon during a purchase, how will vendor commissions be calculated? It's up to you:
                    with the <b>"Coupon handling"</b> option, you can decide whether you want to calculate the commission for
                    the discounted total or not.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/06.png" alt="Screenshot"/>
            </div>
        </div>
    </div>
    <div class="section section-even clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/07-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/07.png" alt="Screenshot"/>
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/07-icon.png" alt="payments method"/>
                    <h2>Payment methods</h2>
                </div>
                <p>
                    Set the "Payment" section of the plugin to activate the PayPal payment method. You can decide
                    whether to generate the payment requests <b>manually</b> with the relative button, or <b>leave the decision to
                    the users</b> on how and when they want to be paid: when the order will be completed, or achieving a
                    certain amount of commissions
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/08-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/08-icon.png" alt="Icon"/>
                    <h2>Advanced reports</h2>
                </div>
                <p>
                    Statistics have a central relevance in the e-commerce field and WooCommerce Multi Vendor offers you
                    different reports to <b>analyze your data</b>: from the most lucrative vendors, to the best sellers,
                    including convenient commissions diagrams for each of them.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/08.png" alt="Screenshot"/>
            </div>
        </div>
    </div>
    <div class="section section-even clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/09-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/09.png" alt="Screenshot"/>
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/09-icon.png" alt="payments method"/>
                    <h2>CSV exportation of the reports</h2>
                </div>
                <p>
                    For each report, <b>you can export its data in the CSV format</b>: in this way, you will be free to use all the report details in every fields not expressively related to WordPress.
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/10-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/10-icon.png" alt="Icon"/>
                    <h2>Vendor's product exclusion from the shop (Product listing)</h2>
                </div>
                <p>
                    The <b>"Product listing"</b> option has been conceived to exclude from the shop the products of a
                    particular vendor. In this way, these would be consultable only from the relative vendor's page.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/10.png" alt="Screenshot"/>
            </div>
        </div>
    </div>
    <div class="section section-even clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/11-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/11.png" alt="Screenshot"/>
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/11-icon.png" alt="payments method"/>
                    <h2>Three widgets for your sidebars</h2>
                </div>
                <p>
                    <b>YITH Vendor List, YITH Vendor Quick Info</b> and <b>YITH Vendor Store Location</b>: these are the three widgets
                    that the plugin offers you. Thanks to these widgets, you will be able to show the list of the
                    registered vendors of your shop, and enrich the detail page of the vendor shops with information
                    about their locations, or a contact form users can use to send emails to the shop
                    administrators. </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear"
         style="background: url(<?php echo YITH_WPV_URL ?>assets/images/12-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WPV_URL ?>assets/images/12-icon.png" alt="Icon"/>
                    <h2>Advanced administration area for each vendor</h2>
                </div>
                <p>
                    A breath of fresh air for the "vendor users" that can take advantage of a big and advanced
                    administrative area. From this place, they will be free to consult the reports about their products,
                    change their profile information and choose the payment method they prefer.
                    Each vendor can also check the commissions table, with the whole sum of the relative commissions up
                    to that moment.
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WPV_URL ?>assets/images/12.png" alt="Screenshot"/>
            </div>
        </div>
    </div>
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    Upgrade to the <span class="highlight">premium version</span>
                    of <span class="highlight">YITH WooCommerce Multi Vendor</span> to benefit from all features!
                </p>
                <a href="<?php echo YITH_Vendors()->admin->get_premium_landing_uri(); ?>" target="_blank"
                   class="premium-cta-button button btn">
                    <span class="highlight">UPGRADE</span>
                    <span>to the premium version</span>
                </a>
            </div>
        </div>
    </div>
</div>
