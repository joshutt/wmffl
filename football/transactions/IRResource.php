<?php
require_once "utils/injuryUtils.php";


class IRResource
{
    private const ELGIBLE_QUERY = "select p.playerid, p.firstname, p.lastname, p.pos, inj.status, inj.details, DATE_FORMAT(inj.expectedReturn, '%m-%d-%Y')
from newplayers p
join weekmap wm on now() between wm.StartDate and wm.EndDate
join roster r on p.playerid=r.PlayerID and r.dateoff is null
join newinjuries inj on p.playerid = inj.playerid and inj.season=wm.Season and inj.week=wm.Week
left join ir on p.playerid = ir.playerid and ir.dateoff is null
where r.teamid=? and ir.id is null and inj.status in ";

    private const CURRENT_IR_QUERY = "select p.playerid, p.firstname, p.lastname, p.pos, DATE_FORMAT(ir.dateon, '%m-%d-%Y'), n.details, DATE_FORMAT(n.expectedReturn, '%m-%d-%Y')
from newplayers p
join weekmap wm on now() between wm.StartDate and wm.EndDate
join roster r on p.playerid=r.PlayerID and r.DateOff is null
join ir on ir.playerid=p.playerid and ir.dateoff is null
left join newinjuries n on p.playerid = n.playerid and wm.Season=n.season and wm.week=n.week
where r.TeamID=?";

    private const INSERT_IR_QUERY = "insert into ir
(playerid, current, dateon)
select p.playerid, 1, now()
from newplayers p
join roster r on p.playerid=r.PlayerID and r.DateOff is null
join weekmap wm on now() between wm.StartDate and wm.EndDate
left join ir on p.playerid=ir.playerid and ir.dateoff is null
left join newinjuries inj on p.playerid=inj.playerid and wm.Season=inj.season and wm.week=inj.week
where p.playerid=? and r.teamid=? and ir.id is null and inj.status in ";

    private const REMOVE_IR_QUERY = "update newplayers p
join roster r on p.playerid=r.PlayerID and r.DateOff is null
join weekmap wm on now() between wm.StartDate and wm.EndDate
left join ir on p.playerid=ir.playerid and ir.dateoff is null
set ir.dateoff=now()
where p.playerid=? and r.teamid=? and ir.id is not null";

    private const INSERT_TRANS = "INSERT INTO transactions (TeamID, PlayerID, Method, Date) VALUES (?, ?, ?, now())";


    /** @var mysqli */
    private $conn;

    /**
     * @var int
     */
    private $teamNum;
    private $irElgible = array();
    private $currentIr = array();

    /**
     * IRResource constructor.
     * @param $conn
     * @param $teamNum
     */
    public function __construct($conn, $teamNum)
    {
        $this->conn = $conn;
        $this->teamNum = $teamNum;
    }

    /**
     * @return array
     */
    public function getIrElgible(): array
    {
        return $this->irElgible;
    }

    /**
     * @return array
     */
    public function getCurrentIr(): array
    {
        return $this->currentIr;
    }

    public function loadElgiblePlayers()
    {
        /** @var mysqli_stmt $stmt */
        $stmt = $this->conn->prepare(self::ELGIBLE_QUERY . getIRStatusSql());
        $stmt->bind_param("i", $this->teamNum);
        $stmt->execute();

        $stmt->bind_result($playerid, $firstName, $lastName, $pos, $status, $details, $expReturn);

        while ($stmt->fetch()) {
            $irPlayer = new IRPlayer($playerid, $firstName, $lastName, $pos, $status, $details, $expReturn);
            array_push($this->irElgible, $irPlayer);
        }
    }

    public function loadCurrentIRPlayers()
    {
        /** @var mysqli_stmt $stmt */
        $stmt = $this->conn->prepare(self::CURRENT_IR_QUERY);
        $stmt->bind_param("i", $this->teamNum);
        $stmt->execute();

        $stmt->bind_result($playerid, $firstName, $lastName, $pos, $dateOn, $details, $expReturn);

        while ($stmt->fetch()) {
            $irPlayer = new IRPlayer($playerid, $firstName, $lastName, $pos, $dateOn, $details, $expReturn);
            array_push($this->currentIr, $irPlayer);
        }

    }

    public function addPlayerToIR(IRPlayer $player): int
    {
        $insertQuery = self::INSERT_IR_QUERY . getIRStatusSql();

        $stmt = $this->conn->prepare($insertQuery);
        $stmt->bind_param("ii", $player->playerid, $this->teamNum);
        $stmt->execute();

        $rows = $stmt->affected_rows;
        $stmt->close();

        $this->updateTransaction($player, "To IR");

        if ($rows > 0) {
            $success = TRUE;
        } else {
            $success = FALSE;
        }
        return $success;
    }


    public function removePlayerFromIR(IRPlayer $player): int
    {
        $stmt = $this->conn->prepare(self::REMOVE_IR_QUERY);
        $stmt->bind_param("ii", $player->playerid, $this->teamNum);
        $stmt->execute();

        $rows = $stmt->affected_rows;
        $stmt->close();

        $this->updateTransaction($player, "From IR");

        if ($rows > 0) {
            $success = TRUE;
        } else {
            $success = FALSE;
        }
        return $success;
    }

    /**
     * @param IRPlayer $player
     * @param string $transMethod
     */
    protected function updateTransaction(IRPlayer $player, string $transMethod): void
    {
        $stmt2 = $this->conn->prepare(self::INSERT_TRANS);
        $stmt2->bind_param("iis", $this->teamNum, $player->playerid, $transMethod);
        $stmt2->execute();
        $stmt2->close();
    }


}


class IRPlayer
{
    public $playerid;
    public $firstName;
    public $lastName;
    public $pos;
    public $status;
    public $details;
    public $expReturn;

    /**
     * IRPlayer constructor.
     * @param $playerid
     * @param $firstName
     * @param $lastName
     * @param $pos
     * @param $status
     * @param $details
     * @param $expReturn
     */
    public function __construct($playerid, $firstName="", $lastName="", $pos="", $status="", $details="", $expReturn="")
    {
        $this->playerid = $playerid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->pos = $pos;
        $this->status = $status;
        $this->details = $details;
        $this->expReturn = $expReturn;

        if ($this->details === Null || $this->details === "") {
            $this->details = "Unknown";
        }
    }

}