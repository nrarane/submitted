<?php
    function ft_escape_str($string){
		return (filter_var($string, FILTER_SANITIZE_STRING));
	}
?>