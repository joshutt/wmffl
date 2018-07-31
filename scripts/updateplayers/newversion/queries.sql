update nflrosters set dateoff=null where dateoff='0000-00-00';


update newplayers p, nflrosters r set p.team=r.nflteamid where (p.team is null or p.team <> r.nflteamid) and r.dateoff is null and r.playerid=p.playerid;

