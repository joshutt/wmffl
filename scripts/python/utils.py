import configparser

import mysql.connector


def load_config():
    config = configparser.RawConfigParser()
    config.read('../../conf/wmffl.conf')
    return config


def get_db_connection():
    config = load_config()
    conn = mysql.connector.connect(
        host="localhost",
        user=config.get('DB_Values', 'username'),
        password=config.get('DB_Values', 'password'),
        database=config.get('DB_Values', 'dbname')
    )
    return conn
