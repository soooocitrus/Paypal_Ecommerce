<?php
	define("LOG_FILE", "/var/www/ipn.log");
	session_start();
	include_once('csrf.php');
	date_default_timezone_set("Asia/Hong_Kong");
?>
<?php
	function loggedin(){
	    if (!empty($_SESSION['t4210'])){
	        return $_SESSION['t4210']['em'];
	    }
	    if (!empty($_COOKIE['t4210'])) {
	        // stripslashes returns a string with backslashes stripped off.
	        //(\' becomes ' and so on)
	        if ($t = json_decode(stripslashes($_COOKIE['t4210']), true)) {
	            if (time() > $t['exp']) return false; //to expire the user
	            $db = "IERG4210_USER";
				$host = "localhost";
				$username = "sooocitrus";
				$password = "123456";
				$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
			    $connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
			    $connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
			    $connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
	            $stmt = $connection->prepare("SELECT * FROM user WHERE email = :email");
	            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
	            $stmt->execute();
	            $result = $stmt->fetch();
	            if ($result) {
	                $realk = hash_hmac('sha1', $t['exp'] . $result['password'], $result['salt']);
	                if ($realk == $t['k']) {
	                    $_SESSION['t4210'] = $t;
	                    return $t['em'];
	                }
	            }
	        }
	    }
	    return false;
	}
	if (!loggedin()) {
	    $data = array(
            'ifLogin' => 0,
        );
        echo json_encode($data);
	    exit();
	}
?>
<?php
    $db = "IERG4210";
	$host = "localhost";
	$username = "sooocitrus";
	$password = "123456";
	$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
	$connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
	$connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
	$connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

    $msg = json_decode($_POST['item_list']);
    if ($msg != null) {
        $total = 0.0;
        $item_list = "{";
        foreach ($msg as $pid => $quantity) {
        	if($quantity < 0){
        		print_r($quantity);
				throw new Exception("Error Processing Request -- quantity", 1);
        	}
            $stmt= $connection -> prepare("SELECT price FROM products WHERE pid = :pid;");
			$stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if(count($result) != 1){
				print_r($result);
				throw new Exception("Error Processing Request -- price", 1);
			}
            $price = $result['price'];
            settype($price, "float");
            $subTotal = $price * $quantity;
            $item_list .= $pid . ":{" . $quantity . "," . $subTotal . "},";
            $total += $subTotal;
        }
        $item_list .= "}";
        $salt = mt_rand();
        $message = "HKD;oneera.citrus-facilitator@gmail.com;" . $salt . ";" . $item_list . ";" . $total;
        error_log(date("Y-m-d H:i:s"). "item_list " . $message. PHP_EOL, 3, LOG_FILE);
        
        $digest = hash('md5', $message);
        $createdtime = date("Y-m-d H:i:s");
        $stmt= $connection -> prepare("INSERT INTO orders (email,digest,salt,createdtime,status) VALUES (:email,:digest,:salt,:createdtime,:status)");
        $stmt->bindParam(':email', loggedin(), PDO::PARAM_STR);
        $stmt->bindParam(':digest', $digest, PDO::PARAM_STR);
        $stmt->bindParam(':salt', $salt, PDO::PARAM_INT);
        $stmt->bindParam(':createdtime', $createdtime, PDO::PARAM_STR);
        $status = "UNCHARGED";
        $stmt->bindParam(':status',$status, PDO::PARAM_STR);
		$stmt->execute();
        $lastInsertId = $connection->lastInsertId();
        $data = array(
            'orderID' => $lastInsertId,
            'digest' => $digest,
        );

        echo json_encode($data);
        exit;
    }
?>
