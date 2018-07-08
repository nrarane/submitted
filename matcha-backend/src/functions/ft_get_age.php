<?php

function ft_get_age($dateofbirth){
    return ((INT)date('Y') - (INT)date('Y', strtotime($dateofbirth)));
}

?>