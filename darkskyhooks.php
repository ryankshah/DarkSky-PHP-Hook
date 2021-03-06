<?php
    class DarkSky
    {
        private $api_key;
        private $location;
        private $request_url;
        private $units;
        private $latitude;
        private $longitude;

        private $data_blocks = array(
            'currently',
            'minutely',
            'hourly',
            'daily',
            'alerts',
            'flags'
        );

        function __construct($par1Key, $par2Location)
        {
            if(empty($par1Key) || empty($par2Location))
                throw new Exception("One or more parameters were left empty.");

            $this->api_key = $par1Key;
            $this->location = $par2Location;
            $this->units = 'si';

            $this->instantiate();

            if(empty($this->request_url))
                throw new Exception("Failed to instantiate a DarkSky connection.");
        }

        public static function create($par1Key, $par2Location) {
            try {
                return new DarkSky($par1Key, $par2Location);
            } catch (Exception $e) {
                return null;
            }
        }

        public function getUnits() {
            return $this->units;
        }

        public function getDisplayUnits() {
            return ($this->units == 'si' ? 'C' : 'F');
        }

        public function setUnits($units) {
            $this->units = $units;
        }

        public function getLocation() {
            return $this->location;
        }

        public function getLatitude() {
            return $this->latitude;
        }

        public function getLongitude() {
            return $this->longitude;
        }

        public function getAllData() {
            return $this->makeGeneralRequest();
        }

        public function getCurrentData() {
            return $this->makeDetailedRequest('currently');
        }

        public function getMinutelyData() {
            return $this->makeDetailedRequest('minutely');
        }

        public function getHourlyData() {
            return $this->makeDetailedRequest('hourly');
        }

        public function getDailyData() {
            return $this->makeDetailedRequest('daily');
        }

        public function getAlerts() {
            return $this->makeDetailedRequest('alerts');
        }

        private function instantiate() {
            $latlong = $this->getLatLong($this->location);
            $this->latitude = $latlong['lat'];
            $this->longitude = $latlong['long'];

            $this->request_url = "https://api.darksky.net/forecast/{$this->api_key}/{$this->latitude},{$this->longitude}";
        }

        private function getLatLong($location) {
            $url = "http://maps.google.com/maps/api/geocode/json?address={$location}&sensor=false";
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($response);

            $loc['lat'] = $response->results[0]->geometry->location->lat;
            $loc['long'] = $response->results[0]->geometry->location->lng;

            return $loc;
        }

        private function makeGeneralRequest() {
            $response = json_decode(file_get_contents($this->request_url.'?units='.$this->units), true);

            return ($response != null ? $response : null);
        }

        private function makeDetailedRequest($property) {
            $exclusion = array_filter($this->data_blocks, function($el) use ($property) {
                return $el != $property;
            });

            $response = json_decode(file_get_contents($this->request_url.'?units='.$this->units.'&exclude='.implode(',', $exclusion)), true);

            return ($response != null ? $response[$property] : null);
        }
    }
?>