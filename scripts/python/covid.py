import utils
from checkIR import ir_insert


def main():
    conn = utils.get_db_connection()

    # Get players on roster and covid list - Eligible list
    covid_players = "select p.playerid, r.teamid " \
                    + "from newplayers p " \
                    + "join roster r on p.playerid=r.PlayerID and r.DateOff is null " \
                    + "join weekmap wm on now() between wm.StartDate and wm.EndDate " \
                    + "join newinjuries i on p.playerid = i.playerid and i.week=wm.week and i.season=wm.season " \
                    + "left join ir on p.playerid=ir.playerid and ir.dateoff is null " \
                    + "where (i.status='COVID-IR'  or (i.status='Holdout' and i.details='Opt Out')) " \
                    + "and ir.id is null"

    insert_to_covid = "insert into ir (playerid, current, dateon, covid) " \
                      + "values (?, 1, now(), 1)"

    ir_insert(conn, covid_players, insert_to_covid, 'To Covid')

    # Get players that need to be removed from covid list
    covid_remove = "select p.playerid, r.TeamID " \
                   + "from newplayers p " \
                   + "join ir on p.playerid = ir.playerid and ir.dateoff is null " \
                   + "join weekmap wm on now() between wm.StartDate and wm.EndDate " \
                   + "left join newinjuries i on p.playerid = i.playerid and i.week=wm.week and i.season=wm.Season " \
                   + "left join roster r on p.playerid=r.PlayerID and r.dateoff is null " \
                   + "where ir.covid=1 and i.status not in ('COVID-IR', 'Holdout')"
    update_covid = "UPDATE ir SET dateoff=now(), current=0 WHERE dateoff is null and playerid=%s"
    ir_insert(conn, covid_remove, update_covid, 'From Covid')

    conn.close()


if __name__ == '__main__':
    main()
