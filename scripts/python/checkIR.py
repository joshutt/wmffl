import mysql.connector

import utils


def main():
    conn = utils.get_db_connection()

    # Determine all players currently on IR that are not eligible
    clear_string = "select ir.playerid, r.TeamID from ir " \
                   + "join weekmap wm on now() between wm.StartDate and wm.EndDate " \
                   + "left join newinjuries inj on ir.playerid=inj.playerid and " \
                   + "inj.season=wm.Season and inj.week=wm.week " \
                   + "left join roster r on ir.playerid=r.playerid and r.dateoff is null " \
                   + "where ir.dateoff is null and (inj.id is null or inj.status not in ('IR', 'IR-PUP', 'IR-NFI')) " \
                   + "and ir.covid=0"

    update_query = "UPDATE ir SET dateoff=now(), current=0 WHERE dateoff is null and playerid=%s"

    ir_insert(conn, clear_string, update_query, 'From IR')
    conn.close()


def ir_insert(conn, retrive_query, update_query, trans_type):
    trans_insert = "INSERT INTO transactions (teamid, PlayerID, Method, Date) " \
                   + "VALUES (%s, %s, %s, now())"
    try:
        # get the players this applies to
        cur = conn.cursor()
        cur.execute(retrive_query)
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
                trans_cur.execute(trans_insert, (player[1], player[0], trans_type))
        conn.commit()

    except mysql.connector.Error as error:
        print("Failed to update database: {}".format(error))
        conn.rollback()


if __name__ == '__main__':
    main()
