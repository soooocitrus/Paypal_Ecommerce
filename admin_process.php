	<?php
		define('DBSERVER',"localhost");
		define('DBUSER',"sooocitrus");
		define('DBPASS',"123456");
		define('DATABASE',"IERG4210");
		
		if (!$connection = @ mysql_connect(DBSERVER, DBUSER, DBPASS))
		  die("database cannot connect");
		
		@mysql_select_db(DATABASE) or die( "Unable to select database");
		
		function ierg4210_cat_insert() {
			$cat_insert_name=$_POST[cat_insert_name];
			// input validation or sanitization
			if (!preg_match('/^[\w\-, ]+$/', $cat_insert_name)){
				throw new Exception("invalid-name");
			}
			// DB manipulation
			$sql = "INSERT INTO categories VALUES (null, '$cat_insert_name')";
			mysql_query($sql);
		}
		
		function ierg4210_cat_update() {
			$cat_update_old_name=$_POST[cat_update_old_name];
			$cat_update_new_name=$_POST[cat_update_new_name];
			// input validation or sanitization
			if (!preg_match('/^[\w\-, ]+$/', $cat_update_new_name)){
				throw new Exception("invalid-name");
			}
			// DB manipulation
			$sql = "UPDATE categories SET catname = '$cat_update_new_name' 
				WHERE catname = '$cat_update_old_name'";
    		mysql_query($sql);
		}
		
		function ierg4210_cat_delete() {
			$cat_delete_catid=$_POST[cat_delete_catid];
			$sql = "DELETE FROM categories WHERE catid = '$cat_delete_catid'";
    		mysql_query($sql);
		}
		
		function ierg4210_prod_insert() {
			$prod_insert_catid=$_POST[prod_insert_catid];
			$prod_insert_name=$_POST[prod_insert_name];
			$prod_insert_price=$_POST[prod_insert_price];
			$prod_insert_description=$_POST[prod_insert_description];
			// input validation or sanitization
			if (!preg_match('/^[\w\-, ]+$/', $prod_insert_name)){
				throw new Exception("invalid-name");
			}
			if (!preg_match('/^[\w\-, .\n\r]+$/', $prod_insert_description)){
				throw new Exception("invalid-description");
			}
			if (!preg_match('/[0-9]{1,10}(\.[0-9]{0,2})?$/', $prod_insert_price)){
				throw new Exception("invalid-price");
			}
			// DB manipulation
			$sql = "INSERT INTO products VALUES (null, '$prod_insert_catid', 
				'$prod_insert_name', '$prod_insert_price', '$prod_insert_description')";
			mysql_query($sql);
			$sql = "SELECT pid FROM products ORDER BY pid DESC LIMIT 1";
			$lastInsertID = mysql_query($sql);
			$imageName = mysql_fetch_assoc($lastInsertID)['pid'];
			if(strlen($imageName)!=0 && $imageName != 0){
				if(isset($_FILES["prod_insert_image"])){
					echo"yes";
			    } else{
			        echo "Error";
			    }
			}
		}
		
		function ierg4210_prod_update() {
			$prod_update_catid=$_POST[prod_update_catid];
			$prod_update_pid=$_POST[prod_update_pid];
			$prod_update_new_name=$_POST[prod_update_new_name];
			$prod_update_price=$_POST[prod_update_price];
			$prod_update_description=$_POST[prod_update_description];
			// DB manipulation
			if(strlen($prod_update_new_name)!=0){
				// input validation or sanitization
				if (!preg_match('/^[\w\-, ]+$/', $prod_update_new_name)){
					throw new Exception("invalid-name");
				}
				$sql = "UPDATE products SET 
				pname = '$prod_update_new_name'
				WHERE pid = '$prod_update_pid' 
				AND catid = '$prod_update_catid'";
	    		mysql_query($sql);
			}
			if(strlen($prod_update_price)!=0){
				// input validation or sanitization
				if (!preg_match('/[0-9]{1,10}(\.[0-9]{0,2})?$/', $prod_update_price)){
					throw new Exception("invalid-price");
				}
				$sql = "UPDATE products SET 
				price = '$prod_update_price'
				WHERE pid = '$prod_update_pid' 
				AND catid = '$prod_update_catid'";
	    		mysql_query($sql);
			}
			if(strlen($prod_update_description)!=0){
				// input validation or sanitization
				if (!preg_match('/^[\w\-, .\n\r]+$/', $prod_update_description)){
					throw new Exception("invalid-description");
				}
				$sql = "UPDATE products SET 
				description = '$prod_update_description'
				WHERE pid = '$prod_update_pid' 
				AND catid = '$prod_update_catid'";
	    		mysql_query($sql);
			}
		}
		
		function ierg4210_prod_delete() {
			$prod_delete_catid=$_POST[prod_delete_catid];
			$prod_delete_pid=$_POST[prod_delete_pid];
			$sql = "DELETE FROM products WHERE catid ='$prod_delete_catid' 
				AND pid = '$prod_delete_pid'";
    		mysql_query($sql);
		}

		switch ($_REQUEST[action]){
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
		mysql_close();
		//header('Location: admin_panel.php');
		//header( "refresh:1;url=admin_panel.php" );
	?>