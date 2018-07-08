<?php
    function    ft_decode_image($image, $path){
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);
        file_put_contents($path, $data);
    }
?>