<?php
class OmniFundGateway 
{
	var $omni_gateway = 'https://secure.gotobilling.com/os/system/gateway/transact.php';

	# merchant validation
	var $access_key;
	var $access_key_secret;
	var $ip_address;
	var $debug = 0;
	var $transaction_type;
	var $customer_id;
	# optional
	var $company;
	var $first_name;
	var $last_name;
	var $address1;
	var $address2;
	var $city;
	var $state;
	var $zip;
	var $country;
	var $phone;
	var $email;

	# BASIC TRANSACTION INFORMATION
	# required
	var $invoice_id;
	var $amount;
	# optional
	var $process_date;

	# CREDIT CARD SPECIFIC
	# required
	var $cc_number;
	var $cc_exp;
	#optional
	var $cc_name;
	var $cc_type;
	var $cc_cvv;
	var $authorization;

	# ACH SPECIFIC
	# required
	var $ach_payment_type;
	var $ach_route;
	var $ach_account;
	var $ach_account_type;
	
	# optional
	var $ach_serial;
	var $state_fee;
	var $ach_verification;

	var $last_input = "";
	var $data_sent = "";
  
    function process()
    {
	$data = $this->getUrlData(); 	// get the data to send
	$this->data_sent = $data;
    $body = $this->urlDataToArray($data);

    $args = array(
        'body'        => $body,
        'timeout'     => '100',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array(),
        'cookies'     => array(),
    );


    $this->response = wp_remote_retrieve_body(wp_remote_post( $this->omni_gateway, $args ));

    }
    
    function error() 
    {
        return $this->error;
    }

    function urlDataToArray($data){
        $arr = array();
        $rows = explode("&", $data);
        foreach ($rows as $row){
            list($field,$value) = explode("=", $row);
            $arr[$field] = urldecode($value);
        }
        return $arr;
    }
    
    function getUrlData()
    {
       
	    $data =
				"access_key="			.	$this->access_key . 
				"&access_key_secret="		.	$this->access_key_secret . 
				"&ip_address="			.	$this->ip_address . 
				"&x_transaction_type="	.	$this->transaction_type . 
				"&x_customer_id="			.	$this->customer_id . 
				"&x_invoice_id="			.	$this->invoice_id . 
				"&x_amount="				.	$this->amount;
		
				# optional
				if (isset($this->relay_type))			$data .= "&x_relay_type="		. $this->relay_type;
				if (isset($this->relay_url))			$data .= "&x_relay_url="		. $this->relay_url;
				if (isset($this->debug))				$data .= "&x_debug="			. $this->debug;
				if (isset($this->company))				$data .= "&x_company="			. $this->company;
				if (isset($this->first_name))			$data .= "&x_first_name="		. $this->first_name;
				if (isset($this->last_name))			$data .= "&x_last_name="		. $this->last_name;
				if (isset($this->address1))				$data .= "&x_address1="			. $this->address1;
				if (isset($this->address2))				$data .= "&x_address2="			. $this->address2;
				if (isset($this->city))					$data .= "&x_city="				. $this->city;
				if (isset($this->state))				$data .= "&x_state="			. $this->state;
				if (isset($this->zip))					$data .= "&x_zip="				. $this->zip;
				if (isset($this->country))				$data .= "&x_country="			. $this->country;
				if (isset($this->phone))				$data .= "&x_phone="			. $this->phone;
				if (isset($this->email))				$data .= "&x_email="			. $this->email;
				if (isset($this->process_date))			$data .= "&x_process_date="		. $this->process_date;
				if (isset($this->cc_name))				$data .= "&x_cc_name="			. $this->cc_name;
				if (isset($this->cc_number))			$data .= "&x_cc_number="		. $this->cc_number;
				if (isset($this->cc_exp))				$data .= "&x_cc_exp="			. $this->cc_exp;
				if (isset($this->cc_type))				$data .= "&x_cc_type="			. $this->cc_type;
				if (isset($this->cc_cvv))				$data .= "&x_cc_cvv="			. $this->cc_cvv;
				if (isset($this->authorization))		$data .= "&x_authorization="	. $this->authorization;
				if (isset($this->ach_payment_type))		$data .= "&x_ach_payment_type="	. $this->ach_payment_type;
				if (isset($this->ach_route))			$data .= "&x_ach_route="		. $this->ach_route;
				if (isset($this->ach_account))			$data .= "&x_ach_account="		. $this->ach_account;
				if (isset($this->ach_account_type))		$data .= "&x_ach_account_type="	. $this->ach_account_type;
				if (isset($this->ach_serial))			$data .= "&x_ach_serial="		. $this->ach_serial;
				if (isset($this->state_fee))			$data .= "&x_state_fee="		. $this->state_fee;
				if (isset($this->ach_verification))		$data .= "&x_ach_verification="	. $this->ach_verification;

        return $data;
    }
    
    //each field must be urlencoded to send with a post.  DO NOT set the variables without using these functions, as you will most
    //likely run into errors
    
    # merchant validation
	function setAccessKey( $access_key )
	{
		$this->access_key = $this->prepare_data( $access_key );
	}
	function setAccessKeySecret( $access_key_secret) 
	{
		$this->access_key_secret = $this->prepare_data( $access_key_secret );
	} 
	function setIpAddress( $ip )
	{
		$this->ip_address = $ip;
	} 
	
	function setDebug( $debug )
	{
		$this->debug = $debug;
	} 
	
	function setRelayUrl( $url )
	{
		$this->relay_url = $url;
	} 
	
	function setRelayType( $type )
	{
		$this->relay_type = strtolower($type);
	} 
	
