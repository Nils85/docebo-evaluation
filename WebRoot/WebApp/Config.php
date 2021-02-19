<?php
namespace WebApp;

/**
 * WebApp configuration.
 */
class Config
{
	const

	/**
	 * PDO driver name.
	 * @var string mysql, sqlite, pgsql, sqlsrv, or oci (Oracle)
	 */
	DATABASE_DRIVER = 'sqlite',

	/**
	 * Server hosting the database system.
	 * "unix_socket=..." to use socket path with MySQL
	 * @var string IP address or machine name
	 */
	DATABASE_HOST = 'localhost',

	/**
	 * Port number to connect to the database system.
	 * @var int Number between 1 and 65535 or 0 to use the default port
	 */
	DATABASE_PORT = 0,

	/**
	 * Name of the database where the tables are stored.
	 * @var string Database name (or file path with SQLite)
	 */
	DATABASE_NAME = 'Docebo.db',

	/**
	 * User name and password to connect to the database system.
	 * @var string
	 */
	DATABASE_USER = 'root',
	DATABASE_PASSWORD = '',

	/**
	 * Default page size if not provided.
	 * @var int Number between min and max
	 */
	PAGESIZE_DEFAULT = 100,

	/**
	 * Minimum page size allowed.
	 * @var int Number
	 */
	PAGESIZE_MIN = 0,

	/**
	 * Maximum page size allowed.
	 * @var int Number
	 */
	PAGESIZE_MAX = 1000;
}