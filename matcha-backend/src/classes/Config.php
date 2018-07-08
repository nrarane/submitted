<?php
    class Config{
        public function get($path){
            $config = $GLOBALS['api_config'];
            $path_bits = explode('/', $path);

            foreach ($path_bits as $el){
                if (isset($config[$el]))
                    $config = $config[$el];
                else
                    return (false);
            }
            return ($config);
        }

        public function response($response = null, $path = null, $value = null){
            if (!$response)
                return (self::get('response_format'));
            if (!is_array($response) || !$value || !$path)
                return (array());
            
            $path_bits = explode('/', $path);
            if (isset($response[$path_bits[0]])){
                if ($path_bits[0] === 'response'){
                    if (isset($response[$path_bits[0]][$path_bits[1]])){
                        $response[$path_bits[0]][$path_bits[1]] = $value;
                    }else
                        return (array());
                }
                else
                    $response[$path_bits[0]] = $value; 
                return ($response);
            }
            return (array());
        }
    }
?>