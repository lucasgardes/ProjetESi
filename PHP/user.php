<?php
class User {
    private $id;
    private $firstname;
    private $lastname;
    private $email;
    private $startLocation;
    private $batteryLevel;
    private $currentLocation;

    public function __construct($id, $firstname, $lastname, $email, $startLocation, $batteryLevel, $currentLocation) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->startLocation = $startLocation;
        $this->batteryLevel = $batteryLevel;
        $this->currentLocation = $currentLocation;
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getStartLocation() {
        return $this->startLocation;
    }

    public function setStartLocation($startLocation) {
        $this->startLocation = $startLocation;
    }

    public function getBatteryLevel() {
        return $this->batteryLevel;
    }

    public function setBatteryLevel($batteryLevel) {
        $this->batteryLevel = $batteryLevel;
    }

    public function getCurrentLocation() {
        return $this->currentLocation;
    }

    public function setCurrentLocation($currentLocation) {
        $this->currentLocation = $currentLocation;
    }
}
?>
