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
	        	echo "here2";
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
	                	echo "here3";
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
	$db = "IERG4210";
	$host = "localhost";
	$username = "sooocitrus";
	$password = "123456";
	$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);
    $connection -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
    $connection -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
    $connection -> setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

	$stmt= $connection -> prepare("SELECT catid FROM categories;");
	$stmt->execute();
	$catid_arr = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

	$stmt= $connection -> prepare("SELECT catname FROM categories;");
	$stmt->execute();
	$catname_arr = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

	$pid_arr = array();
	$pname_arr = array();
	for($i = 0, $size = count($catid_arr); $i < $size; $i++){
		$catid = $catid_arr[$i];
		$stmt= $connection -> prepare("SELECT pid, pname FROM products WHERE catid = :catid;");
		$stmt->bindParam(':catid', $catid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_BOTH);
		$pid_arr[$catid] = array();
		$pname_arr[$catid] = array();
		for($j = 0, $count = count($result); $j < $count; $j++){
			array_push($pid_arr[$catid], $result[$j][0]);
			array_push($pname_arr[$catid], $result[$j][1]);
		}
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>IERG4210 demo website</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="THIS IS A DEMO WEBSITE FOR CUHK IERG4210 WEB PROGRAMMING AND SECURITY, THIS IS AN
		    SHOPPING WEBSITE. THIS IS THE MAIN PAGE." />
		<meta name="keywords" content="IERG4210, SECURITY, WEB, SHOPPING, MAIN" />
		<link rel="stylesheet" type="text/css" href="css/admin.css">
		<script src="js/expandSelect.js"></script>
	</head>
	<body>
		<div id="user_information">
			Hi <?php print htmlspecialchars(loggedin(), ENT_COMPAT, 'ISO-8859-1', true) ?>
			<form method="POST" action="auth-process.php?action=<?php echo ($action = 'logout'); ?>" enctype="multipart/form-data">
				<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
				<input type="submit" value="Logout"/>
			</form>
		</div>
		<h1>Administration Panel</h1>
		<div id="operation_container">
			Please select what you want to do
			<select id="operation" name="operation" onchange="onChange()">
				<option value="default"></option>
				<option value="cat_insert">Insert Category</option>
				<option value="cat_update">Edit Category</option>
				<option value="cat_delete">Delete Category</option>
				<option value="prod_insert">Insert Product</option>
				<option value="prod_update">Edit Product</option>
				<option value="prod_delete">Delete Product</option>
			</select>
		</div>
		<div class="select_value">
			<fieldset id="cat_insert" class="showfield">
				<legend>New Category</legend>
				<form method="POST" action="admin_process.php?action=<?php echo ($action1 = 'cat_insert'); ?>" enctype="multipart/form-data">
					<label for="cat_insert_name">Name *</label>
					<div><input id="cat_insert_name" type="text" name="cat_insert_name" required="true" pattern="^[\w\- ]+$"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action1); ?>" />
					<input type="submit" value="Submit"/>
				</form>
			</fieldset>

			<fieldset id="cat_update" class="showfield">
				<legend>Update Category</legend>
				<form method="POST" action="admin_process.php?action=<?php echo ($action2 = 'cat_update'); ?>" enctype="multipart/form-data">
					<label for="cat_update_old_name">Old Name *</label>
					<select id="cat_update_catid" name="cat_update_catid">
						<option value="default"></option>
						<?php
			                for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			   				    $catid = $catid_arr[$i];
			   				    $catname = $catname_arr[$i];
			   				    print '<option value="'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
			   				}
						?>
					</select>
					<label for="cat_update_new_name">New Name *</label>
					<div><input id="cat_update_new_name" type="text" name="cat_update_new_name" required="true" pattern="^[\w\- ]+$"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action2); ?>" />
					<input type="submit" value="Update" />
				</form>
			</fieldset>

			<fieldset id="cat_delete" class="showfield">
				<legend>Delete Category</legend>
				<form method="POST" action="admin_process.php?action=<?php echo ($action3 = 'cat_delete'); ?>" enctype="multipart/form-data">
					<label for="cat_delete_name">Name *</label>
					<select id="cat_delete_catid" name="cat_delete_catid">
						<option value="default"></option>
						<?php
			        	    for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			   				    $catid = $catid_arr[$i];
			   				    $catname = $catname_arr[$i];
			   				    print '<option value="'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
			   				}
						?>
					</select>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action3); ?>" />
					<input type="submit" value="Delete"/>
				</form>
			</fieldset>

			<fieldset id="prod_insert" class="showfield">
				<legend>New Product</legend>
				<form method="POST" action="admin_process.php?action=<?php echo ($action4 = 'prod_insert'); ?>" enctype="multipart/form-data">
					<label for="prod_insert_catid">Category *</label>
					<div>
						<select id="prod_insert_catid" name="prod_insert_catid">
							<option value="default"></option>
							<?php
			        		    for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			   				    $catid = $catid_arr[$i];
			   				    $catname = $catname_arr[$i];
			   				    print '<option value="'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
			   					}
							?>
						</select>
					</div>
					<label for="prod_insert_name">Name *</label>
					<div><input id="prod_insert_name" type="text" name="prod_insert_name" required="true" pattern="^[\w\- ]+$"/>
					</div>
					<label for="prod_insert_price">Price *</label>
					<div><input id="prod_insert_price" type="text" name="prod_insert_price" required="true" pattern="^[0-9]{1,10}(\.[0-9]{0,2})?$"/>
					</div>
					<label for="prod_insert_description">Description *</label>
					<div><textarea id="prod_insert_description" type="text" name="prod_insert_description" required="true" value="" pattern="^[\w\-, .\n\r]+$"></textarea>
					</div>
					<label for="prod_insert_image">Image *</label>
					<input id="prod_insert_image" type="file" name="prod_insert_image" required="true" accept="image/jpeg"/>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action4); ?>" />
					<input type="submit" value="Submit"/>
				</form>
			</fieldset>

			<fieldset id="prod_update" class="showfield">
				<legend>Update Product</legend>
				<form method="POST" action="admin_process.php?action=<?php echo ($action5 = 'prod_update'); ?>" enctype="multipart/form-data">
					<label for="prod_update_catid">Category *</label>
					<select id="prod_update_catid" name="prod_update_catid" onchange="prodUpdateSelectorOnChange()">
						<option value="default"></option>
						<?php
			        	    for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			   				    $catid = $catid_arr[$i];
			   				    $catname = $catname_arr[$i];
			   				    print '<option value="'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
			   				}
						?>
					</select>
					<?php
						for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
							$catid = $catid_arr[$i];
							print '<div class="prod_update_old_name" id="prod_update_old_name_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">';
							print '<label for="prod_update_old_name_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">Old Name *</label>';
							print '<select id="prod_update_pid_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'" 
								class="prod_update_name_from_cat"
								name="prod_update_pid_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">';
							print '<option value="default"></option>';
							for($j = 0, $count = count($pid_arr[$catid]); $j < $count; $j++){
								$pid=$pid_arr[$catid][$j];
								$pname=$pname_arr[$catid][$j];
								print '<option value="'.htmlspecialchars($pid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($pname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
							}
							print '</select></div>';
						}
					?>
					<label for="prod_update_new_name">New Name</label>
					<div><input id="prod_update_new_name" type="text" name="prod_update_new_name" pattern="^[\w\- ]+$"/></div>
					<label for="prod_update_price">Price</label>
					<div><input id="prod_update_price" type="text" name="prod_update_price" pattern="^[0-9]{1,10}(\.[0-9]{0,2})?$"/>
					</div>
					<label for="prod_update_description">Description</label>
					<div><textarea id="prod_update_description" type="text" name="prod_update_description" value="" pattern="^[\w\-, .\n\r]+$"></textarea>
					</div>
					<label for="prod_update_image">Image</label>
					<div><input id="prod_update_image" type="file" name="prod_update_image" accept="image/jpeg"/>
					</div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action5); ?>" />
					<input type="submit" value="Update"/>
				</form>
			</fieldset>

			<fieldset id="prod_delete" class="showfield">
				<legend>Delete Product</legend>
				<form method="POST" action="admin_process.php?action=<?php echo ($action6 = 'prod_delete'); ?>" enctype="multipart/form-data">
					<label for="prod_delete_catid">Category *</label>
					<select id="prod_delete_catid" name="prod_delete_catid" onchange="prodDeleteSelectorOnChange()">
						<option value="default"></option>
						<?php
			        	    for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			   				    $catid = $catid_arr[$i];
			   				    $catname = $catname_arr[$i];
			   				    print '<option value="'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
			   				}
						?>
					</select>
					<?php
				    	for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
							$catid = $catid_arr[$i];
							print '<div class="prod_delete_old_name" id="prod_delete_old_name_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">';
							print '<label for="prod_delete_old_name_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">Old Name *</label>';
							print '<select id="prod_delete_old_name_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'" 
								class="prod_delete_name_from_cat"
								name="prod_delete_pid_'.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">';
							print '<option value="default"></option>';
							for($j = 0, $count = count($pid_arr[$catid]); $j < $count; $j++){
								$pid=$pid_arr[$catid][$j];
								$pname=$pname_arr[$catid][$j];
								print '<option value="'.htmlspecialchars($pid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($pname, ENT_COMPAT, 'ISO-8859-1', true).'</option>';
							}
							print '</select></div>';
						}
					?>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action6); ?>" />
					<input type="submit" value="Delete"/>
				</form>
			</fieldset>
		</div>
		<table id="order table">
			<legend>Lastest 20 Transaction Records</legend>
			<?php
				$stmt= $connection -> prepare("SELECT * FROM orders ORDER BY orderid DESC LIMIT 20");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_BOTH);
				$orderid_arr = array();
				$email_arr = array();
				$digest_arr = array();
				$salt_arr = array();
				$createdtime_arr = array();
				$txn_id_arr = array();
				$status_arr = array();
				for($j = 0, $count = count($result); $j < $count; $j++){
					array_push($orderid_arr, $result[$j][0]);
					array_push($email_arr, $result[$j][1]);
					array_push($digest_arr, $result[$j][2]);
					array_push($salt_arr, $result[$j][3]);
					array_push($createdtime_arr, $result[$j][4]);
					array_push($txn_id_arr, $result[$j][5]);
					array_push($status_arr, $result[$j][6]);
				}
				$orderid_threshold = $orderid_arr[count($orderid_arr)-1];
				print '<tr><th>Order ID</th><th>User Account</th><th>Digest</th><th>Salt</th><th>CheckOut Time</th><th>Transaction ID</th><th>Status</th></tr>';
				for ($i = 0, $size = count($orderid_arr); $i < $size; $i++) {
					print '<tr>'.'<td>'.$orderid_arr[$i].'</td>'.'<td>'.$email_arr[$i].'</td>'.'<td>'.$digest_arr[$i].'</td>'.'<td>'.$salt_arr[$i].'</td>'.'<td>'.$createdtime_arr[$i].'</td>'.'<td>'.$txn_id_arr[$i].'</td>'.'<td>'.$status_arr[$i].'</td>'.'</tr>';
				}
			?>
		</table>
		<table>
			<legend>Purchased List Of Lastest 20 Transaction Records</legend>
			<?php
				$stmt= $connection -> prepare("SELECT * FROM purchased where orderid >= :orderid_threshold ORDER BY orderid DESC");
				$stmt->bindParam(':orderid_threshold', $orderid_threshold, PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_BOTH);
				$orderid_arr = array();
				$pid_arr = array();
				$quantity_arr = array();
				$price_arr = array();
				$txn_id_arr = array();
				for($j = 0, $count = count($result); $j < $count; $j++){
					array_push($orderid_arr, $result[$j][0]);
					array_push($pid_arr, $result[$j][1]);
					array_push($quantity_arr, $result[$j][2]);
					array_push($price_arr, $result[$j][3]);
					array_push($txn_id_arr, $result[$j][4]);
				}
				print '<tr><th>Order ID</th><th>Product ID</th><th>Quantity</th><th>Price</th><th>Transaction ID</th></tr>';
				for ($i = 0, $size = count($orderid_arr); $i < $size; $i++) {
					print '<tr>'.'<td>'.$orderid_arr[$i].'</td>'.'<td>'.$pid_arr[$i].'</td>'.'<td>'.$quantity_arr[$i].'</td>'.'<td>'.$price_arr[$i].'</td>'.'<td>'.$txn_id_arr[$i].'</td>'.'<tr>';
				}
			?>
		</table>
	</body>
</html>
<?php
	$stmt = null;
	$connection = null;
?>