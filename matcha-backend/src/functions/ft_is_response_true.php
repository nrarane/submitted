<?php
    function ft_is_response_true($res){
        if (isset($res['response']['state'])){
            if ($res['response']['state'] === 'true')
                return (true);
        }
        return (false);
    }
?>