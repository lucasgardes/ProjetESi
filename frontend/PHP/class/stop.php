<?php
class Stop {
    public $name;
    public $load = 50;

    // Constructor to initialize the Stop with its name and load
    public function __construct($name, $load)  {
        $this->name = $name;
        $this->load = $load;
    }

    // Get name of stop
    public function getName() {
        return $this->name;
    }

    // Get load of stop
    public function getLoad() {
        return $this->load;
    }

    // Empty load of stop
    public function emptyLoad() {
        $this->load = 0;
    }
}
?>