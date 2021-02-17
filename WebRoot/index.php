<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Frontend</title>
	<script>
		function enableDisableElement(id)
		{
			element = document.getElementById(id);
			element.disabled = !element.disabled;
		}
	</script>
</head>
<body>
	<b>Test API params</b>
	<br><br>
	<form action="api.php" method="get">
		<label for="NodeID">node_id:</label>
		<input id="NodeID" type="text" name="node_id" value="5">
		<br>
		<label for="Language">language:</label>
		<select id="Language" name="language">
			<option>english</option>
			<option>italian</option>
		</select>
		<br>
		<label for="SearchKeyword">search_keyword:</label>
		<input id="SearchKeyword" type="text" name="search_keyword" disabled>
		<br>
		<label for="PageNum">page_num:</label>
		<input id="PageNum" type="text" name="page_num" value="0">
		<br>
		<label for="PageSize">page_size:</label>
		<input id="PageSize" type="text" name="page_size" value="100">
		<br><br>
		<input type="submit">
		<br>
		<input id="cb" type="checkbox" onchange="enableDisableElement('SearchKeyword')">
		<label for="cb">search keyword</label>
	</form>
</body>
</html>