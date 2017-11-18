	<?php
		session_start();
		include_once('csrf.php'); 
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
		            if ($result = $stmt->fetch()) {
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
		    // redirect to login
		    header('Location:login.php');
		    exit();
		}else{
			if(loggedin() != "admin@gmail.com"){
				header('Location:main.php');
			}
		}
	?>
	<?php
		function getConnection(){
			$db = "IERG4210";
			$host = "localhost";
			$username = "sooocitrus";
			$password = "123456";
			$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
		    $connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
		    $connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		    $connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
		    return $connection;
		}
		
		function ierg4210_cat_insert() {
			$cat_insert_name=$_POST['cat_insert_name'];
			// input validation or sanitization
			if (!preg_match('/^[\w\- ]+$/', $cat_insert_name)){
				throw new Exception("invalid-name");
			}
			// DB manipulation
			$connection = getConnection();
			$stmt = $connection -> prepare("INSERT INTO categories (catname) VALUES (:catname)");
			$stmt->bindParam(':catname', $cat_insert_name, PDO::PARAM_STR, 512);
			return $stmt->execute();
		}
		
		function ierg4210_cat_update() {
			$cat_update_catid=$_POST['cat_update_catid'];
			$cat_update_new_name=$_POST['cat_update_new_name'];
			// input validation or sanitization
			if (!preg_match('/^[0-9]+$/', $cat_update_catid)){
				throw new Exception("invalid-catid");
			}
			if (!preg_match('/^[\w\- ]+$/', $cat_update_new_name)){
				throw new Exception("invalid-new-name");
			}
			// DB manipulation
			$connection = getConnection();
			$stmt = $connection -> prepare("UPDATE categories SET catname = (:catname) WHERE catid = :catid");
			$stmt->bindParam(':catname', $cat_update_new_name, PDO::PARAM_STR, 512);
			$stmt->bindParam(':catid', $cat_update_catid, PDO::PARAM_INT);
			return $stmt->execute();
		}
		
		function ierg4210_cat_delete() {
    		$cat_delete_catid=$_POST['cat_delete_catid'];
			// input validation or sanitization
			if (!preg_match('/^[0-9]+$/', $cat_delete_catid)){
				throw new Exception("invalid-catid");
			}
			// DB manipulation
			$connection = getConnection();
			$stmt = $connection -> prepare("DELETE FROM categories WHERE catid = :catid");
			$stmt->bindParam(':catid', $cat_delete_catid, PDO::PARAM_INT);
			return $stmt->execute();
		}
		
		function ierg4210_prod_insert() {
			$prod_insert_catid=$_POST['prod_insert_catid'];
			$prod_insert_name=$_POST['prod_insert_name'];
			$prod_insert_price=$_POST['prod_insert_price'];
			$prod_insert_description=$_POST['prod_insert_description'];
			// input validation or sanitization
			if (!preg_match('/^[\w\- ]+$/', $prod_insert_name)){
				throw new Exception("invalid-name");
			}
			if (!preg_match('/^[\w\-, .\n\r]+$/', $prod_insert_description)){
				throw new Exception("invalid-description");
			}
			if (!preg_match('/^[0-9]{1,10}(\.[0-9]{0,2})?$/', $prod_insert_price)){
				throw new Exception("invalid-price");
			}
			// DB manipulation
			$connection = getConnection();
			$stmt = $connection -> prepare("INSERT INTO products (catid,pname,price,description) VALUES (:catid,:pname,:price,:description)");
			$stmt->bindParam(':catid', $prod_insert_catid, PDO::PARAM_INT);
			$stmt->bindParam(':pname', $prod_insert_name, PDO::PARAM_STR, 512);
			//TODO: bind double type to :price
			//$stmt->bindParam(':price', $prod_insert_price, PDO::PARAM_INT);
			$stmt->bindParam(':price', $prod_insert_description);
			$stmt->bindParam(':description', $prod_insert_description, PDO::PARAM_STR, 512);

			if(!($stmt->execute())){
				return false;
			}

			$lastInsertId = $connection->lastInsertId();

			if($lastInsertId != 0){
				if(isset($_FILES["prod_insert_image"])){
					$allowed = array("jpg" => "image/jpg",
			        	"jpeg" => "image/jpeg",
			        	"gif" => "image/gif", 
			        	"png" => "image/png");
			        	$filename = $_FILES["prod_insert_image"]["name"];
			        	$filetype = $_FILES["prod_insert_image"]["type"];
			        	$filesize = $_FILES["prod_insert_image"]["size"];
			        	// Verify file extension
			        	$ext = pathinfo($filename, PATHINFO_EXTENSION);
			        
			        	if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
			    
			        	// Verify file size - 10MB maximum
			        	$maxsize = 10 * 1024 * 1024;
			        	if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
			    
			        	// Verify MYME type of the file
			        	if(in_array($filetype, $allowed)){
			           		 // Check whether file exists before uploading it
			           		 if(file_exists("image/".$lastInsertId.".jpeg")){
			               			 echo $lastInsertId.".jpeg" . " is already exists.";
			           		 } else{
			               			 move_uploaded_file($_FILES["prod_insert_image"]["tmp_name"], "image/".$lastInsertId.".jpeg");
			               			 echo "Your file was uploaded successfully.";
			               			 return true;
			           		 } 
			       		} else{
			            	echo "Error: There was a problem uploading your file. Please try again."; 
			        	}
			    	} else{
			        echo "Error: ".$_FILES["prod_insert_image"]["error"];
			    }
			}
			return false;
		}
		
		function ierg4210_prod_update() {
			$prod_update_catid=$_POST['prod_update_catid'];
			$prod_update_pid=$_POST['prod_update_pid_'.$prod_update_catid];
			$prod_update_new_name=$_POST['prod_update_new_name'];
			$prod_update_price=$_POST['prod_update_price'];
			$prod_update_description=$_POST['prod_update_description'];
			// DB manipulation

			if (!preg_match('/^[0-9]+$/', $prod_update_catid)){
				throw new Exception("invalid-catid");
			}
			if (!preg_match('/^[0-9]+$/', $prod_update_pid)){
				echo $prod_update_pid;
				throw new Exception("invalid-pid");
			}

			if(strlen($prod_update_new_name)!=0){
				// input validation or sanitization
				if (!preg_match('/^[\w\- ]+$/', $prod_update_new_name)){
					throw new Exception("invalid-name");
				}
				$connection = getConnection();
				$stmt = $connection -> prepare("UPDATE products SET pname = (:pname) WHERE pid = :pid AND catid = :catid ");
				$stmt->bindParam(':pname', $prod_update_new_name, PDO::PARAM_STR, 512);
				$stmt->bindParam(':pid', $prod_update_pid, PDO::PARAM_INT);
				$stmt->bindParam(':catid', $prod_update_catid, PDO::PARAM_INT);
				if(!($stmt->execute())){
					return false;
				}
			}
			if(strlen($prod_update_price)!=0){
				// input validation or sanitization
				if (!preg_match('/^[0-9]{1,10}(\.[0-9]{0,2})?$/', $prod_update_price)){
					throw new Exception("invalid-price");
				}
				$connection = getConnection();
				$stmt = $connection -> prepare("UPDATE products SET price = (:price) WHERE pid = :pid AND catid = :catid ");
				//TODO: bind double type to :price
				//$stmt->bindParam(':price', $prod_update_price, PDO::PARAM_INT);
				$stmt->bindParam(':price', $prod_update_price);
				$stmt->bindParam(':pid', $prod_update_pid, PDO::PARAM_STR);
				$stmt->bindParam(':catid', $prod_update_catid, PDO::PARAM_INT);
				if(!($stmt->execute())){
					return false;
				}
			}
			if(strlen($prod_update_description)!=0){
				// input validation or sanitization
				if (!preg_match('/^[\w\-, .\n\r]+$/', $prod_update_description)){
					throw new Exception("invalid-description");
				}
				$connection = getConnection();
				$stmt = $connection -> prepare("UPDATE products SET description = (:description) WHERE pid = :pid AND catid = :catid ");
				$stmt->bindParam(':description', $prod_update_description, PDO::PARAM_STR, 512);
				$stmt->bindParam(':pid', $prod_update_pid, PDO::PARAM_INT);
				$stmt->bindParam(':catid', $prod_update_catid, PDO::PARAM_INT);
				if(!($stmt->execute())){
					return false;
				}
			}
			if(isset($_FILES["prod_update_image"])){
				if($_FILES["prod_update_image"]["error"] == 0){
				    $allowed = array("jpg" => "image/jpg",
				    "jpeg" => "image/jpeg",
			        "gif" => "image/gif", 
			        "png" => "image/png");
			        $filename = $_FILES["prod_update_image"]["name"];
			        $filetype = $_FILES["prod_update_image"]["type"];
			        $filesize = $_FILES["prod_update_image"]["size"];
			        // Verify file extension
			        $ext = pathinfo($filename, PATHINFO_EXTENSION);
			        
			        if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
			    
			        // Verify file size - 10MB maximum
			        $maxsize = 10 * 1024 * 1024;
			        if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
			    
			        // Verify MYME type of the file
			        if(in_array($filetype, $allowed)){
			            // Check whether file exists before uploading it
			            if(file_exists("image/".$prod_update_pid.".jpeg")){
			                echo "image/".$prod_update_pid.".jpeg" . " is already exists.";
			                move_uploaded_file($_FILES["prod_update_image"]["tmp_name"], "image/".$prod_update_pid.".jpeg");
			                echo "Your file was uploaded successfully.";
			            } else{
			                move_uploaded_file($_FILES["prod_update_image"]["tmp_name"], "image/".$prod_update_pid.".jpeg");
			                echo "Your file was uploaded successfully.";
			            } 
			        } else{
			            echo "Error: There was a problem uploading your file. Please try again."; 
			            return false;
			        }
		    	}else{
		    		return false;
		    	}
			}
			return true;
		}
		
		function ierg4210_prod_delete() {
			$prod_delete_catid=$_POST['prod_delete_catid'];
			$prod_delete_pid=$_POST['prod_delete_pid_'.$prod_delete_catid];
			// input validation or sanitization
			if (!preg_match('/^[0-9]+$/', $prod_delete_catid)){
				throw new Exception("invalid-catid");
			}
			if (!preg_match('/^[0-9]+$/', $prod_delete_pid)){
				throw new Exception("invalid-pid");
			}
			// DB manipulation
			$connection = getConnection();
			$stmt = $connection -> prepare("DELETE FROM products WHERE pid = :pid AND catid = :catid");
			$stmt->bindParam(':catid', $prod_delete_catid, PDO::PARAM_INT);
			$stmt->bindParam(':pid', $prod_delete_pid, PDO::PARAM_INT);
			if($stmt->execute()){
				unlink ("image/".$prod_delete_pid.".jpeg");
				return true;
			}else{
				return false;
			}
		}

		/*switch ($_REQUEST['action']){
			case "cat_insert":
			  ierg4210_cat_insert();
			  echo "cat_insert";
			  break;
			case "cat_update":
			  ierg4210_cat_update();
			  echo "cat_update";
			  break;
			case "cat_delete":
			  ierg4210_cat_delete();
			  echo "cat_delete";
			  break;
			case "prod_insert":
			  ierg4210_prod_insert();
			  echo "prod_insert";
			  break;
			case "prod_update":
			  ierg4210_prod_update();
			  echo "prod_update";
			  break;
			case "prod_delete":
			  ierg4210_prod_delete();
			  echo "prod_delete";
			  break;
			default:
			  echo "Unknow action";
		}
		header('Location: admin.php');
		//header( "refresh:1;url=admin_panel.php" );*/
	?>
	<?php 
		// input validation
		if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
			header('Location: admin.php');
			throw new Exception('Undefined Action');
		}
		try {
		    // check if the form request can present a valid nonce
		    if ($_REQUEST['action']=="prod_insert"||$_REQUEST['action']=="cat_insert"||$_REQUEST['action']=="cat_update"||$_REQUEST['action']=="cat_update"||$_REQUEST['action']=="prod_delete"||$_REQUEST['action']=="cat_delete")
		        csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);

		    $connection = getConnection();
			if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
				if ($connection && $connection->errorCode()){
					error_log(print_r($connection->errorInfo(), true));
				}
				throw new Exception($_REQUEST['action'].'Failed');
			}else{
				header('Location: admin.php', true, 302);
			}
		} catch(PDOException $e) {
			error_log($e->getMessage());
	        echo 'Error Occurred: DB!!!!!! <br/>Redirecting to admin page in 10 seconds...';
	        header('Refresh: 5; url=admin.php');
		} catch(Exception $e) {
			echo 'Error Occurred: ' . $e->getMessage() . '<br/>Redirecting to admin page in 10 seconds...';
	        header('Refresh: 5; url=admin.php?' . $e->getMessage());
		}
	?>