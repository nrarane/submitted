<?php
    function ft_save_profile_image($image_base){
        $url = null;
        $filename = Config::get('paths/profile_uploads').'/'.'IMG_'.Hash::unique_key(35).'_'.date('Y-m-d_H-i-s').'.png';
        $path = '../';

        ft_ready_app_dir();
        ft_decode_image($image_base, $path.$filename);
        $url = $filename;
        return ($url);
    }
?>