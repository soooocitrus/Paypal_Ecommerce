i<!DOCTYPE HTML>
<html>
    <?php
    
        define('DBSERVER',"localhost");
        define('DBUSER',"sooocitrus");
        define('DBPASS',"123456");
        define('DATABASE',"IERG4210");
        
        if (!$connection = @ mysql_connect(DBSERVER, DBUSER, DBPASS))
          die("Cannot connect");
        
        @mysql_select_db(DATABASE) or die( "Unable to select database");
    ?>
	<head>
		<title>IERG4210 demo website</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="THIS IS A DEMO WEBSITE FOR CUHK IERG4210 WEB PROGRAMMING AND SECURITY, THIS IS AN
		    SHOPPING WEBSITE. THIS IS THE MAIN PAGE." />
		<meta name="keywords" content="IERG4210, SECURITY, WEB, SHOPPING, MAIN" />
		<link rel="stylesheet" type="text/css" href="../css/style.css">
		<link rel="stylesheet" type="text/css" href="../css/header.css">
		<link rel="stylesheet" type="text/css" href="../css/leftbar.css">
		<link rel="stylesheet" type="text/css" href="../css/shoppingcart.css">
		<!--script src="https://rawgit.com/wojodesign/simplecart-js/master/simpleCart.js"></script-->
		<!--This simpleCart was the same as "https://rawgit.com/soooocitrus/simplecart-js/master/simpleCart.js", which was forked from repository "https://github.com/wojodesign/simplecart-js.git", for easy management, I copy the code here. If necessary, you could check the forked repository under the same account "soooocitrus".-->
		<script src="../js/simpleCart.js"></script>
		<script src="https://rawgit.com/wojodesign/simplecart-js/master/test/inc/jquery.1.6.1.min.js"></script>
		<script type="text/javascript" src="../js/init.js"></script>
	</head>
	<body class="main_page">
		<div id="no_move_header">
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
        		    $query="SELECT * FROM categories;";
                    $result=mysql_query($query);
                    $num=mysql_numrows($result);
                    $i=0;
    				while ($i < $num) {
    				    $catid = mysql_result($result,$i,"catid");
    				    $catname = mysql_result($result,$i,"catname");
    				    print '<li><a href="main.php?catid='.$catid.'">'.$catname.'</a></li>';
    				    $i++;
    				}
                ?>
            </ul>
		</div>
		<div id="page_content">
			<div class="history_hyperlink_container">
				<a class="history_hyperlink" href="main.php">Home</a>
				<?php
					$catid = $_REQUEST['catid'];
				    $pid = $_REQUEST['pid'];
			        if(!strlen($pid) == 0){
			            $query ="SELECT pname FROM products where pid = ".$pid.";";
			            $result  = mysql_query($query);
                        $query_row1 = mysql_fetch_array($result);
                        $pname = $query_row1[pname];
                        $query ="SELECT catname FROM categories where catid = ".$catid.";";
			            $result  = mysql_query($query);
                        $query_row2 = mysql_fetch_array($result);
                        $catname = $query_row2[catname];
                        print '<a class="history_hyperlink" href="main.php?catid='.$catid.'"> > '.$catname.'</a>';
			            print '<a class="history_hyperlink" href="product.php?catid='.$catid.'&pid='.$pid.'"> > '.$pname.'</a>';
			        }
				?>
			</div>
			<?php
			    $pid = $_REQUEST['pid'];
			    $query='SELECT * FROM products where pid ='.$pid.' ;';
                $result=mysql_query($query);
                $query_row=mysql_fetch_array($result);
    			$pname = $query_row[pname];
    			$price = $query_row[price];
    			$description = $query_row[description];
                print '<div class="product_picture_container">';
                print '<div class="image_wrapper">';
                print '<img class="center" src="../image/'.$pid.'.jpeg"></img>';
                print '</div></div>';
                print '<div class="product_text_container">';
                print '<div class="simpleCart_shelfItem">';
                print '<p class="item_name">'.$pname.'</p>';
                print '<p class="item_price">$'.$price.'</p>';
                print '<input type="number" class="item_quantity" value="1" min="1" />';
                print '<input type="button" class="item_add" value="ADD" />';
                print '<p class="product_description">'.$description.'</p>';
                print '</div></div>';
            ?>
		</div>
		<div id="no_move_footer">
			This is the no move footer.
		</div>
	</body>
    <?php
        mysql_close();
    ?>
</html>