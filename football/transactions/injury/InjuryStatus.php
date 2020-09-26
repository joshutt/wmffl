<?php
namespace transactions\injury;

class InjuryStatus
{
    private $status;
    private $details;
    private $expReturn;

    /**
     * InjuryStatus constructor.
     * @param $status
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     */
    public function setDetails($details): void
    {
        $this->details = $details;
    }

    /**
     * @return mixed
     */
    public function getExpReturn()
    {
        return $this->expReturn;
    }

    /**
     * @param mixed $expReturn
     */
    public function setExpReturn($expReturn): void
    {
        $this->expReturn = $expReturn;
    }


    public static function loadAssocArray($assocArray): InjuryStatus
    {
//        print "IS Load";
        $statusObj = new InjuryStatus($assocArray['status']);
        if (key_exists('details', $assocArray)) {
            $statusObj->setDetails($assocArray['details']);
        }

        if (key_exists('expectedReturn', $assocArray)) {
            $statusObj->setExpReturn($assocArray['expectedReturn']);
        }
//        print "!";
        return $statusObj;
    }
}