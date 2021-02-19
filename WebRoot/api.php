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
	$pageNum = 0;  // Default
	$pageSize = WebApp\Config::PAGESIZE_DEFAULT;

	// Check required params
	if ($nodeID === null || $nodeID === false || $nodeID === '' || $language == null)
	{ throw new Exception('Missing mandatory params'); }

	if (filter_has_var(INPUT_GET, 'page_num'))
	{
		$pageNum = filter_input(INPUT_GET, 'page_num', FILTER_SANITIZE_NUMBER_INT);

		if ($pageNum === null || $pageNum === false || $pageNum === '')
		{ throw new Exception('Invalid page number requested'); }
	}

	if (filter_has_var(INPUT_GET, 'page_size'))
	{
		$pageSize = filter_input(INPUT_GET, 'page_size', FILTER_SANITIZE_NUMBER_INT);

		if ($pageSize === null || $pageSize === false || $pageSize === ''
			|| $pageSize < WebApp\Config::PAGESIZE_MIN || $pageSize > WebApp\Config::PAGESIZE_MAX)
		{
			throw new Exception('Invalid page size requested');
		}
	}

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