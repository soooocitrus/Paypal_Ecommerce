1. Sign up at https://developer.paypal.com/ and create two test accounts -- done
2. Enclose your shopping cart with a <form> element -- done
3. When the checkout button is clicked:
	3.1. Using AJAX send pid and quanity to server -- done
	3.2. Server generate a digest which is composed of 'Currency', 'Merchant email address', 'A random salt', 'item list', 'total price', '' -- done
	3.3. Server cash these data in table order -- done
	3.4. Send back the digest and the invoice -- lastInsertID() -- done
	3.5. Normal paypal form submission -- done
4. . Setup a Instant Payment Notification (IPN) page to get notified once a payment is completed -- done
5. After the buyer has finished paying with PayPal, auto redirect the buyer back to your shop -- done
6. Display the DB orders table in admin panel: product list, payment status…etc. -- done