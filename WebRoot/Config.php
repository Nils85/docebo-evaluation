<?php

/**
 * Web App configuration.
 */
class Config
{
	const

	/**
	 * PDO driver name.
	 * @var string mysql or sqlite (other driver like pgsql, sqlsrv or oci can works but wasn't tested)
	 */
	DATABASE_DRIVER = 'sqlite',

	/**
	 * Server hosting the database system.
	 * To use socket path with MySQL add "unix_socket=..."
	 * @var string IP address or machine name
	 */
	DATABASE_HOST = 'localhost',

	/**
	 * Port number to connect to the database system.
	 * @var int
	 */
	DATABASE_PORT = 3306,

	/**
	 * Name of the database where the tables are stored.
	 * @var string
	 */
	DATABASE_NAME = 'Docebo',

	/**
	 * User name and password to connect to the database system.
	 * @var string
	 */
	DATABASE_USER = 'root',
	DATABASE_PASSWORD = '',

	/**
	 * Default page size if not provided.
	 * @var int
	 */
	DEFAULT_PAGESIZE = 100;
}