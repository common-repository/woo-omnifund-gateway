<?php
/*
 * Plugin Name: OmniFund Payment Gateway for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/woo-omnifund-gateway/
 * Description: WooCommerce Integration for the OmniFund payment gateway
 * Version: 1.1.4
 * Author: OmniFund
 * Author URI: https://www.omnifund.com/
 * License: GPL3
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 9.2.3
*/

/** Check if WooCommerce is active **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action('plugins_loaded', 'init_omnifund_gateway', 0);

    function init_omnifund_gateway() {
        /**
         * Invoice Payment Gateway
         *
         */
        class WC_Gateway_OmniFund extends WC_Payment_Gateway {
            /**
             * Constructor for the gateway.
             *
             */

            public function __construct() {
                $this->id		= 'omnifund';
                $this->icon 		= apply_filters('woocommerce_invoice_icon', '');
                $this->has_fields 	= true;
                $this->method_title     = __( 'OmniFund Payment Gateway', 'dg_wc_omnifund' );
                $this->method_description = __('Accept payments through your merchant account using the OmniFund custom payment gateway.', 'dg_wc_omnifund');

                // Load the form fields.
                $this->init_form_fields();

                // Load the settings.
                $this->init_settings();

                // Define user set variables
                $this->title = isset($this->settings['title']) ? $this->settings['title']: "";
                $this->description = isset($this->settings['description']) ? $this->settings['description'] : "";
                $this->emailmsg = isset($this->settings['emailmsg']) ? $this->settings['emailmsg'] : "";
                $this->accesskey = isset($this->settings['accesskey']) ? $this->settings['accesskey'] : "";
                $this->accesssecret = isset($this->settings['accesssecret']) ? $this->settings['accesssecret'] : "";
                $this->testmode = isset($this->settings['testmode']) ? $this->settings['testmode'] : "no";
                $this->acceptach = isset($this->settings['acceptach']) ? $this->settings['acceptach']: "no";
                $this->recaptcha = isset($this->settings['recaptcha']) ? $this->settings['recaptcha'] : "no";
                $this->recaptchakey = isset($this->settings['recaptchakey']) ? $this->settings['recaptchakey'] : "";
                $this->recaptchasecret = isset($this->settings['recaptchasecret']) ? $this->settings['recaptchasecret'] : "";

                // Extrapolated recaptcha enabled flag, based on items set above.
                $this->recaptchaenabled = (($this->recaptcha == 'yes') && !empty($this->recaptchakey) && !empty($this->recaptchasecret));

                // Actions
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action('woocommerce_thankyou_omnifund', array(&$this, 'thankyou_page'));

                // Customer Emails
                add_action('woocommerce_email_before_order_table', array(&$this, 'email_instructions'), 10, 2);


            }
            /**
             * Initialize Gateway Settings Form Fields
             *
             */
            function init_form_fields() {

                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable/Disable', 'dg_wc_omnifund' ),
                        'type' => 'checkbox',
                        'label' => __( 'Enable OmniFund', 'dg_wc_omnifund' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'dg_wc_omnifund' ),
                        'type' => 'text',
                        'description' => __( 'Title the user will see for this payment method during checkout.', 'dg_wc_omnifund' ),
                        'default' => __( 'OmniFund', 'dg_wc_omnifund' )
                    ),
                    'description' => array(
                        'title' => __( 'Order Review Message', 'dg_wc_omnifund' ),
                        'type' => 'textarea',
                        'description' => __( 'Description that shows on the order review page beneath the Title you specified for this payment method.' ),
                        'default' => __( '', 'dg_wc_omnifund' )
                    ),
                    'emailmsg' => array(
                        'title' => __( 'Order Success Message', 'dg_wc_omnifund' ),
                        'type' => 'textarea',
                        'description' => __( 'Message included on the order confirmation screen after payment is successful.' ),
                        'default' => __( '', 'dg_wc_omnifund' )
                    ),
                    'accesskey' => array(
                        'title' => __( 'Access Key', 'dg_wc_omnifund' ),
                        'type' => 'text',
                        'description' => __( 'Access key generated by your OmniFund account.', 'dg_wc_omnifund' ),
                        'default' => __( '', 'dg_wc_omnifund' )
                    ),
                    'accesssecret' => array(
                        'title' => __( 'Access Key Secret', 'dg_wc_omnifund' ),
                        'type' => 'text',
                        'description' => __( 'Access key secret generated by your OmniFund account.', 'dg_wc_omnifund' ),
                        'default' => __( '', 'dg_wc_omnifund' )
                    ),
                    'acceptach' => array(
                        'title' => __( 'ACH', 'dg_wc_omnifund' ),
                        'type' => 'checkbox',
                        'label' => __( 'When enabled, your store will accept both Credit Card and ACH payments.  When not enabled, your store will only accept Credit Card payments.', 'dg_wc_omnifund' ),
                        'default' => 'no'
                    ),
                    'testmode' => array(
                        'title' => __( 'Test Mode', 'dg_wc_omnifund' ),
                        'type' => 'checkbox',
                        'label' => __( 'When enabled, all transactions will be sent in test mode.', 'dg_wc_omnifund' ),
                        'default' => 'no'
                    ),
                    'recaptcha' => array(
                        'title' => __( 'Enable reCAPTCHA?', 'dg_wc_omnifund' ),
                        'type' => 'checkbox',
                        'label' => __( 'When enabled, your store will verify that the customer is a human.  When not enabled, your store may be vulnerable to fraudsters running scripts against your store, which can incur costly fees.  <b><i>Please consider carefully if you are not going to enable this option.</i></b>', 'dg_wc_omnifund' ),
                        'default' => 'yes'
                    ),
                    'recaptchakey' => array(
                        'title' => __( 'reCAPTCHA v3 Key', 'dg_wc_omnifund' ),
                        'type' => 'text',
                        'description' => __( 'Key generated for reCAPTCHA for your site.  To obtain a key for your site, visit <a href="https://www.google.com/recaptcha/admin/" target="_new">Google\'s reCAPTCHA page</a>', 'dg_wc_omnifund' ),
                        'default' => __( '', 'dg_wc_omnifund' )
                    ),
                    'recaptchasecret' => array(
                        'title' => __( 'reCAPTCHA v3 Secret', 'dg_wc_omnifund' ),
                        'type' => 'text',
                        'description' => __( 'Secret generated for reCAPTCHA for your site.', 'dg_wc_omnifund' ),
                        'default' => __( '', 'dg_wc_omnifund' )
                    )

                );

            }
            /**
             * Output for the order received page.
             *
             */
            function thankyou_page() {
                if ( $emailmsg = $this->emailmsg ){
                    echo wpautop( wptexturize( esc_attr($emailmsg) ) );
                }
            }

            /**
             * Add content to the WC emails.
             *
             */
            function email_instructions( $order, $sent_to_admin ) {
                if ( $sent_to_admin ) return;

                if ( $order->status !== 'on-hold') return;

                if ( $order->payment_method !== 'omnifund') return;

                if ( $emailmsg = $this->emailmsg ){
                    echo wpautop( wptexturize( esc_attr($emailmsg) ) );
                }
            }

            /**
             * Process the payment and return the result
             *
             */
            function process_payment( $order_id ) {
                global $woocommerce;

                if ( !class_exists( 'OmniFundGateway' ) ) {
                    require('OmniFundGateway.php');
                }

                $order = new WC_Order( $order_id );

                $order_data = $order->get_data();

                $omnifund_gateway = new OmniFundGateway();

                $omnifund_gateway->setAccessKey( $this->accesskey );
                $omnifund_gateway->setAccessKeySecret( $this->accesssecret );
                $omnifund_gateway->setIpAddress( $_SERVER['REMOTE_ADDR'] );

                // Set Test Mode if necessary
                if ($this->testmode == 'yes'){
                    $omnifund_gateway->setDebug( 'true' );
                }

                $omnifund_gateway->setCompany( $order_data['billing']['company'] );

                $omnifund_gateway->setFirstName( $order_data['billing']['first_name'] );
                $omnifund_gateway->setLastName( $order_data['billing']['last_name'] );
                $omnifund_gateway->setAddress1( $order_data['billing']['address_1'] );
                $omnifund_gateway->setCity( $order_data['billing']['city'] );
                $omnifund_gateway->setState( $order_data['billing']['state'] );
                $omnifund_gateway->setZipCode( $order_data['billing']['postcode'] );
                $omnifund_gateway->setCountry( $order_data['billing']['country'] );
                $omnifund_gateway->setPhone( $order_data['billing']['phone'] );
                $omnifund_gateway->setEmail( $order->get_billing_email() );

                /*
                    AS – Authorize Only
                    DS – Capture Only
                    ES – Authorize & Capture
                    CR – Credit/Refund
                    VO – Void
                    AV – AVS Check Only
                    OF – Offline (Force)
                */

                if ($_POST['omnifund_payment_method'] == "ach") {
                    $omnifund_gateway->setTransactionType( 'DH' );
                    $omnifund_gateway->setAchPaymentType('WEB');
                    $omnifund_gateway->setAchRoute(sanitize_text_field($_POST['omnifund_achrouting']));
                    $omnifund_gateway->setAchAccount(sanitize_text_field($_POST['omnifund_achaccount']));
                } else {
                    $omnifund_gateway->setTransactionType( 'ES' );
                    $omnifund_gateway->setCcName( $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'] );
                    $omnifund_gateway->setCcNumber( sanitize_text_field($_POST['omnifund_cc_number'] ));
                    $omnifund_gateway->setCcExpiration( sprintf('%02d', sanitize_text_field($_POST['omnifund_cc_exp_month'])) . substr(sanitize_text_field($_POST['omnifund_cc_exp_year']), 2) );
                    $omnifund_gateway->setCcVerification( sanitize_text_field($_POST['omnifund_cvv'] ));
                }

                //$omnifund_gateway->setAmount( $order->get_total() * 100 );
                $omnifund_gateway->setAmount( $order->get_total() );

                //$omnifund_gateway->setInvoiceId( '1234564' );

                $omnifund_gateway->setCustomerId( $order->get_billing_email() );

                $omnifund_gateway->process();

                if (($omnifund_gateway->getStatus() == "G" ) || (($_POST['omnifund_payment_method'] == "ach") && ($omnifund_gateway->getStatus() == "R"))) {
                    // Success
                    $order->add_order_note( __('OmniFund Payment Completed.  Auth Code: ' . $omnifund_gateway->getAuthCode(), 'dg_wc_omnifund') );
                    $order->payment_complete();

                } else {
                    // Fail
                    wc_add_notice( __('Payment error:', 'dg_wc_omnifund') . $omnifund_gateway->getTerminationDescription(), 'error' );
                    return;
                }

                // Remove cart
                $woocommerce->cart->empty_cart();

                // Empty awaiting payment session
                unset( $woocommerce->session->order_awaiting_payment );

                // Return thankyou redirect
                return array(
                    'result' 	=> 'success',
                    'redirect'	=> $this->get_return_url( $order )
                );
            }

            /**
             * Render Payment Fields
             *
             */
            function payment_fields() {


                if ($this->description) echo wpautop(wptexturize(esc_attr($this->description)));
                if ($this->recaptchaenabled) {
                    echo "
                    <script src=\"https://www.google.com/recaptcha/api.js?render=".esc_attr($this->recaptchakey)."\"></script>
                    <script>
                        function onOmnifundSubmit() {
                            grecaptcha.ready(function () {
                                grecaptcha.execute('".esc_attr($this->recaptchakey)."', { action: 'contact' }).then(function (token) {
                                    var recaptchaResponse = document.getElementById('recaptchaResponse');
                                    recaptchaResponse.value = token;
                                    jQuery('#place_order').click();
                                });
                            });
                        }
                    </script>
                    ";
                }
                echo '
                <script type="text/javascript">
                    jQuery(document).ready(function( $ ) {

                        $("#omnifund_payment_method_cc").prop("checked", true);
                        $("#omnifund_cc").show();
                        $("#omnifund_ach").hide();

                        $("#omnifund_payment_method_cc").change(function(){
                            if ($( "#omnifund_payment_method_cc:checked" ).length > 0) {
                                $("#omnifund_cc").show();
                                $("#omnifund_ach").hide();
                            }
                        });

                        $("#omnifund_payment_method_ach").change(function(){
                            if ($( "#omnifund_payment_method_ach:checked" ).length > 0) {
                                $("#omnifund_cc").hide();
                                $("#omnifund_ach").show();
                            }
                        });

                    });
                </script>
                <style>
                    p.omnifund {
                        width:100% !important;
                    }
                    select.omnifund-select {
                        width:48%;
                    }
                    .omnifund-payment-spacing {
                        margin-top:10px;
                        padding:10px;
                    }
                    #omnifund_place_order{
                        width: 100%;
                        text-transform: uppercase;
                    }                        
                </style>
                ';
                if ($this->recaptchaenabled) {
                    echo '<input type="hidden" id="recaptchaResponse" name="recaptchaResponse" value="" />';
                }
                echo '
                <div '.($this->acceptach != 'yes' ? 'style="display:none;"':'').'>
                    <p class="form-row form-row-first omnifund omnifund-payment-spacing"><strong>
                            <input id="omnifund_payment_method_cc" type="radio" class="input-radio" name="omnifund_payment_method" value="cc">
                            <label for="omnifund_payment_method_cc" style="display:inline;font-weight:bolder;">Pay with Credit Card</label>
                        </strong></p>
                </div>
                <div class="clear"></div>
                <div id="omnifund_cc">
                    <p class="form-row form-row-first omnifund omnifund-payment-spacing">
                        <label>'.__("Card Number", 'dg_wc_omnifund').'
                            <span class="required">*</span></label>
                        <input class="input-text" style="width:180px;" type="text" size="16" maxlength="16" name="omnifund_cc_number" id="omnifund_cc_number" autocomplete="off" />
                    </p>
                    <div class="clear"></div>
                    <p class="form-row form-row-first omnifund omnifund-payment-spacing">
                        <label>'.__("Expiration Date", 'dg_wc_omnifund').' <span class="required">*</span></label>
                        <select id="omnifund_cc_exp_month" name="omnifund_cc_exp_month" class="input-text">
                            <option value="">'.__('Month', 'dg_wc_omnifund').'</option>
                            <option value=01> 1 - January</option>
                            <option value=02> 2 - February</option>
                            <option value=03> 3 - March</option>
                            <option value=04> 4 - April</option>
                            <option value=05> 5 - May</option>
                            <option value=06> 6 - June</option>
                            <option value=07> 7 - July</option>
                            <option value=08> 8 - August</option>
                            <option value=09> 9 - September</option>
                            <option value=10>10 - October</option>
                            <option value=11>11 - November</option>
                            <option value=12>12 - December</option>
                        </select>
                        <select id="omnifund_cc_exp_year" name="omnifund_cc_exp_year" class="input-text">
                            <option value="">'.__('Year', 'dg_wc_omnifund').'</option>
                ';

                $today1 = date('Y');
                for($i = 0; $i < 8; $i++)
                {
                    echo '<option value="'.esc_attr($today1).'">'.esc_attr($today1).'</option>';
                    $today1++;
                }

                echo '
                        </select>
                    </p>
                    <div class="clear"></div>
                    <p class="form-row form-row-first omnifund omnifund-payment-spacing">
                        <label>'.__("Card CVV", 'dg_wc_omnifund').'
                            <span class="required">*</span></label>
                        <input class="input-text" style="width:180px;" type="text" size="5" maxlength="5" id="omnifund_cvv" name="omnifund_cvv" autocomplete="off" />
                    </p>
                </div>
                <div class="clear"></div>
                ';
                if ($this->acceptach == 'yes') {
                    echo '
                    <!-- ACH -->
                    <div>
                        <p class="form-row form-row-first omnifund omnifund-payment-spacing"><strong>
                                <input id="omnifund_payment_method_ach" type="radio" class="input-radio" name="omnifund_payment_method" value="ach">
                                <label for="omnifund_payment_method_ach" style="display:inline;font-weight:bolder;">Pay with ACH</label>
                            </strong></p>
                    </div>
                    <div class="clear"></div>
                    <div id="omnifund_ach">
                        <p class="form-row form-row-first omnifund omnifund-payment-spacing">
                            <label>'.__("Routing Number", 'dg_wc_omnifund').'
                                <span class="required">*</span></label>
                            <input class="input-text" style="width:180px;" type="text" size="5" maxlength="9" id="omnifund_achrouting" name="omnifund_achrouting" autocomplete="off" />
                        </p>
                        <div class="clear"></div>

                        <p class="form-row form-row-first omnifund omnifund-payment-spacing">
                            <label>'.__("Account Number", 'dg_wc_omnifund').'
                                <span class="required">*</span></label>
                            <input class="input-text" style="width:180px;" type="text" size="5" maxlength="17" id="omnifund_achaccount" name="omnifund_achaccount" autocomplete="off" />
                        </p>
                        <div class="clear"></div>

                        <p class="form-row form-row-first omnifund omnifund-payment-spacing">
                            <label>'.__("Confirm Account Number", 'dg_wc_omnifund').'
                                <span class="required">*</span></label>
                            <input class="input-text" style="width:180px;" type="text" size="5" maxlength="17" id="omnifund_achaccount_confirm" name="omnifund_achaccount_confirm" autocomplete="off" />
                        </p>
                    </div>
                    <div class="clear"></div>';
                } 	// End Accept ACH
            }


            /**
             * Validate Payment Fields
             *
             */
            function validate_fields()
            {
                global $woocommerce;

                $wc_21 = version_compare($woocommerce->version, "2.1", ">=" );

                // Validate reCAPTCHA if enabled.
                if ($this->recaptchaenabled) {
                    $recaptcha = json_decode(wp_remote_retrieve_body(wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $this->recaptchasecret . '&response=' . sanitize_text_field($_POST['recaptchaResponse']))));

                    if ($recaptcha->score < 0.5) {
                        if($wc_21) {
                            wc_add_notice( __('reCAPTCHA was not correct.  Please try again.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('reCAPTCHA was not correct.  Please try again.', 'dg_wc_omnifund') );
                        }
                    }
                }

                if ($_POST['omnifund_payment_method'] == "ach") {
                    // ACH validation
                    if (!$_POST['omnifund_achrouting']) {
                        if($wc_21) {
                            wc_add_notice( __('Routing Number is not entered.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('Routing Number is not entered.', 'dg_wc_omnifund') );
                        }
                    }
                    if (!$_POST['omnifund_achaccount']) {
                        if($wc_21) {
                            wc_add_notice( __('Account Number is not entered.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('Account Number is not entered.', 'dg_wc_omnifund') );
                        }
                    }
                    if (!$_POST['omnifund_achaccount_confirm']) {
                        if($wc_21) {
                            wc_add_notice( __('Account Number Confirmation is not entered.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('Account Number Confirmation is not entered.', 'dg_wc_omnifund') );
                        }
                    } else {
                        if ($_POST['omnifund_achaccount_confirm']!=$_POST['omnifund_achaccount']){
                            if($wc_21) {
                                wc_add_notice( __('Account Number and Confirmation do not match.', 'dg_wc_omnifund'), $notice_type = 'error' );
                            } else {
                                $woocommerce->add_error( __('Account Number and Confirmation do not match.', 'dg_wc_omnifund') );
                            }
                        }
                    }
                } else {
                    if (empty($_POST['omnifund_cc_number'])) {
                        if($wc_21) {
                            wc_add_notice( __('Credit Card Number is not entered.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('Credit Card Number is not entered.', 'dg_wc_omnifund') );
                        }
                    }
                    //Check if Credit Card Number is valid
                    if (!empty($_POST['omnifund_cc_number']) && !$this->luhn_validate(sanitize_text_field($_POST['omnifund_cc_number']))) {					/*error_log("(Credit Card Number) is not valid.", 0);*/
                        if($wc_21) {
                            wc_add_notice( __('Credit Card Number is not valid.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('Credit Card Number is not valid.', 'dg_wc_omnifund') );
                        }
                    }
                    if (empty($_POST['omnifund_cc_exp_month']) || empty($_POST['omnifund_cc_exp_year'])) {
                        if($wc_21) {
                            wc_add_notice( __('Expiration Date is not valid.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('Expiration Date is not valid.', 'dg_wc_omnifund') );
                        }
                    }
                    if (!$_POST['omnifund_cvv']) {
                        if($wc_21) {
                            wc_add_notice( __('CVV is not entered.', 'dg_wc_omnifund'), $notice_type = 'error' );
                        } else {
                            $woocommerce->add_error( __('CVV is not entered.', 'dg_wc_omnifund') );
                        }
                    }
                }

            }

            function luhn_validate($number, $mod5 = false) {
                $parity = strlen($number) % 2;
                $total = 0;

                // Split each digit into an array
                $digits = str_split($number);
                foreach($digits as $key => $digit) { // Foreach digit
                    // for every second digit from the right most, we must multiply * 2
                    if (($key % 2) == $parity) {
                        $digit = ($digit * 2);
                    }
                    // each digit place is it's own number (11 is really 1 + 1)
                    if ($digit >= 10) {
                        // split the digits
                        $digit_parts = str_split($digit);
                        // add them together
                        $digit = $digit_parts[0]+$digit_parts[1];
                    }
                    // add them to the total
                    $total += $digit;
                }
                return ($total % ($mod5 ? 5 : 10) == 0 ? true : false); // If the mod 10 or mod 5 value is equal to zero (0), then it is valid
            }

            function ValidateCardExpiration($expirationDate) {
                $expirationDate = preg_replace('/[^0-9]+/', '', $expirationDate);
                $exp_month = substr($expirationDate,0,2);
                $exp_year = 20 . substr($expirationDate,2);
                $current_month = date("m");
                $current_year = date("Y");

                if ($exp_month > 12 || $exp_month < 1)
                    return false;

                if ($exp_year < $current_year)
                    return false;

                if ($exp_year == $current_year) {
                    if ($exp_month < $current_month)
                        return false;
                }

                return true;
            }

            function ValidateAbaNumber($aba) {

                # static array of valid first digits for routing numbers
                $valid_first_digits = array(0,1,2,3);

                $first_digit = substr($aba,0,1);

                if (!in_array($first_digit,$valid_first_digits)) {
                    return false;
                }

                if (preg_match("/^[0-9]{9}$/",$aba)) {
                    $n = 0;

                    for ($i = 0; $i < 9; $i += 3) {
                        $n += (substr($aba,$i,1) * 3)
                            + (substr($aba,$i + 1,1) * 7)
                            + (substr($aba,$i + 2,1));
                    }

                    // If the resulting sum is an even multiple of ten (but not zero),
                    // the aba routing number is good.
                    if ($n != 0 && $n % 10 == 0) {
                        return true; // found good aba
                    }
                    else {

                        return false;
                    }

                }
                else {
                    return false;
                }

            }

            function ValidateAccountNumber($accountNumber) {

                $max_length = 17;

                if (empty($accountNumber)) {
                    return false;
                }
                if (preg_match('/[^0-9]/', $accountNumber)) {
                    return false;
                }
                else if (strlen($accountNumber) > $max_length) {
                    return false;
                }

                return true;

            }

        }
    }

    /**
     * Add the gateway to WooCommerce
     *
     */
    function add_omnifund_gateway( $methods ) {
        $methods[] = 'WC_Gateway_OmniFund';
        return $methods;
    }


    /**
     * Customize the Place Order button.
     *
     */
    function omnifund_order_button_html( $button ) {

        // Get options
        if ( WC()->payment_gateways() ) {
            $payment_gateways = WC()->payment_gateways->payment_gateways();
        } else {
            $payment_gateways = array();
        }

        // Gracefully check for whether this plugin is enabled, and things are configured.
        $recaptchaenabled = false;
        if (isset($payment_gateways['omnifund']->settings['enabled']) && isset($payment_gateways['omnifund']->settings['recaptcha']) && isset($payment_gateways['omnifund']->settings['recaptchakey']) && isset($payment_gateways['omnifund']->settings['recaptchasecret'])){
            $recaptchaenabled = (($payment_gateways['omnifund']->settings['enabled'] == 'yes') && ($payment_gateways['omnifund']->settings['recaptcha'] == 'yes') && !empty($payment_gateways['omnifund']->settings['recaptchakey']) && !empty($payment_gateways['omnifund']->settings['recaptchasecret']));
        }

        if ($recaptchaenabled) {
            // Include our handler for recaptcha check, while wrapping the actual button in a div that hides it
            $order_button_text = __('Place order', 'woocommerce');
            $button = '<a href="#" onClick="onOmnifundSubmit();" class="button alt" id="omnifund_place_order">' . esc_attr( $order_button_text ) . "</a>" .
                '<div style="display:none;">' . $button . '</div>';
        } else {
            // Use the standard functionality, i.e., ignore and just return the button.
        }

        return $button;
    }

    add_filter('woocommerce_payment_gateways', 'add_omnifund_gateway');
    add_filter('woocommerce_order_button_html','omnifund_order_button_html');
}