<?php
    class DarkSky
    {
        private $api_key;
        private $location;
        private $request_url;
        private $units;

        function __construct($par1Key, $par2Location)
        {
            if(empty($par1Key) || empty($par2Location))
                throw new Exception("One or more parameters were left empty.");

            $this->api_key = $par1Key;
            $this->location = $par2Location;
            $this->units = 'si';

            instantiate();

            if(empty($request_url))
                throw new Exception("Failed to instantiate a DarkSky connection.");
        }

        public static function new($par1Key, $par2Location) {
            try {
                return new DarkSky($par1Key, $par2Location);
            } catch (Exception $e) {
                return null;
            }
        }

        private function instantiate() {
            $latlong = getLatLong($location);
            $lat = $latlong['lat'];
            $long = $latlong['long'];

            $request_url = "https://api.darksky.net/forecast/{$api_key}/{$lat},{$long}";
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

        public function setUnits($units) {
            $this->units = $units;
        }

        public function getCurrentData() {
            $response = json_decode(file_get_contents($request_url.'?units='.$units), true);

            if($response == null)
                return null;

            $response = $response['currently'];

            return $response;
        }
    }
?>