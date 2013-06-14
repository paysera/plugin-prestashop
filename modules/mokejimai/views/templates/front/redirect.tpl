<script type="text/javascript">
{literal}
	$(document).ready(function(){
		$('#paysera-form').submit();
	});
{/literal}
</script>

...

<form action="{$payUrl}" method="POST" id="paysera-form">
	{foreach from=$request key=name item=value}
	<input type="hidden" name="{$name}" value="{$value}" />
	{/foreach}
</form>