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
	}
?>
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
		$stmt= $connection -> prepare("SELECT pid, pname, price FROM products WHERE catid = :catid;");
		$stmt->bindParam(':catid', $catid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_BOTH);
		$pid_arr[$catid] = array();
		$pname_arr[$catid] = array();
		$price_arr[$catid] = array();
		for($j = 0, $count = count($result); $j < $count; $j++){
			array_push($pid_arr[$catid], $result[$j][0]);
			array_push($pname_arr[$catid], $result[$j][1]);
			array_push($price_arr[$catid], $result[$j][2]);
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
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="stylesheet" type="text/css" href="css/header.css">
		<link rel="stylesheet" type="text/css" href="css/leftbar.css">
		<!--link rel="stylesheet" type="text/css" href="css/shoppingcart.css"-->
		<!--script src="https://rawgit.com/wojodesign/simplecart-js/master/simpleCart.js"></script-->
		<!--This simpleCart was the same as "https://rawgit.com/soooocitrus/simplecart-js/master/simpleCart.js", which was forked from repository "https://github.com/wojodesign/simplecart-js.git", for easy management, I copy the code here. If necessary, you could check the forked repository under the same account "soooocitrus".-->
		<script src="js/simpleCart.js"></script>
		<script src="https://rawgit.com/wojodesign/simplecart-js/master/test/inc/jquery.1.6.1.min.js"></script>
		<script type="text/javascript" src="js/init.js"></script>
	</head>
	<body class="main_page">
		<div id="no_move_header">
			<div id="user_information">
				Hi <?php print htmlspecialchars(loggedin(), ENT_COMPAT, 'ISO-8859-1', true) ?>
				<form method="POST" action="auth-process.php?action=<?php echo ($action = 'logout'); ?>" enctype="multipart/form-data">
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
					<input type="submit" value="Logout"/>
				</form>
			</div>
			<ul id="shoppingcart">
				<li>Total: <span class="simpleCart_total"></span> (<span id="simpleCart_quantity" class="simpleCart_quantity"></span> items)
					<ul>
						<li  id="shoppingcart_empty"><a href="javascript:;" class="simpleCart_empty">empty cart</a></li>
						<li id="shoppingcart_checkout"><a href="javascript:;" class="simpleCart_checkout">checkout</a></li>
						<li>
							<div id="shoppingcart_list" class="simpleCart_items" ></div>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<div id="no_move_left_bar">
		    <ul id="category">
    		    <?php
    		    	for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			   			$catid = $catid_arr[$i];
			   			$catname = $catname_arr[$i];
			   			print '<li><a href="main.php?catid='.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'">'.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</a></li>';
			   		}
                ?>
            </ul>
		</div>
		<div id="page_content">
			<div class="history_hyperlink_container">
				<a class="history_hyperlink" href="main.php">Home</a>
				<?php
				    $catid = $_REQUEST['catid'];
			        if(!strlen($catid) == 0){
			        	for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
			        		if($catid == $catid_arr[$i]){
			        			$catname = $catname_arr[$i];
			        		}
			        	}
			            print '<a class="history_hyperlink" href="main.php?catid='.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'"> > '.htmlspecialchars($catname, ENT_COMPAT, 'ISO-8859-1', true).'</a>';
			        }
				?>
			</div>
			<div class="product_thumbnail_container">
			     <?php
			        $catid = $_REQUEST['catid'];
			        if(strlen($catid) == 0){
			            for ($i = 0, $size = count($catid_arr); $i < $size; $i++) {
							$catid = $catid_arr[$i];
							for($j = 0, $count = count($pid_arr[$catid]); $j < $count; $j++){
				        		$pid = $pid_arr[$catid][$j];
				        		$pname = $pname_arr[$catid][$j];
				        		$price = $price_arr[$catid][$j];
				        		print '<div class="simpleCart_shelfItem">';
		    				    print '<div class="product_thumbnail_image_container">';
		    				    print '<a href="product.php?catid='.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'&pid='.htmlspecialchars($pid, ENT_COMPAT, 'ISO-8859-1', true).'">';
		    				    print '<img class="center", src="image/'.htmlspecialchars($pid, ENT_COMPAT, 'ISO-8859-1', true).'.jpeg"></img>';
		    				    print '<p class="item_name">'.htmlspecialchars($pname, ENT_COMPAT, 'ISO-8859-1', true).'</p>';
		    				    print '</a></div>';
		    				    print '<div class="product_thumbnail_txt_container">';
		    				    print '<p class="item_price">$'.htmlspecialchars($price, ENT_COMPAT, 'ISO-8859-1', true).'</p>';
		    				    print '<input type="number" class="item_quantity" value="1" min="1" />';
		    				    print '<input type="button" class="item_add" value="ADD" />';
		    				    print '</div></div>';
				        	}
						}
			        }else{
			        	for($j = 0, $count = count($pid_arr[$catid]); $j < $count; $j++){
			        		$pid = $pid_arr[$catid][$j];
			        		$pname = $pname_arr[$catid][$j];
			        		$price = $price_arr[$catid][$j];
			        		print '<div class="simpleCart_shelfItem">';
	    				    print '<div class="product_thumbnail_image_container">';
	    				    print '<a href="product.php?catid='.htmlspecialchars($catid, ENT_COMPAT, 'ISO-8859-1', true).'&pid='.htmlspecialchars($pid, ENT_COMPAT, 'ISO-8859-1', true).'">';
	    				    print '<img class="center", src="image/'.htmlspecialchars($pid, ENT_COMPAT, 'ISO-8859-1', true).'.jpeg"></img>';
	    				    print '<p class="item_name">'.htmlspecialchars($pname, ENT_COMPAT, 'ISO-8859-1', true).'</p>';
	    				    print '</a></div>';
	    				    print '<div class="product_thumbnail_txt_container">';
	    				    print '<p class="item_price">$'.htmlspecialchars($price, ENT_COMPAT, 'ISO-8859-1', true).'</p>';
	    				    print '<input type="number" class="item_quantity" value="1" min="1" />';
	    				    print '<input type="button" class="item_add" value="ADD" />';
	    				    print '</div></div>';
			        	}
			        }
                ?>
			</div>
		</div>
		<footer id="no_move_footer">
			This is the no move footer.
		</footer>
	</body>
</html>
<?php
	$stmt = null;
	$connection = null;
?>