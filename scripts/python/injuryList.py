from datetime import datetime

import mysql.connector
import requests

from python import utils
from python.injury import Injury


def get_week(con):
    mycursor = con.cursor()
    mycursor.execute("SELECT season, week FROM weekmap where now() BETWEEN StartDate and EndDate")
    # Get current week
    myresult = mycursor.fetchone()
    return myresult[0], myresult[1]


def get_injury_json(season, week):
    # Read in JSON from url
    url = "https://api.myfantasyleague.com/%s/export?TYPE=injuries&W=%d&JSON=1" % (season, week)
    response = requests.get(url)
    return response.json()


def main():
    conn = utils.get_db_connection()
    conn.autocommit = False
    (season, week) = get_week(conn)
    json_response = get_injury_json(season, week)

    version = json_response['version']
    timestamp = json_response['injuries']['timestamp']
    week = json_response['injuries']['week']
    injuries = json_response['injuries']['injury']

    select_string = "SELECT playerid FROM newplayers WHERE flmid=%s"
    insert_string = "REPLACE INTO newinjuries " + \
                    "(playerid, season, week, status, details, expectedReturn, version, updated) " + \
                    "VALUES (%s, %s, %s, %s, %s, %s, %s, FROM_UNIXTIME(%s))"

    # mycursor.execute(select_string)
    # insert_cur.execute(insert_cur)
    cur = conn.cursor()
    insert_cur = conn.cursor()
    try:
        for injury in injuries:
            inj_obj = Injury.load_by_dict(injury)

            # Get real player id
            cur.execute(select_string, (inj_obj.id,))
            player_result = cur.fetchone()
            if player_result is None:
                continue
            real_id = player_result[0]

            # if return date given, use it
            if inj_obj.expectedReturn:
                adj_date = datetime.strptime(inj_obj.expectedReturn, "%b %d, %Y")
            else:
                adj_date = None

            # Save to table
            insert_cur.execute(insert_string,
                               (real_id, season, week, inj_obj.status, inj_obj.details, adj_date, version, timestamp))

        # commit everything outside loop
        conn.commit()
    except mysql.connector.Error as error:
        print("Failed to update database: {}".format(error))
        conn.rollback()
    finally:
        conn.close()


if __name__ == '__main__':
    main()
