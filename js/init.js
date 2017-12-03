$(document).ready(function(){
    simpleCart.ready();
})

function shoppingcart_submit(e){
	function ajaxSend(){
		var xmlhttp =  new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()  {
			if (xmlhttp.readyState ==  4   &&  xmlhttp.status  ==  200)    {
				try{
					var obj = JSON.parse(xmlhttp.responseText);
					if (obj.ifLogin == 0) {
						alert("Please kindly login first.");
						window.location.href = "login.php";
					}
					else {
						simpleCart.bind( 'beforeCheckout' , function( data ){
							data.custom= obj.digest;
							data.invoice= obj.orderID;
						});
						simpleCart.checkout();
						simpleCart.empty();
					}
				}catch(e){
					//alert(e);
				}	
			}
		};

		xmlhttp.open("POST",  "checkout-process.php", true);
		//xmlhttp.setRequestHeader("Content-type",  "application/json");
		xmlhttp.setRequestHeader("Content-type",  "application/x-www-form-urlencoded");
		var items = {};
		simpleCart.each(function( item , x){
			items[item.id()] = item.quantity();
		});
		items = JSON.stringify(items);
		var message = "item_list=" + items;
		xmlhttp.send(message);
	}
	ajaxSend();
	return false;
}