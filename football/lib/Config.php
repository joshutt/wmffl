<?

class Config {

    var $init = array();

    function __construct($filename) {
        $this->init = parse_ini_file($filename, true);
    }


    public function getValue($pathName) {
        $pieces = explode(".", $pathName);
        $base = $this->init;
        foreach($pieces as $label) {
            if (!array_key_exists($label, $base)) {
                return null;
            }
            $base = $base[$label];
        }
        return $base;
    }


    public function addFile($filename) {
        $newInit = parse_ini_file($filename, true);
        $this->init = array_replace($this->init, $newInit);
    }


    public function getCategories() {
        $keyArray = array_keys($this->init);
        return $keyArray;
    }


    public function categoryExists($catName) {
        return array_key_exists($catName, $this->init);
    }


    public function getCategory($key) {
        return $this->init[$key];
    }

}
