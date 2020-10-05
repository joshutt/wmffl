<?php
require_once "utils/injuryUtils.php";
//namespace stats;


//use mysqli;

class InjuryReportResource
{
    private const CURRENT_IR_LIST = "select p.playerid, p.firstname, p.lastname, p.pos, nr.nflteamid, t.name, t.abbrev, 
       i.details, i.expectedReturn, ir.dateon, ir.covid
        from ir
        join newplayers p on ir.playerid = p.playerid
        join roster r on p.playerid=r.PlayerID and r.DateOff is null
        join weekmap wm on now() between wm.StartDate and wm.EndDate
        join teamnames t on wm.Season = t.season and r.TeamID=t.teamid
        left join newinjuries i on p.playerid = i.playerid and wm.season=i.season and wm.week=i.week
        left join nflrosters nr on nr.playerid=p.playerid and nr.dateoff is null
        where ir.dateoff is null";

    private const ELIGIBLE_IR = "select p.playerid, p.firstname, p.lastname, p.pos, nr.nflteamid, tn.abbrev, inj.status,
       inj.details, inj.expectedReturn
from newplayers p
join weekmap wm on now() between wm.StartDate and wm.EndDate
join newinjuries inj on p.playerid=inj.playerid and inj.season=wm.Season and inj.week=wm.Week
join roster r on p.playerid=r.playerid and r.dateoff is null
left join ir on ir.playerid=p.playerid and ir.dateoff is null
left join nflrosters nr on p.playerid=nr.playerid and nr.dateoff is null
join teamnames tn on r.TeamID=tn.teamid and tn.season=wm.Season
where ir.id is null and inj.status in ";

    private const FULL_REPORT = "select p.firstname, p.lastname, p.pos, n.nflteamid, t.name, 
       if(ir.id, 'WMFFL IR', i.status) as 'status', i.details, wm.season, wm.week
from newinjuries i
join weekmap wm on now() between wm.StartDate and wm.EndDate and i.season=wm.season and i.week=wm.Week
join newplayers p on i.playerid = p.playerid
join roster r on p.playerid=r.PlayerID and r.DateOff is null
left join ir on ir.playerid=p.playerid and ir.dateoff is null
join teamnames t on t.season=wm.season and t.teamid=r.TeamID
left join nflrosters n on n.playerid=p.playerid and n.dateoff is null
order by t.name, p.pos, n.nflteamid";

    /** @var mysqli */
    private $conn;

    /** @var array */
    private $irList = null;

    /** @var array */
    private $covidList = null;

    /**
     * InjuryReportResource constructor.
     * @param $conn
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    public function getIRList(): array
    {
        print_r($this->irList);
        if (!$this->irList || sizeof($this->irList) == 0) {
            $this->loadLists();
        }

        return $this->irList;
    }


    public function getCovidList(): array
    {
        if (!$this->covidList || sizeof($this->covidList) == 0) {
            $this->loadLists();
        }

        return $this->covidList;
    }


    public function getEligible(): array
    {
        $sql = self::ELIGIBLE_IR . getIRStatusSql();
        $result = $this->conn->query($sql);
        $resultArray = array();
        while ($obj = $result->fetch_object()) {
            array_push($resultArray, $obj);
        }
        return $resultArray;
    }

    public function getFullReport(): array
    {
        $result = $this->conn->query(self::FULL_REPORT);
        $resultArray = array();
        $currentTeam = null;
        while ($obj = $result->fetch_object()) {
            if ($obj->name !== $currentTeam) {
                $currentTeam = $obj->name;
                $resultArray[$currentTeam] = array();
            }
            array_push($resultArray[$currentTeam], $obj);
        }
        return $resultArray;
    }


    public function loadLists()
    {
        $result = $this->conn->query(self::CURRENT_IR_LIST);
        $this->covidList = array();
        $this->irList = array();
        while ($obj = $result->fetch_object()) {
            if ($obj->covid == 1) {
                array_push($this->covidList, $obj);
            } else {
                array_push($this->irList, $obj);
            }
        }
    }


}


class IRReportPlayer
{
    private $playerid;
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;
    /**
     * @var string
     */
    private $pos;
    /**
     * @var string
     */
    private $nflTeam;
    /**
     * @var string
     */
    private $team;
    /**
     * @var string
     */
    private $abb;
    /**
     * @var string
     */
    private $detail;
    /**
     * @var string
     */
    private $expReturn;
    /**
     * @var string
     */
    private $dateOn;

    /**
     * IRReportPlayer constructor.
     * @param $playerid
     * @param string $firstName
     * @param string $lastName
     * @param string $pos
     * @param string $nflTeam
     * @param string $team
     * @param string $abb
     * @param string $detail
     * @param string $expReturn
     * @param string $dateOn
     */
    public function __construct($playerid, $firstName = "", $lastName = "", $pos = "", $nflTeam = "", $team = "", $abb = "", $detail = "", $expReturn = "", $dateOn = "")
    {
        $this->playerid = $playerid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->pos = $pos;
        $this->nflTeam = $nflTeam;
        $this->team = $team;
        $this->abb = $abb;
        $this->detail = $detail;
        $this->expReturn = $expReturn;
        $this->dateOn = $dateOn;
    }

    /**
     * @return mixed
     */
    public function getPlayerid()
    {
        return $this->playerid;
    }

    /**
     * @param mixed $playerid
     */
    public function setPlayerid($playerid): void
    {
        $this->playerid = $playerid;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPos(): string
    {
        return $this->pos;
    }

    /**
     * @param string $pos
     */
    public function setPos(string $pos): void
    {
        $this->pos = $pos;
    }

    /**
     * @return string
     */
    public function getNflTeam(): string
    {
        return $this->nflTeam;
    }

    /**
     * @param string $nflTeam
     */
    public function setNflTeam(string $nflTeam): void
    {
        $this->nflTeam = $nflTeam;
    }

    /**
     * @return string
     */
    public function getTeam(): string
    {
        return $this->team;
    }

    /**
     * @param string $team
     */
    public function setTeam(string $team): void
    {
        $this->team = $team;
    }

    /**
     * @return string
     */
    public function getAbb(): string
    {
        return $this->abb;
    }

    /**
     * @param string $abb
     */
    public function setAbb(string $abb): void
    {
        $this->abb = $abb;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     */
    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getExpReturn(): string
    {
        return $this->expReturn;
    }

    /**
     * @param string $expReturn
     */
    public function setExpReturn(string $expReturn): void
    {
        $this->expReturn = $expReturn;
    }

    /**
     * @return string
     */
    public function getDateOn(): string
    {
        return $this->dateOn;
    }

    /**
     * @param string $dateOn
     */
    public function setDateOn(string $dateOn): void
    {
        $this->dateOn = $dateOn;
    }
}


