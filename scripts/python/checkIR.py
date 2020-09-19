import mysql.connector

import utils


def main():
    conn = utils.get_db_connection()

    # Determine all players currently on IR that are not elgible
    clear_string = "select ir.playerid, r.TeamID from ir " \
                   + "join weekmap wm on now() between wm.StartDate and wm.EndDate " \
                   + "left join newinjuries inj on ir.playerid=inj.playerid and " \
                   + "inj.season=wm.Season and inj.week=wm.week " \
                   + "left join roster r on ir.playerid=r.playerid and r.dateoff is null " \
                   + "where ir.dateoff is null and (inj.id is null or inj.status not in ('IR', 'IR-PUP', 'IR-NFI'))"

    update_query = "UPDATE ir SET dateoff=now() WHERE dateoff is null and playerid=%s"
    trans_insert = "INSERT INTO transactions (teamid, PlayerID, Method, Date) " \
                   + "VALUES (%s, %s, 'From IR', now())"

    try:
        # get the players this applies to
        cur = conn.cursor()
        cur.execute(clear_string)
        rows = cur.fetchall()
        cur.close()

        # Loop over each row
        update_cur = conn.cursor(prepared=True)
        trans_cur = conn.cursor(prepared=True)
        for player in rows:
            # update the IR to make it now
            update_cur.execute(update_query, (player[0],))
            # insert into transactions
            if player[1]:
                trans_cur.execute(trans_insert, (player[1], player[0]))
        conn.commit()

    except mysql.connector.Error as error:
        print("Failed to update database: {}".format(error))
        conn.rollback()
    finally:
        conn.close()


if __name__ == '__main__':
    main()
