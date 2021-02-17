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
	require str_replace('\\', '/', $className) . '.php';
});

header('Content-Type: application/json');

try
{
	// Sanitize input params
	$nodeID = filter_input(INPUT_GET, 'node_id', FILTER_SANITIZE_NUMBER_INT);
	$language = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_STRING);
	$searchKeyword = filter_input(INPUT_GET, 'search_keyword', FILTER_SANITIZE_STRING);
	$pageNum = filter_input(INPUT_GET, 'page_num', FILTER_SANITIZE_NUMBER_INT) ?? 0;
	$pageSize = filter_input(INPUT_GET, 'page_size', FILTER_SANITIZE_NUMBER_INT) ?? WebApp\Config::DEFAULT_PAGESIZE;

	// Check params
	if ($nodeID === null || $nodeID === false || $language == null)
	{ throw new Exception('Missing mandatory params'); }
	elseif ($pageNum === false || $pageNum < 0)
	{ throw new Exception('Invalid page number requested'); }
	elseif ($pageSize === false || $pageSize < 0 || $pageSize > 1000)
	{ throw new Exception('Invalid page size requested'); }

	$dao = WebApp\WebApp::getDataAccessObject();

	if ($nodeID == 0)
	{ $nodes = $dao->getRootNodes($language, $pageNum, $pageSize, $searchKeyword); }
	else
	{ $nodes = $dao->getChildNodes($nodeID, $language, $pageNum, $pageSize, $searchKeyword); }

	echo '{"nodes":', json_encode($nodes), '}';
}
catch (Exception $e)
{
	echo '{"nodes":[],"error":"', $e->getMessage(), '"}';
}