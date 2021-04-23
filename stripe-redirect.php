<?php
include 'config.php';
?>
<!DOCTYPE html>
<html>
	<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Stripe Redirect</title>
        <style>
        p {
            font-family: -apple-system, BlinkMacSystemFont, "segoe ui", roboto, oxygen, ubuntu, cantarell, "fira sans", "droid sans", "helvetica neue", Arial, sans-serif;
            padding: 10px;
        }
        </style>
	</head>
	<body>
        <?php if (!isset($_GET['stripe_session_id'])): ?>
        <p>No Stripe session ID specified!</p>
        <?php else: ?>
        <p>Redirecting to Stripe Checkout... Click <a href="#" class="redirect">here</a> if you're not redirected.</p>
        <script src="https://js.stripe.com/v3/"></script>
        <script>
        function redirectToStripe() {
            try {
                var stripe = Stripe('<?=stripe_publish_key?>');
                stripe.redirectToCheckout({
                    sessionId: '<?=$_GET['stripe_session_id']?>'
                }).then(function(result) {

                });
            } catch(error) {
                document.querySelector("p").innerHTML = error;
            }
        }
        document.querySelector(".redirect").onclick = function(e) {
            e.preventDefault();
            redirectToStripe();
        };
        redirectToStripe();
        </script>
        <?php endif; ?>
    </body>
</html>
