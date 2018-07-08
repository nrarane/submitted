<?php
    function ft_ready_app_dir(){
        $path = '../';

        if (!file_exists($path . Config::get('paths/profile_uploads'))){
            $main_path = explode('/', Config::get('paths/profile_uploads'));
            mkdir($path . $main_path[0], 0777);
            mkdir($path . Config::get('paths/profile_uploads'), 0777);
        }
    }
?>