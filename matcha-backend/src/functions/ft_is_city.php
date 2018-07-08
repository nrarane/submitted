<?php
    function is_city($city){
        if (trim($city)){
            $city = ucwords(trim($city));
            $cities = file_get_contents('../src/cities.json');
            if (strpos($cities, $city) !== false){
                return ($city);
            }
        }
        //$cities = json_decode($cities, true);
        //$cities = $cities['response'];
        //print_r($cities);
        return (false);
    }
?>