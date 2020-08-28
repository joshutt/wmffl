import configparser
import unittest

import mysql

import utils


class MyTestCase(unittest.TestCase):
    def test_load_config(self):
        config = utils.load_config()
        self.assertIsNotNone(config)
        self.assertIsInstance(config, configparser.RawConfigParser)

    def test_db_conn(self):
        conn = utils.get_db_connection()
        self.assertIsNotNone(conn)
        self.assertIsInstance(conn, mysql.connector.connection.MySQLConnection)


if __name__ == '__main__':
    unittest.main()
