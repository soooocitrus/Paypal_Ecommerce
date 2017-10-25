<!DOCTYPE HTML>
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
		<link rel="stylesheet" type="text/css" href="css/admin.css">
		<script src="js/expandSelect.js"></script>
	</head>
	<body>
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
				<form method="POST" action="admin_process.php?action=cat_insert" enctype="multipart/form-data">
					<label for="cat_insert_name">Name *</label>
					<div><input id="cat_insert_name" type="text" name="cat_insert_name" required="true" pattern="^[\w\- ]+$"/></div>
					<input type="submit" value="Submit"/>
				</form>
			</fieldset>
			<fieldset id="cat_update" class="showfield">
				<legend>Update Category</legend>
				<form method="POST" action="admin_process.php?action=cat_update" enctype="multipart/form-data">
					<label for="cat_update_old_name">Old Name *</label>
					<select id="cat_update_old_name" name="cat_update_old_name">
						<option value="default"></option>
						<?php
			        	    $query="SELECT * FROM categories;";
			                $result=mysql_query($query);
			                $num=mysql_numrows($result);
			                $i=0;
			  				while ($i < $num) {
			   				    $catid = mysql_result($result,$i,"catid");
			   				    $catname = mysql_result($result,$i,"catname");
			   				    print '<option value="'.$catid.'">'.$catname.'</option>';
			   				    $i++;
			   				}
						?>
					</select>
					<label for="cat_update_new_name">New Name *</label>
					<div><input id="cat_update_new_name" type="text" name="cat_update_new_name" required="true" pattern="^[\w\- ]+$"/></div>
					<input type="submit" value="Update" />
				</form>
			</fieldset>
			<fieldset id="cat_delete" class="showfield">
				<legend>Delete Category</legend>
				<form method="POST" action="admin_process.php?action=cat_delete" enctype="multipart/form-data">
					<label for="cat_delete_name">Name *</label>
					<select id="cat_delete_name" name="cat_delete_catid">
						<option value="default"></option>
						<?php
			        	    $query="SELECT * FROM categories;";
			                $result=mysql_query($query);
			                $num=mysql_numrows($result);
			                $i=0;
			  				while ($i < $num) {
			   				    $catid = mysql_result($result,$i,"catid");
			   				    $catname = mysql_result($result,$i,"catname");
			   				    print '<option value="'.$catid.'">'.$catname.'</option>';
			   				    $i++;
			   				}
						?>
					</select>
					<input type="submit" value="Delete"/>
				</form>
			</fieldset>
			<fieldset id="prod_insert" class="showfield">
				<legend>New Product</legend>
				<form method="POST" action="admin_process.php?action=prod_insert" enctype="multipart/form-data">
					<label for="prod_insert_catid">Category *</label>
					<div>
						<select id="prod_insert_catid" name="prod_insert_catid">
							<option value="default"></option>
							<?php
			        		    $query="SELECT * FROM categories;";
			                    $result=mysql_query($query);
			                    $num=mysql_numrows($result);
			                    $i=0;
			    				while ($i < $num) {
			    				    $catid = mysql_result($result,$i,"catid");
			    				    $catname = mysql_result($result,$i,"catname");
			    				    print '<option value="'.$catid.'">'.$catname.'</option>';
			    				    $i++;
			    				}
							?>
						</select>
					</div>
					<label for="prod_insert_name">Name *</label>
					<div><input id="prod_insert_name" type="text" name="prod_insert_name" required="true" pattern="^[\w\- ]+$"/>
					</div>
					<label for="prod_insert_price">Price *</label>
					<div><input id="prod_insert_price" type="text" name="prod_insert_price" required="true" pattern="^[\d\.]+$"/>
					</div>
					<label for="prod_insert_description">Description</label>
					<div><textarea id="prod_insert_description" type="text" name="prod_insert_description" value="" pattern="^[\d\.]+$">
					</textarea>
					</div>
					<label for="prod_insert_image">Image *</label>
					<input id="prod_insert_image" type="file" name="prod_insert_image" required="true" accept="image/jpeg"/>
					<input type="submit" value="Submit"/>
				</form>
			</fieldset>
			<fieldset id="prod_update" class="showfield">
				<legend>Update Product</legend>
				<form method="POST" action="admin_process.php?action=prod_update" enctype="multipart/form-data">
					<label for="prod_update_catid">Category *</label>
					<select id="prod_update_catid" name="prod_update_catid" onchange="prodUpdateSelectorOnChange()">
						<option value="default"></option>
						<?php
			        	    $query="SELECT * FROM categories;";
			                $result=mysql_query($query);
			                $num=mysql_numrows($result);
			                $i=0;
			   				while ($i < $num) {
		    				    $catid = mysql_result($result,$i,"catid");
		    				    $catname = mysql_result($result,$i,"catname");
		    				    print '<option value="'.$catid.'">'.$catname.'</option>';
		    				    $i++;
		    				}
						?>
					</select>
					<?php
				    	$query1="SELECT * FROM categories;";
				        $result1=mysql_query($query1);
						$num1=mysql_numrows($result1);
						$i=0;
						while ($i < $num1) {
	    				    $catid = mysql_result($result1,$i,"catid");
	    				    print '<div class="prod_update_old_name" id="prod_update_old_name_'.$catid.'">';
	    				    print '<label for="prod_update_old_name_'.$catid.'">Old Name *</label>';
							print '<select id="prod_update_old_name_'.$catid.'" 
								class="prod_update_name_from_cat"
								name="prod_update_pid">';
							print '<option value="default"></option>';
	    				    $query2 = "SELECT * FROM products where catid = '$catid';";
	    				    $result2=mysql_query($query2);
	    				    $num2=mysql_numrows($result2);
	    				    $j=0;
	    				    while($j < $num2){
	    				    	$pid = mysql_result($result2,$j,"pid");
	    				    	$pname = mysql_result($result2,$j,"pname");
	    				    	print '<option value="'.$pid.'">'.$pname.'</option>';
	    				    	$j++;
	    				    }
	    				    print '</select></div>';
	    				    $i++;
	    				}
					?>
					<label for="prod_update_new_name">New Name</label>
					<div><input id="prod_update_new_name" type="text" name="prod_update_new_name" pattern="^[\w\- ]+$"/></div>
					<label for="prod_update_price">Price</label>
					<div><input id="prod_update_price" type="text" name="prod_update_price" pattern="^[\d\.]+$"/>
					</div>
					<label for="prod_update_description">Description</label>
					<div><textarea id="prod_update_description" type="text" name="prod_update_description" value="" pattern="^[\d\.]+$">
					</textarea>
					</div>
					<label for="prod_update_image">Image</label>
					<div><input id="prod_update_image" type="file" name="prod_update_image" accept="image/jpeg"/>
					</div>
					<input type="submit" value="Update"/>
				</form>
			</fieldset>
			<fieldset id="prod_delete" class="showfield">
				<legend>Delete Product</legend>
				<form method="POST" action="admin_process.php?action=prod_delete" enctype="multipart/form-data">
					<label for="prod_delete_catid">Category *</label>
					<select id="prod_delete_catid" name="prod_delete_catid" onchange="prodDeleteSelectorOnChange()">
						<option value="default"></option>
						<?php
			        	    $query="SELECT * FROM categories;";
			                $result=mysql_query($query);
			                $num=mysql_numrows($result);
			                $i=0;
			   				while ($i < $num) {
		    				    $catid = mysql_result($result,$i,"catid");
		    				    $catname = mysql_result($result,$i,"catname");
		    				    print '<option value="'.$catid.'">'.$catname.'</option>';
		    				    $i++;
		    				}
						?>
					</select>
					<?php
				    	$query1="SELECT * FROM categories;";
				        $result1=mysql_query($query1);
						$num1=mysql_numrows($result1);
						$i=0;
						while ($i < $num1) {
	    				    $catid = mysql_result($result1,$i,"catid");
	    				    print '<div class="prod_delete_old_name" id="prod_delete_old_name_'.$catid.'">';
	    				    print '<label for="prod_delete_old_name_'.$catid.'">Name *</label>';
							print '<select id="prod_delete_old_name_'.$catid.'" 
								class="prod_delete_name_from_cat"
								name="prod_delete_pid">';
							print '<option value="default"></option>';
	    				    $query2 = "SELECT * FROM products where catid = '$catid';";
	    				    $result2=mysql_query($query2);
	    				    $num2=mysql_numrows($result2);
	    				    $j=0;
	    				    while($j < $num2){
	    				    	$pid = mysql_result($result2,$j,"pid");
	    				    	$pname = mysql_result($result2,$j,"pname");
	    				    	print '<option value="'.$pid.'">'.$pname.'</option>';
	    				    	$j++;
	    				    }
	    				    print '</select></div>';
	    				    $i++;
	    				}
					?>
					<input type="submit" value="Delete"/>
				</form>
			</fieldset>
		</div>
	</body>
    <?php
        mysql_close();
    ?>
</html>