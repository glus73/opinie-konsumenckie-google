<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
<script type="text/javascript">
	window.renderOptIn = function() {
		window.gapi.load('surveyoptin', function() {
			window.gapi.surveyoptin.render(
			{
				"merchant_id": {$merchant_id},
				"order_id": "{$order_id}",
				"email": "{$customer_email}",
				"delivery_country": "{$country}",
				"estimated_delivery_date": "{$estimated_delivery_date}",
				"products":[{$gtin nofilter}],
				"opt_in_style": "{$gdzie_plakietka}"
			});
		});
	}
</script>
<script type="text/javascript">
window.___gcfg = {
lang: '{$country}'
};
</script>

