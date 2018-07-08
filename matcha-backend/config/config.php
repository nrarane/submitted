<?php
    $GLOBALS['api_config'] = array(
        'server' => array(
            'host' => 'localhost',
            'db_user' => 'root',
            'db_password' => 'nrarane',
            'db_name' => 'matcha_db'
        ),
        'app' => array(
            //NB*! url without "http://"
            'url' => '127.0.0.1:8081/matcha-backend',
            'name' => 'Matcha',
            'email' => '',
            'email_password' => '',
            'salt' => '8cd8aa091d721adbdc',
            'author' => 'Nyameko Rarane'
        ),
        'paths' => array(
            'profile_uploads' => 'uploads/profiles'
        ),
        'response_format' => array(
            "response" => array(
                "state" => "false",
                "message" => ""
            ),
            "data" => ""
        ),
        'setup_formats' => array(
            'table_names' => array(
                'tbl_users',
                'tbl_user_registrations',
                'tbl_user_images',
                'tbl_user_history',
                'tbl_login_session',
                'tbl_user_connections',
                'tbl_user_messages',
                'tbl_interests',
                'tbl_user_interests',
                'tbl_user_report',
                'tbl_user_block',
                'tbl_access',
                'tbl_user_locations'
            ),
            'table_queries' => array(
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(16) UNIQUE NOT NULL, `email` VARCHAR(120) UNIQUE NOT NULL, `password` TEXT NOT NULL, `firstname` VARCHAR(90) NOT NULL, `lastname` VARCHAR(90) NOT NULL, `gender` ENUM('none','male','female') NOT NULL DEFAULT 'none', `date_of_birth` DATE, `sexual_preference` ENUM('all','male','female') NOT NULL DEFAULT 'all', `biography` TEXT, `address` TEXT, `token` TEXT, `salt` TEXT NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(16) UNIQUE NOT NULL, `email` VARCHAR(120) UNIQUE NOT NULL, `password` TEXT NOT NULL, `firstname` VARCHAR(90) NOT NULL, `lastname` VARCHAR(90) NOT NULL, `date_of_birth` DATE, `salt` TEXT, `token` TEXT, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL REFERENCES tbl_users(id), `code` INT NOT NULL, `url` TEXT NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id_from` INT NOT NULL REFERENCES tbl_users(id), `user_id_to` INT NOT NULL, `action` ENUM('connect','unconnect','visit', 'login', 'message', 'report', 'block') NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL REFERENCES tbl_users(id), `session` VARCHAR(255) UNIQUE NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id_from` INT NOT NULL REFERENCES tbl_users(id), `user_id_to` INT NOT NULL REFERENCES tbl_users(id), `status` INT NOT NULL DEFAULT 0, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id_from` INT NOT NULL REFERENCES tbl_users(id), `user_id_to` INT NOT NULL REFERENCES tbl_users(id), `message` TEXT NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `tag` TEXT NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL REFERENCES tbl_users(id), `interest_id` INT NOT NULL REFERENCES tbl_interests(id), `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id_from` INT NOT NULL REFERENCES tbl_users(id), `user_id_to` INT NOT NULL REFERENCES tbl_users(id), `description` TEXT, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id_from` INT NOT NULL REFERENCES tbl_users(id), `user_id_to` INT NOT NULL REFERENCES tbl_users(id), `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `app` TEXT NOT NULL, `token` TEXT NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                "`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL REFERENCES tbl_users(id), `location` TEXT NOT NULL, `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ),
            'data_queries' => array(
                '' => ''
            )
        )
    );
?>
