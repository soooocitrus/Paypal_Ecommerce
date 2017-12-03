<?php
	session_start();
	include_once('csrf.php');
?>
<?php
	function ierg4210_login(){
		//echo "<script>alert(\"ds\")</script>";
		$login_success = false;

        if (empty($_POST['email']) || empty($_POST['pw'])
            || !preg_match('/^[\w_\.\-]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$/', $_POST['email'])
            || !preg_match('/^[\w@#$%\^\&\*\-]+$/', $_POST['pw'])){
        	header('Location: login.php?error=format', true, 302);
        	throw new Exception('Wrong Credentials');
        }else{
        	$db = "IERG4210_USER";
			$host = "localhost";
			$username = "sooocitrus";
			$password = "123456";
			$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
		    $connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
		    $connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		    $connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

		    $email = $_POST['email'];
            $stmt = $connection->prepare("SELECT * FROM user WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();

            if (empty($result)) {
            	header('Location: login.php?error=user', true, 302);
                throw new Exception('Wrong users');
            }else{
            	$salt = $result['salt'];
                $password = $result['password'];
                $saltPassword = hash_hmac('sha1', $_POST['pw'], $salt);
                if ($saltPassword == $password) {
                	//reset session
                	session_regenerate_id();
                    $exp = time()+3600*24*3;
                    $token = array(
                        'em'=>$email,
                        'exp'=>$exp,
                        'k'=>hash_hmac('sha1', $exp.$password, $salt)
                    );
                    //create cookie, make it HTTP only
                    //setcookie() must be called before printing anything out
                    setcookie('t4210',json_encode($token),$exp,'','',false,true);
                    //put it also in the session
                    $_SESSION['t4210'] = $token;
                    $login_success=true;
                }
            }

        }


        if($login_success){
        	if($_POST['email'] == "admin@gmail.com"){
        		header('Location: admin.php', true, 302);
        	}else{
        		header('Location: main.php', true, 302);
        	}
            exit();
        }else{
        	header('Location: login.php?error=password', true, 302);
			throw new Exception('Wrong password');
        }
	}
	function ierg4210_logout(){
		// clear the cookies and session
		if (isset($_COOKIE['t4210'])) {
			unset($_COOKIE['t4210']);
			setcookie('t4210',null,-1);
            session_start();
            session_unset();
            session_destroy();
		}

		// redirect to login page after logout
        header('Location:login.php', true, 302);
        exit();
	}

	try {
        // input validation
        if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
            throw new Exception('Undefined Action');

        // check if the form request can present a valid nonce
        if($_REQUEST['action']=='login' || $_REQUEST['action']=='logout')
        	csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);

        // run the corresponding function according to action

        $db = "IERG4210_USER";
		$host = "localhost";
		$username = "sooocitrus";
		$password = "123456";
		$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
		$connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
		$connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		$connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

        if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
            if ($connection && $connection->errorCode()){
                error_log(print_r($connection->errorInfo(), true));
            }
            throw new Exception('Failed');
        } else {
            // no functions are supposed to return anything
            // echo $returnVal;
        }
    }catch(PDOException $e){
        error_log($e->getMessage());
        echo 'Error Occurred: DB!!!!!! <br/>Redirecting to login page in 10 seconds...';
        header('Refresh: 10; url=login.php?error=db');
    }catch(Exception $e) {
        echo 'Error Occurred: ' . $e->getMessage() . '<br/>Redirecting to login page in 10 seconds...';
        header('Refresh: 10; url=login.php?error=' . $e->getMessage());
    }
?>