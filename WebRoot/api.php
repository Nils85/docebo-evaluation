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
	$inputs = filter_input_array(INPUT_GET,
		[
			'node_id' => FILTER_VALIDATE_INT,
			'language' => FILTER_SANITIZE_STRING,
			'search_keyword' => FILTER_SANITIZE_STRING,
			'page_num' => FILTER_VALIDATE_INT,
			'page_size' => FILTER_VALIDATE_INT
		]);

	// Check required params
	if ($inputs['node_id'] === null || $inputs['node_id'] === false || $inputs['language'] == null)
	{ throw new Exception('Missing mandatory params'); }

	if ($inputs['page_num'] === null)
	{ $inputs['page_num'] = 0; }  // Default
	elseif ($inputs['page_num'] === false || $inputs['page_num'] < 0)
	{ throw new Exception('Invalid page number requested'); }

	if ($inputs['page_size'] === null)
	{
		$inputs['page_size'] = WebApp\Config::PAGESIZE_DEFAULT;
	}
	elseif ($inputs['page_size'] === false
		|| $inputs['page_size'] < WebApp\Config::PAGESIZE_MIN
		|| $inputs['page_size'] > WebApp\Config::PAGESIZE_MAX)
	{
		throw new Exception('Invalid page size requested');
	}

	$dao = WebApp\WebApp::getDataAccessObject();

	if ($inputs['node_id'] == 0)
	{
		$nodes = $dao->getRootNodes(
			$inputs['language'],
			$inputs['page_num'],
			$inputs['page_size'],
			$inputs['search_keyword']);
	}
	else
	{
		$nodes = $dao->getChildNodes(
			$inputs['node_id'],
			$inputs['language'],
			$inputs['page_num'],
			$inputs['page_size'],
			$inputs['search_keyword']);
	}

	echo '{"nodes":', json_encode($nodes), '}';
}
catch (Exception $e)
{
	echo '{"nodes":[],"error":"', $e->getMessage(), '"}';
}