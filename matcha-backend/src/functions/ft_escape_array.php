<?php
    function ft_escape_array($str){
        if (!is_array($str))
            return (null);
        return (filter_var_array($str, FILTER_SANITIZE_STRING));
    }
?>