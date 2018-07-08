<?php
    function ft_ms_register($username, $token){
        $ret = '<html>'.
            '<body style="font-family: Tahoma, Geneva, Verdana, sans-serif; color: #525252;">'.
            '<p><b>Matcha - Registration Code</b></p>'.
            '<br>'.
            '<p>Hello <b>'. $username .'</b>.</p>'.
            '<p>Your code is:</p>'.
            '<div style="padding: 10px 15px; display: inline-block; border: 1px solid black">'.
            '<h2 style="font-weight: 100; margin: 0;">'. $token .'</h2>'.
            '</div>'.
            '<br><br><br>'.
            '<small style="color: #8f8f8f">If this was a mistake, just ignore this email and nothing will happen, thank you.</small>'.
            '</body>'.
            '</htm>';
        return ($ret);
    }

    function ft_ms_verify_token($username, $token){
        $ret = '<html>'.
            '<body style="font-family: Tahoma, Geneva, Verdana, sans-serif; color: #525252;">'.
            '<p><b>Matcha - Verify Token</b></p>'.
            '<br>'.
            '<p>Hello <b>'. $username .'</b>.</p>'.
            '<p>Your token is:</p>'.
            '<div style="padding: 10px 15px; display: inline-block; border: 1px solid black">'.
            '<h2 style="font-weight: 100; margin: 0;">'. $token .'</h2>'.
            '</div>'.
            '<br><br><br>'.
            '<small style="color: #8f8f8f">If this was a mistake, just ignore this email and nothing will happen, thank you.</small>'.
            '</body>'.
            '</htm>';
        return ($ret);
    }

    function ft_ms_report_user($username, $reporter){
        $ret = '<html>'.
            '<body style="font-family: Tahoma, Geneva, Verdana, sans-serif; color: #525252;">'.
            '<p><b>Matcha - User Report</b></p>'.
            '<br>'.
            '<p>Hello <b>'. $username .'</b>.</p>'.
            'you have been reported you on some kind of some malice, so we are currently conducting investigation, further communication will be sent to you regarding our investigation result.'.
            '<br><br><br>'.
            '<small style="color: #8f8f8f">If this was a mistake, just ignore this email and nothing will happen, thank you.</small>'.
            '</body>'.
            '</htm>';
        return ($ret);
    }
?>