<?php
    class Hash{
		public static function make($string, $salt = ''){
			return (hash('whirlpool', Config::get('app/salt'). $string . $salt));
		}
		
		public static function salt($length){
			//return (mcrypt_create_iv($length));
			return (self::unique_key($length));
		}
		
		public static function unique_key($retlen = 10){
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charlen = strlen($chars);
            $ret = null;
    
            for ($i = 0; $i < $retlen; $i++){
                $ret .= $chars[rand(0, ($charlen - 1))];
            }
            return ($ret);
		}
	}
?>