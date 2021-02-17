<?php
namespace WebApp;
use DataAccess\DataAccess;

/**
 * WebApp common functions.
 * @author Vince <vincent.boursier@gmail.com>
 */
class WebApp
{
	/**
	 * Get the Data Access Object.
	 * @return WebApp\DataAccess
	 */
	static function getDataAccessObject()
	{
		return new DataAccess(
			Config::DATABASE_DRIVER,
			Config::DATABASE_NAME,
			Config::DATABASE_HOST,
			Config::DATABASE_USER,
			Config::DATABASE_PASSWORD,
			Config::DATABASE_PORT);
	}
}