<?php
namespace transactions\injury;
require_once "InjuryStatus.php";


class InjuryPlayer
{
    private $firstName;
    private $lastName;
    private $pos;
    private $nflTeam;
    private $team;
    private $status;

    /**
     * InjuryPlayer constructor.
     * @param $firstName
     * @param $lastName
     * @param $status
     */
    public function __construct(string $firstName, string $lastName, InjuryStatus $status)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @param mixed $pos
     */
    public function setPos($pos): void
    {
        $this->pos = $pos;
    }

    /**
     * @return mixed
     */
    public function getNflTeam()
    {
        return $this->nflTeam;
    }

    /**
     * @param mixed $nflTeam
     */
    public function setNflTeam($nflTeam): void
    {
        $this->nflTeam = $nflTeam;
    }

    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $team
     */
    public function setTeam($team): void
    {
        $this->team = $team;
    }

    /**
     * @return InjuryStatus
     */
    public function getStatus(): InjuryStatus
    {
        return $this->status;
    }

    /**
     * @param InjuryStatus $status
     */
    public function setStatus(InjuryStatus $status): void
    {
        $this->status = $status;
    }

    public static function loadAssocArray(array $assocArray): InjuryPlayer
    {
        $statusObj = InjuryStatus::loadAssocArray($assocArray);
        $playerObj = new InjuryPlayer($assocArray['firstname'], $assocArray['lastname'], $statusObj);

        if (array_key_exists('pos', $assocArray)) {
            $playerObj->setPos($assocArray['pos']);
        }

        if (array_key_exists('nflTeam', $assocArray)) {
            $playerObj->setNflTeam($assocArray['nflTeam']);
        }

        if (array_key_exists('team', $assocArray)) {
            $playerObj->setTeam($assocArray['team']);
        }
        return $playerObj;
    }

    public static function aThing()
    {
        print "Here";
    }

}

