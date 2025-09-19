<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Data_Importer
{
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function run(string $sql): bool
    {
        // don't truncate when we got an empty SQL statement
        if ($sql === '') {
            return false;
        }

        // the database driver that WordPress uses by default doesn't allow multiple SQL statements in a single call
        // PDO does, so here we manually connect to the DB using PDO
        $pdo = $this->connect_pdo();
        if (!$pdo) {
            return false;
        }

        // then, excute sql string
        $pdo->exec($sql);
        return true;
    }

    private function connect_pdo(): ?\PDO
    {
        // Parse the DB_HOST using WordPress's specific style
        // Supports IPv4, IPv6, and socket connections
        $host_data = $this->db->parse_db_host(DB_HOST);

        if (is_array($host_data)) {
            list($host, $port, $socket, $is_ipv6) = $host_data;
        } else {
            // Redacted. Throw an error or something
            return null;
        }

        // Wrap the IPv6 host in braces as required
        if ($is_ipv6 && extension_loaded('mysqlnd')) {
            $host = "[$host]";
        }

        // Generate either a socket connection string or TCP connection string
        if (isset($socket)) {
            $connection_str = 'mysql:unix_socket=' . $socket . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        } else {
            $connection_str = 'mysql:host=' . $host . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            if (isset($port)) {
                $connection_str .= ';port=' . $port;
            }
        }

        // Open the connection
        return new \PDO($connection_str, DB_USER, DB_PASSWORD, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING
        ]);
    }
}
