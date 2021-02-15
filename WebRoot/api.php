<?php
/**
 * HTTP interface for RESTful API.
 * GET param "node_id" integer (required)​
 * GET param "language" enum (required)​
 * GET param "search_keyword" string (optional)​
 * GET param "page_num" integer (optional, defaults to 0)​
 * GET param "page_size" integer (optional, defaults to 100)​
 * return JSON {"nodes":[...], "error":"..."}
 * @author Vince <vincent.boursier@gmail.com>
 */

spl_autoload_register(function($className) {
	require $className . '.php';
});

$nodeID = filter_input(INPUT_GET, 'node_id', FILTER_SANITIZE_NUMBER_INT);
$language = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_STRING);
$searchKeyword = filter_input(INPUT_GET, 'search_keyword', FILTER_SANITIZE_STRING);
$pageNum = filter_input(INPUT_GET, 'page_num', FILTER_SANITIZE_NUMBER_INT) ?? 0;
$pageSize = filter_input(INPUT_GET, 'page_size', FILTER_SANITIZE_NUMBER_INT) ?? Config::DEFAULT_PAGESIZE;

$error = null;

// Check required params
if ($nodeID == null || $language == null)  // or false
{ $error = 'Missing mandatory params'; }
elseif ($pageNum === false || $pageNum < 0)
{ $error = 'Invalid page number requested'; }
elseif ($pageSize === false || $pageSize < 0 || $pageSize > 1000)
{ $error = 'Invalid page size requested'; }

header('Content-Type: application/json');

if ($error !== null)
{
	echo '{"nodes": [], "error": "', $error, '"}';
	exit;
}

$db = new DataAccess(
	Config::DATABASE_DRIVER,
	Config::DATABASE_HOST,
	Config::DATABASE_PORT,
	Config::DATABASE_NAME,
	Config::DATABASE_USER,
	Config::DATABASE_PASSWORD);

echo '{"nodes": ', json_encode($db->getNodes($id)), '}';