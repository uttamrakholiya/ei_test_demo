<script type="text/javascript">
function validate()
{
var pattern=/\s/;
if(document.getElementById("txt").value.match(pattern))
{
alert("Whitespaces are not allowed");
return false;
}
}
</script>
<form action="" method="post" onsubmit="return validate()">
<input type="text" id="txt" name="txt">
<input type="submit" value="submit">
</form>