<?php

date_default_timezone_set("Asia/Hong_Kong");

ini_set('display_errors', 'On');
ini_set("file_uploads", "On");
ini_set("error_log", "/var/www/php.errors");

define("DEBUG", false);
define("LOG_FILE", "/var/www/ipn.log");

// Reading raw POST data from input stream instead.
$raw_post_data = file_get_contents('php://input');
echo "rawdata=".$raw_post_data;
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN msg from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

if(function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}

foreach ($myPost as $key => $value) {
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

// Post IPN data back to PayPal to validate
$ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
if ($ch == FALSE) {
    return FALSE;
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

// Set TCP timeout to 30 seconds
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);
if (curl_errno($ch) == 0) // cURL error
{
    // Log the entire HTTP response if debug is switched on.
    if(DEBUG == true) {
        error_log(date("Y-m-d H:i:s"). "HTTP request of validation request: $req" . PHP_EOL, 3, LOG_FILE);
        error_log(date("Y-m-d H:i:s"). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
    }
    curl_close($ch);
}
// Inspect IPN validation result and act accordingly
// Split response headers and payload, a better way for strcmp
$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));

if (strcmp ($res, "VERIFIED") == 0) {
    $payment_status = $_POST['payment_status'];
    $payment_amount = round(floatval($_POST['mc_gross']), 2);
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $custom = $_POST['custom'];
    $txn_type = $_POST['txn_type'];
    $invoice = (int)$_POST['invoice'];

    $i = 1;
    $list_array = array();
    $results_array = array();
    $item_id = array();
    $quantity = array();
    $price = array();

    while(!empty($_POST['item_number' .$i. '']))
    {
        $item_id[$i] = $_POST['item_number'.$i.''];
        $quantity[$i] = $_POST['quantity'.$i.''];
        $price[$i] = round(floatval($_POST['mc_gross_'.$i.'']), 2);
        $i += 1;
    }
    $db = "IERG4210";
    $host = "localhost";
    $username = "sooocitrus";
    $password = "123456";
    $connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
    $connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
    $connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

    $stmt= $connection -> prepare("SELECT COUNT(*) FROM purchased WHERE txn_id=:txn_id");
    $stmt->bindParam(':txn_id', $txn_id, PDO::PARAM_STR);
    $stmt->execute();
    $row_num = $stmt->fetchColumn();

    if ($row_num > 0){
        error_log(date("Y-m-d H:i:s") . $txn_id . " Duplicated Transaction Id" . PHP_EOL, 3, LOG_FILE);
        exit();
    }
    if ($txn_type != 'cart'){
        error_log(date("Y-m-d H:i:s") . $txn_id . " Transaction type is not cart" . PHP_EOL, 3, LOG_FILE);
        exit();
    }
    if ($payment_status != 'Completed'){
        error_log(date("Y-m-d H:i:s") . $txn_id . " Payment is not completed" . PHP_EOL, 3, LOG_FILE);
        $stmt= $connection -> prepare("UPDATE orders SET txn_id=:txn_id, status='UNVERIFIED' WHERE orderid=:orderid");
        $stmt->bindParam(':txn_id', $txn_id, PDO::PARAM_STR);
        $stmt->bindParam(':orderid', $invoice, PDO::PARAM_INT);
        $stmt->execute();
        exit();
    }


    $stmt= $connection -> prepare("SELECT digest, salt FROM orders WHERE orderid=:orderid");
    $stmt->bindParam(':orderid', $invoice, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if(count($result) != 2){
        print_r($result);
        throw new Exception("Error Processing Request -- depulicate orderid", 1);
    }

    $salt_stored = $result['salt'];
    $digest_stored = $result['digest'];

    $item_list = "{";
    for ($j = 1; $j < $i; $j++){
        $item_list .= $item_id[$j].":{".$quantity[$j].",".$price[$j]."},";
    }
    $item_list .= "}";

    $message = $payment_currency.";".$receiver_email.";".$salt_stored.";".$item_list.";".$payment_amount;
    error_log("The message ". $txn_id . " is ". $message . PHP_EOL, 3, LOG_FILE);
//    error_log(date("Y-m-d H:i:s"). "TEST-message " . $message. PHP_EOL, 3, LOG_FILE);
    $digest_regenerated = hash('md5',$message);
    error_log("The digest_regenerated ". $txn_id . " is ". $digest_regenerated . PHP_EOL, 3, LOG_FILE);
    if (strcmp($digest_regenerated, $digest_stored) == 0){
        $stmt= $connection -> prepare("UPDATE orders SET txn_id=:txn_id, status='VERIFIED' WHERE orderid = :orderid");
        $stmt->bindParam(':txn_id', $txn_id, PDO::PARAM_STR);
        $stmt->bindParam(':orderid', $invoice, PDO::PARAM_INT);
        $stmt->execute();

        error_log(date(' [Y-m-d H:i e] ') . $txn_id . " Successfully Validated and Paid" . PHP_EOL, 3, LOG_FILE );

        for ($k = 1 ; $k < $i; $k++){
            $tmp_itemid = (int)$item_id[$k];
            $tmp_itemquan = (int)$quantity[$k];
            $tmp_itempri = round( floatval($price[$k]), 2);
            $stmt= $connection -> prepare("INSERT INTO purchased (orderid,txn_id,pid,quantity,price) VALUES (:orderid,:txn_id,:pid,:quantity,:price)");
            $stmt->bindParam(':orderid', $invoice, PDO::PARAM_STR);
            $stmt->bindParam(':txn_id', $txn_id, PDO::PARAM_INT);
            $stmt->bindParam(':pid', $tmp_itemid, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $tmp_itemquan, PDO::PARAM_INT);
            $stmt->bindParam(':price', $tmp_itempri, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    if(DEBUG == true) {
        error_log(date("Y-m-d H:i:s"). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
    }
} else if (strcmp ($res, "INVALID") == 0) {
    // log for manual investigation
    // Add business logic here which deals with invalid IPN messages
    if(DEBUG == true) {
        error_log(date("Y-m-d H:i:s"). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
    }
}

?>