	# Customer Fields
	function setCustomerId( $id ) 
	{
		$this->customer_id = $this->prepare_data( $id );
	} 
	
	function setCompany( $company ) 
	{
		$this->company = $this->prepare_data( $company );
	} 
	
	function setFirstName( $first_name )
	{
		$this->first_name = $this->prepare_data( $first_name );
	} 
	
	function setLastName( $last_name )
	{
		$this->last_name = $this->prepare_data( $last_name );
	} 
	
	function setAddress1( $address1 )
	{
		$this->address1 = $this->prepare_data( $address1 );
	} 
	
	function setAddress2( $address2 )
	{
		$this->address2 = $this->prepare_data( $address2 );
	} 
	
	function setCity( $city )
	{
		$this->city = $this->prepare_data( $city );
	} 
	
	function setState( $state )
	{
		$this->state = $this->prepare_data( $state );
	} 
	
	function setZipCode( $zip )
	{
		$this->zip = $this->prepare_data( $zip );
	} 
	
	function setCountry( $country )
	{
		$this->country = $this->prepare_data( $country );
	} 
	
	function setPhone( $phone )
	{
		$this->phone = $this->prepare_data( $phone );
	} 
	
	function setEmail( $email )
	{
		 $this->email = $this->prepare_data( $email );
	}  
	
	# Transaction Fields
	# set transaction type; CC: AS,CR,DS,ES,VO  ACH: DH,DC;
	function setTransactionType( $transaction_type )
	{
		$this->transaction_type = $this->prepare_data( $transaction_type );
	} 
	
	function setInvoiceId( $invoice_id )
	{
		$this->invoice_id = $this->prepare_data( $invoice_id );
	} 
	
	function setAmount( $amount )
	{
		$this->amount = $this->prepare_data( $amount ); 
	} 
	
	function setProcessDate( $date )
	{
		$this->process_date = $this->prepare_data( $date );
	} 
	
	# Fee Fields
	# set fees
	function setStateFee( $state_fee )
	{
		$this->state_fee = $this->prepare_data( $state_fee );
	} 
	
	# Credit Card Fields
	function setCcName( $name )
	{
		$this->cc_name = $this->prepare_data( $name );
	} 
	function setCcType( $type )
	{
		$this->cc_type = $this->prepare_data( $type );
	} 
	function setCcNumber( $cc_number )
	{
		$this->cc_number = $this->prepare_data( $cc_number );
	} 
	
	function setCcExpiration( $cc_exp )
	{
		$this->cc_exp = $this->prepare_data( $cc_exp );
	} 
	
	function setCcVerification( $cc_cvv )
	{
		$this->cc_cvv = $this->prepare_data( $cc_cvv );
	} 
	
	
	function setAuthorization( $auth )
	{
		$this->authorization = $this->prepare_data( $auth );
	} 
	
	# ACH FIELDS
	# length=2; PPD,WEB,TEL,RCK,ARC
	function setAchPaymentType( $ach_type )
	{
		$this->ach_payment_type = $this->prepare_data( $ach_type );
	} 
	
	
	function setAchRoute( $ach_route )
	{
		$this->ach_route = $this->prepare_data( $ach_route );
	} 
	
	function setAchAccount( $ach_account )
	{
		$this->ach_account = $this->prepare_data( $ach_account );
	} 
	
	
	function setAchAccountType( $ach_account_type )
	{
		$this->ach_account_type = $this->prepare_data( $ach_account_type );
	} 
	
	
	function setAchSerial( $ach_serial )
	{
		$this->ach_serial = $this->prepare_data( $ach_serial );
	}
	
	function setAchVerification( $verify ) {
		$this->ach_verification = $verify;
	} 
	
	# PREPARE SUBMITTED INFORMATION
	function prepare_data( $input ) 
	{

		$this->last_input = var_export($input, true);

		if (is_array($input))
			$output = urlencode(implode(' ', $input));
		else
			$output = urlencode($input);
		return $output;
	}

	# PREPARE SUBMITTED INFORMATION
	function getStatus() 
	{
		$this->last_input = $this->response;
		preg_match("/<status>(.*)<\/status>/", $this->response, $status);
		return $status[1];
	}
	
	function getOrderNumber() 
	{
		preg_match("/<order_number>(.*)<\/order_number>/", $this->response, $order);
		return $order[1];
	}
	
	function getTerminationCode() 
	{
		preg_match("/<term_code>(.*)<\/term_code>/", $this->response, $termCode);
		return $termCode[1];
	}
	
	function getTransactionDate() 
	{
		preg_match("/<tran_date>(.*)<\/tran_date>/", $this->response, $tranDate);
		return $tranDate[1];
	}
	
	function getTransactionTime() 
	{
		preg_match("/<tran_time>(.*)<\/tran_time>/", $this->response, $tranTime);
		return $tranTime[1];
	}
	
	function getTransactionAmount() 
	{
		preg_match("/<tran_amount>(.*)<\/tran_amount>/", $this->response, $tranAmt);
		return $tranAmt[1];
	}
	
	function getInvoiceId() 
	{
		preg_match("/<invoice_id>(.*)<\/invoice_id>/", $this->response, $invoiceId);
		return $invoiceId[1];
	}
	
	function getTerminationDescription() 
	{
		preg_match("/<description>(.*)<\/description>/", $this->response, $invoiceId);
		return $invoiceId[1];
	}
	
	function getAuthCode() 
	{
		preg_match("/<auth_code>(.*)<\/auth_code>/", $this->response, $invoiceId);
		return $invoiceId[1];
	}

}
