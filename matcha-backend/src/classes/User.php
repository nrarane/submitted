<?php
    class User extends Database{

        public function info($target){
            $error = 'Could not get user\'s information';
            $res = Config::get('response_format');
            new Database();

            if (!is_array($target) || empty($target))
                return (array());
            $target_key = array_keys($target)[0];
            $target_value = $target[$target_key];
            $session = null;

            if ($target_key !== 'id' && $target_key !== 'token')
                return (array());
            if (empty($target_value))
                return (array());
            if ($target_key === 'token'){
                if (($stmt = parent::select('tbl_login_session', array('session', '=', $target_value)))){
                    if (parent::getCount($stmt) > 0){
                        $session = parent::getRows($stmt)[0];
                    }else
                        return (array());
                }
            }

            if ($session)
                $target_value = $session->user_id;
            
            if (($stmt = parent::select('tbl_users', array('id', '=', $target_value)))){
                if (parent::getCount($stmt) == 1){
                    $_data = parent::getRows($stmt, 0)[0];

                    if ($session){
                        $_data['session'] = $session->session;
                    }

                    if (($images = parent::select('tbl_user_images', array('user_id', '=', $_data['id']), null, true))){
                        if ($images->rowCount > 0){
                            
                            foreach ($images->rows as $image){
                                if ($image['code'] == 1)
                                    $_data['img'.$image['code']] = $image;
                            }
                        }
                    }

                    $tags = self::tags($_data['id']);
                    if (isset($tags['response']) && $tags['response']['state'] == 'true')
                        $_data['tags'] = $tags['data'];
                    
                    $fame = self::visits($_data['id']);
                    if ($fame)
                        $_data['visits'] = (double)$fame;

                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'success');
                    $res = Config::response($res, 'data', $_data);
                    return ($res);
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function login($login, $password){
            $error = 'Could not log you in at this time, please wait 2 minutes or so and try again.';
            $res = Config::get('response_format');
            new Database();

            $where = array(
                'username', '=', strtolower($login),
                '||',
                'email', '=', strtolower($login)
            );

            if (($stmt = parent::select('tbl_users', $where))){
                if (parent::getCount($stmt) == 1){
                    $user = parent::getRows($stmt, 1)[0];
                    if (Hash::make($password, $user->salt) === $user->password){
                        if (($stmt = parent::select('tbl_login_session', array('user_id', '=', $user->id)))){
                            //Check if user has a login session, if not insert new one else update old one
                            $token = Hash::unique_key(84);

                            if (!parent::getCount($stmt)){
                                $input = array(
                                    'user_id' => $user->id,
                                    'session' => $token
                                );

                                if ((!parent::insert('tbl_login_session', $input)))
                                    return (Config::response($res, 'response/message', $error));
                            }else{
                                $where = array('user_id' ,'=', $user->id);
                                $input = array(
                                    'user_id' => $user->id,
                                    'session' => $token
                                );

                                if ((!parent::update('tbl_login_session', $input, $where)))
                                    return (Config::response($res, 'response/message', $error));
                            }

                            /*
                            $inputHistory = array(
                                'user_id_from' => $user->id,
                                'user_id_to' => -1,
                                'action' => 'login'
                            );
                            parent::insert('tbl_user_history', $inputHistory);
                            */

                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'login success');
                            $data = (object)self::info(array('token' => $token));
                            $res = Config::response($res, 'data', $data->data);
                            return ($res);
                        }else
                            return (Config::response($res, 'response/message', $error));
                    }
                }
                return (Config::response($res, 'response/message', 'Username or password is incorrect.'));
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function register($fn, $ln, $username, $email, $password, $dob){
            $res = Config::get('response_format');
            new Database();

            if (ft_get_age($dob) < 18)
                return (Config::response($res, 'response/message', 'You are too young to have an account on this site, you must be 18+ years .'));

            //Check if user already has an account...
            if (($stmt = parent::select('tbl_users', array('username', '=', $username)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Username already taken.'));
                }
            }
            if (($stmt = parent::select('tbl_users', array('email', '=', $email)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Email already has an accoun.'));
                }
            }

            //Check if user already registered, and is yet to confirm registration...
            if (($stmt = parent::select('tbl_user_registrations', array('username', '=', $username)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Username already registered.'));
                }
            }
            if (($stmt = parent::select('tbl_user_registrations', array('email', '=', $email)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Email already registered.'));
                }
            }

            $token = Hash::unique_key(8);
            while (self::is_token_unique($token))
                $token = Hash::unique_key(8);
            
            if (!ft_sendmail($email, ucwords($fn . ' ' . $ln), Config::get('app/name') . " - Registration Confirmation", ft_ms_register(ucwords($fn . ' ' . $ln), $token))){
                return (Config::response($res, 'response/message', 'could not send email confirmation, please try again'));
            }

            $salt = Hash::salt(15);

            $input = array(
                'username' => strtolower($username),
                'email' => strtolower($email),
                'password' => Hash::make($password, $salt),
                'firstname' => ucwords($fn),
                'lastname' => ucwords($ln),
                'date_of_birth' => $dob,
                'salt' => $salt,
                'token' => $token
            );

            if ((parent::insert('tbl_user_registrations', $input))){
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'You have successfully registered to '. Config::get('app/name') .'. Please check your email to confirm your registration.');
                return ($res);
            }
            return (Config::response($res, 'response/message', 'Could not register you at this time, please wait 5 minutes or so and try again.'));
        }

        private function is_token_unique($token){
            new Database();
            $where = array(
                'token', '=', $token
            );

            if (($ret = parent::select('tbl_user_registrations', $where, null, true))){
                if ($ret->rowCount)
                    return (true);
            }
            return (false);
        }

        public function logout($session){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($stmt = parent::select('tbl_login_session', $where))){
                if (parent::getCount($stmt) > 0){
                    if (parent::delete('tbl_login_session', $where)){
                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'logout success');
                        return ($res);
                    }
                }else
                    return (Config::response($res, 'response/message', 'Login session was not found'));
            }
            return (Config::response($res, 'response/message', 'Could not log you out'));
        }

        public function is_logged($session){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($stmt = parent::select('tbl_login_session', $where))){
                if (parent::getCount($stmt) > 0){
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'Logged in');
                    return ($res);
                }else
                    return (Config::response($res, 'response/message', 'Not logged in'));
            }
            return (Config::response($res, 'response/message', 'Could not check if you logged in'));
        }

        public function changepassword($username, $old_pass, $new_pass){
            $res = Config::get('response_format');
            new Database();

            $where = array('username', '=', $username);
            if (($data = parent::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    
                    if (Hash::make($old_pass, $user->salt) === $user->password){
                        $salt = Hash::salt(15);
                        $input = array(
                            'password' => Hash::make($new_pass, $salt),
                            'salt' => $salt
                        );

                        if (parent::update('tbl_users', $input, $where)){
                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'Password successfully changed');
                            return ($res);
                        }
                    }else
                        return (Config::response($res, 'response/message', 'Old Passwords does not match your current password'));
                }else
                    return (Config::response($res, 'response/message', 'Username not found'));
            }
            return (Config::response($res, 'response/message', 'Could not change your password'));
        }

        public function resetpassword($username, $password, $token){
            $res = Config::get('response_format');
            new Database();

            $where = array('username', '=', $username);
            if (($data = parent::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    
                    if ($user->token === $token){
                        $salt = Hash::salt(15);
                        $input = array(
                            'password' => Hash::make($password, $salt),
                            'salt' => $salt
                        );

                        if (parent::update('tbl_users', $input, $where)){
                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'Reset successful');
                            parent::update('tbl_users', array('token' => ''), $where);
                            return ($res);
                        }
                    }
                }
                return (Config::response($res, 'response/message', 'Incorrect key'));
            }
            return (Config::response($res, 'response/message', 'Could not reset your password'));
        }

        public function confirm_registration($token){
            $res = Config::get('response_format');
            new Database();

            
            $where = array('token', '=', $token);
            if (($data = parent::select('tbl_user_registrations', $where, null, true))){
                if ($data->rowCount > 0){
                    $reg = (object)$data->rows[0];
                    $input = array(
                        'firstname' => $reg->firstname,
                        'lastname' => $reg->lastname,
                        'username' => $reg->username,
                        'email' => $reg->email,
                        'password' => $reg->password,
                        'date_of_birth' => $reg->date_of_birth,
                        'salt' => $reg->salt
                    );

                    if (!parent::insert('tbl_users', $input))
                        return (Config::response($res, 'response/message', 'Could not confirm registration at this time, please try later'));
                    
                    parent::delete('tbl_user_registrations', $where);
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'Confirmation successful');
                    return ($res);
                }
                return (Config::response($res, 'response/message', 'Incorrect code'));
            }
            return (Config::response($res, 'response/message', 'Could not confirm registration'));
        }

        public function update_profile($session, $fn, $ln, $gender, $dob, $sexual_preference, $bio, $address){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($data = parent::select('tbl_login_session', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_data = (object)$data->rows[0];

                    $where = array('id', '=', $user_data->user_id);
                    $input = array(
                        'firstname' => $fn,
                        'lastname' => $ln,
                        'gender' => $gender,
                        'date_of_birth' => $dob,
                        'sexual_preference' => $sexual_preference,
                        'biography' => $bio,
                        'address' => $address
                    );
                    if (parent::update('tbl_users', $input, $where)){
                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'Profile successfully updated');
                        return ($res);
                    }
                }
            }

            return (Config::response($res, 'response/message', 'Could not update your profile, try again.'));
        }

        public function upload_profile($session, $image, $code){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($data = parent::select('tbl_login_session', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_data = (object)$data->rows[0];

                    $where = array(
                        'user_id', '=', $user_data->user_id,
                        'AND',
                        'code', '=', $code
                    );
                    if (($data = parent::select('tbl_user_images', $where, null, true))){
                        $url =  'http://'.Config::get('app/url') .'/'. ft_save_profile_image($image);

                        $input = array(
                            'user_id' => $user_data->user_id,
                            'code' => $code,
                            'url' => $url
                        );

                        if ($data->rowCount > 0){
                            //Update...
                            $data = (object)$data->rows[0];
                            $file = '../'.$data->url;
                            if (file_exists($file))
                                unlink($file);

                            if (parent::update('tbl_user_images', $input, $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'Image upload success');
                                $res = Config::response($res, 'data', array('url' => $url));
                                return ($res);
                            }
                        }else{
                            //Insert...
                            if (parent::insert('tbl_user_images', $input)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', array('url' => $url));
                                return ($res);
                            }
                        }
                    }
                }
            }

            return (Config::response($res, 'response/message', 'Could not upload image, try again.'));
        }

        public function add_tag($tag, $userid){
            new Database();
            $res = Config::get('response_format');

            $where = array(
                'tag', '=', $tag
            );

            $tag = trim($tag);

            if (preg_match('/\s/',$tag))
                return (Config::response($res, 'response/message', 'Tag should not contain white-spaces'));

            if (($data = parent::select('tbl_interests', $where, null, true))){
                if ($data->rowCount > 0){
                    $data = (object)$data->rows[0];
                    $where = array(
                        'user_id', '=', $userid,
                        'AND',
                        'interest_id', '=', $data->id
                    );
                    if (($new_data = parent::select('tbl_user_interests', $where, null, true))){
                        if ($new_data->rowCount == 0){
                            $input = array(
                                'user_id' => $userid,
                                'interest_id' => $data->id
                            );
                            if (!parent::insert('tbl_user_interests', $input)){
                                $res = Config::response($res, 'response/message', 'Error');
                                return ($res);
                            }
                        }
                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'success');
                        return ($res);
                    }
                }else{
                    $input = array(
                        'tag' => $tag 
                    );
                    if (!parent::insert('tbl_interests', $input)){
                        $res = Config::response($res, 'response/message', 'Error');
                        return ($res);
                    }else
                        return (self::add_tag($tag, $userid));
                }
            }
            return (Config::response($res, 'response/message', 'Error'));
        }

        public function tags($userid){
            new Database();
            $conn = parent::connection();
            $res = Config::get('response_format');

            $query = "SELECT * FROM tbl_user_interests, tbl_interests WHERE tbl_interests.id = interest_id AND user_id = :userid";
            $stmt = $conn->prepare($query);
            $stmt->bindparam(':userid', $userid);

            if ($stmt->execute()){
                if (parent::getCount($stmt) > 0){
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'records:'.parent::getCount($stmt));
                    return (Config::response($res, 'data', parent::getRows($stmt)));
                }
            }
            return (Config::response($res, 'response/message', 'records:0'));
        }

        public function track($id, $location){
            new Database();
            $res = Config::get('response_format');
            $error = 'Error';
            $where = array(
                'user_id', '=', $id,
                'AND',
                'location', '=', $location
            );

            if (($data = parent::select('tbl_user_locations', $where, null, true))){
                $input = array(
                    'user_id' => $id,
                    'location' => $location
                );

                if ($data->rowCount > 0){
                    /*
                    if (!parent::update('tbl_user_locations', $input, $where))
                        return (Config::response($res, 'response/message', $error));
                    */

                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'Track success');
                    return ($res);
                }else{
                    if (!parent::insert('tbl_user_locations', $input))
                        return (Config::response($res, 'response/message', $error));
                    
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'Insert sccess');
                    return ($res);
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function visits($id){
            $query = "SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = $id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history";
            
            if (($views = parent::rawQuery($query, true))){
                $views = (object)$views;
                $views = (object)$views->rows[0];
                return (number_format(trim($views->count), 2, '.', ''));
            }
            return (0);
        }

        public function get_visits($id){
            $ret = array();
            $query = "SELECT DISTINCT username, tbl_user_history.action, tbl_user_history.date_created  FROM tbl_users, tbl_user_history WHERE tbl_users.id = tbl_user_history.user_id_from AND tbl_user_history.user_id_to = $id AND tbl_user_history.action = 'visit' ORDER BY tbl_user_history.date_created DESC;";
            $query_count = "SELECT DISTINCT COUNT(username) as 'count'  FROM tbl_users, tbl_user_history WHERE tbl_users.id = tbl_user_history.user_id_from AND tbl_user_history.user_id_to = $id AND tbl_user_history.action = 'visit' ORDER BY tbl_user_history.date_created DESC;";
            
            if (($views = parent::rawQuery($query, true))){
                $views = (object)$views;
                if (!$views->rows)
                    return(false);
                if (($views_count = parent::rawQuery($query_count, true))){
                    $views_count = (object)$views_count['rows'][0];
                    $ret['count'] = $views_count->count;
                }
                $ret['data'] = $views->rows;

                return ($ret);
            }
            return(false);
        }

        public function get_likes($id){
            $ret = array();
            $query = "SELECT DISTINCT username, tbl_user_history.action, tbl_user_history.date_created FROM tbl_users, tbl_user_history WHERE tbl_users.id = tbl_user_history.user_id_from AND tbl_user_history.user_id_to = $id AND (tbl_user_history.action = 'connect' OR tbl_user_history.action = 'unconnect') ORDER BY tbl_user_history.date_created DESC;";
            $query_count = "SELECT DISTINCT COUNT(username) as 'count' FROM tbl_users, tbl_user_history WHERE tbl_users.id = tbl_user_history.user_id_from AND tbl_user_history.user_id_to = $id AND (tbl_user_history.action = 'connect' OR tbl_user_history.action = 'unconnect') ORDER BY tbl_user_history.date_created DESC;";

            if (($views = parent::rawQuery($query, true))){
                $views = (object)$views;
                if (!$views->rows)
                    return(false);
                if (($views_count = parent::rawQuery($query_count, true))){
                    $views_count = (object)$views_count['rows'][0];
                    $ret['count'] = $views_count->count;
                }
                $ret['data'] = $views->rows;
                
                return ($ret);
            }
            return(false);
        }

        public function suggestions($id, $inerests = false){
            new Database();
            $res = Config::get('response_format');

            if (($user = parent::select('tbl_users', array('id', '=', $id), null, true))){
                if ($user->rowCount > 0){
                    $user = (object)$user->rows[0];

                    if ($location = self::city($id, $user->address)){
                        //location...
                        $query = "SELECT tbl_users.id as 'user_id', username, gender FROM tbl_users, tbl_user_locations WHERE tbl_users.id = tbl_user_locations.user_id AND (tbl_users.address LIKE '%$location%' OR tbl_user_locations.location LIKE '%$location%');";
                        
                        if (($data = parent::rawQuery($query, true))){
                            $data = (object)$data;
                            if ($data->rowCount > 0){
                                $matched_users = array();
                                $data = $data->rows;

                                if ($user->date_of_birth){
                                    //echo "User age: $user->date_of_birth ($user->id. $user->username #$user->gender @$user->sexual_preference - $location)<br>";
                                    foreach ($data as $element){
                                        $element = (object)$element;
                                        if (($other_user = self::age_match($id, $user->date_of_birth, $element->user_id, 5))){
                                            if (self::filter_sex($user->gender, $user->sexual_preference, $element->gender)){
                                                if (!in_array($element->user_id, $matched_users)){
                                                    $matched_users[] = $element->user_id;
                                                }
                                            }
                                        }
                                    }

                                    if (count($matched_users) > 0 && $inerests){
                                        $tmp = array();
                                        foreach ($matched_users as $matched_users_id){
                                            if (($usr_data = self::filter_interests($user->id, $matched_users_id))){
                                                $usr_data = (object)$usr_data;
                                                $tmp[] = $usr_data->user_id;
                                            }
                                        }
                                        $matched_users = $tmp;
                                    }
                                    
                                    if (count($matched_users) > 0){
                                        $res = Config::response($res, 'response/state', 'true');
                                        $res = Config::response($res, 'response/message', 'success');
                                        $res = Config::response($res, 'data', $matched_users);                                    
                                        return ($res);
                                    }
                                    return (Config::response($res, 'response/message', 'Could not find a match for you'));
                                }
                                return (Config::response($res, 'response/message', 'Could not get a match of your age, try updating your date of birthday.'));
                            }
                        }
                    }else
                        return (Config::response($res, 'response/message', 'Could not find a match for you, of your current region'));
                }
            }
            return (Config::response($res, 'response/message', 'Could not find a match for you'));
        }

        public function get_suggestions($session){
            $res = Config::get('response_format');

            $user = (object)self::info(array('token' => $session));
            if (isset($user->response) && $user->response['state'] == 'true'){
                $user = (object)$user->data;
                $res = self::suggestions($user->id);
                if ($res['response']['state'] == 'true'){
                    $final = array();
                    $suggestions = array();
                    $res_without_interests = $res['data'];
                    $res_with_interests = self::suggestions($user->id, true);               
                    
                    if ($res_with_interests['response']['state'] == 'true')
                        $final = $res_with_interests['data'];
                        
                    foreach ($res_without_interests as $element){
                        if (!in_array($element, $final)){
                            $final[] = $element;
                        }
                    }

                    foreach ($final as $element){
                        $data = self::info(array('id' => $element));
                        if ($data['response']['state'] === 'true'){
                            $block = self::is_blocked($user->id, $data['data']['id']);
                            if (isset($block['response']) && $block['response']['state'] == 'true')
                                $data['data']['bloked_user'] = 'true';
                            $suggestions[] = $data['data'];
                        }
                    }
                    if (count($suggestions) > 0)
                        $res = Config::response($res, 'data', $suggestions);
                }
                return ($res);
                return ;
            }
            return (Config::response($res, 'response/message', 'no data'));
        }

        public function generate_new_token($user_id, $username, $new_email, $is_forgotpassword = 0){
            $res = Config::get('response_format');
            new Database();
            $where = array(
                'email', '=', $new_email
            );

            if ($is_forgotpassword){
                $token = Hash::unique_key(6);
                $input = array(
                    'token' => $token
                );

                if (ft_sendmail($new_email, '', 'Forgot Password Verification Token', ft_ms_verify_token($username, $token))){
                    $where = array(
                        'id', '=', $user_id
                    );

                    if (parent::update('tbl_users', $input, $where)){
                        $res = Config::response($res, 'response/state', 'true');
                        return (Config::response($res, 'response/message', 'Sccess'));
                    }
                }else
                    return (Config::response($res, 'response/message', 'Could not send verification token'));
            }else{
                if (($user = parent::select('tbl_users', $where, null, true))){
                    if (!$user->rows){
                        $token = Hash::unique_key(6);
                        $input = array(
                            'token' => $token
                        );

                        if (ft_sendmail($new_email, '', 'Verification Token', ft_ms_verify_token($username, $token))){
                            $where = array(
                                'id', '=', $user_id
                            );
                            
                            if (parent::update('tbl_users', $input, $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                return (Config::response($res, 'response/message', 'Sccess'));
                            }
                        }else
                            return (Config::response($res, 'response/message', 'Could not send verification token'));
                    }else
                        return (Config::response($res, 'response/message', 'Email already registered'));
                }
            }
            return (Config::response($res, 'response/message', 'Could not generate new token'));
        }

        public function change_email($user_id, $token, $new_email){
            $res = Config::get('response_format');
            new Database();
            $where = array(
                'id', '=', $user_id
            );

            if (($user = parent::select('tbl_users', $where, null, true))){
                if ($user->rows){
                    $user = (object)$user->rows[0];
                    if ($user->token === $token){
                        $input = array(
                            'email' => $new_email,
                            'token' => ''
                        );

                        if (parent::update('tbl_users', $input, $where)){
                            $res = Config::response($res, 'response/state', 'true');
                            return (Config::response($res, 'response/message', 'Sccess'));
                        }
                    }else
                        return (Config::response($res, 'response/message', 'User tokens do not match'));            
                }
            }
            return (Config::response($res, 'response/message', 'Could not change email'));
        }

        public function report($session, $user, $report_to, $desc){
            $res = Config::get('response_format');
            new Database();

            $report_to_user = (object)self::info(array('id' => $report_to));
            if (isset($report_to_user->response) && $report_to_user->response['state'] == 'true'){
                $report_to_user = (object)$report_to_user->data;
                
                $where = array(
                    'user_id_from', '=', $user->id,
                    'AND',
                    'user_id_to', '=', $report_to_user->id
                );
                if (($block = parent::select('tbl_user_report', $where, null, true))){
                    if ($block->rowCount >= 3){
                        return (Friends::block($session, $report_to_user->id));
                    }else{
                        $input = array(
                            'user_id_from' => $user->id,
                            'user_id_to' => $report_to_user->id,
                            'description' => $desc
                        );
        
                        if (parent::insert('tbl_user_report', $input)){
                            ft_sendmail($report_to_user->email, ucwords($report_to_user->firstname . ' ' . $report_to_user->lastname), Config::get('app/name') . " - Matcha - User Report", ft_ms_report_user($report_to_user->username, $user->username));
                            $res = Config::response($res, 'response/state', 'true');
                            return (Config::response($res, 'response/message', $report_to_user->username.' was successfully reported'));
                        }
                    }
                }
            }
            return (Config::response($res, 'response/message', 'Could not report user at this time'));
        }

        public function is_blocked($user_id, $viewed_user_id, $rev = 0){
            $res = Config::get('response_format');
            new Database();
            $where = array();

            if (!$rev){
                $where = array(
                    'user_id_from', '=', $user_id,
                    'AND',
                    'user_id_to', '=', $viewed_user_id
                );
            }else{
                $where = array(
                    'user_id_from', '=', $viewed_user_id,
                    'AND',
                    'user_id_to', '=', $user_id
                );
            }
            if (($blocked = parent::select('tbl_user_block', $where, null, true))){
                if ($blocked->rowCount){
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'User is blocked');
                    return (Config::response($res, 'data', 'true'));
                }
            }
            return (Config::response($res, 'response/message', 'Could not check if user is blocked'));
        }

        private function filter_interests($user_id, $other_id){
            $query = "SELECT id as 'user_id', username FROM tbl_users WHERE id IN (SELECT tbl_users.id as 'user_id' FROM tbl_users, tbl_interests, tbl_user_interests WHERE tbl_users.id = tbl_user_interests.user_id AND tbl_user_interests.interest_id = tbl_interests.id AND tbl_interests.id IN (SELECT tbl_interests.id FROM tbl_users, tbl_interests, tbl_user_interests WHERE tbl_users.id = tbl_user_interests.user_id AND tbl_user_interests.interest_id = tbl_interests.id AND tbl_users.id = $user_id)) AND id = $other_id;";
            
            if (($data = parent::rawQuery($query, true))){
                $data = (object)$data;
                if ($data->rowCount > 0){
                    $data = $data->rows[0];
                    return ($data);
                }
            }
            return (false);
        }

        private function filter_sex($gender, $sexual_preference, $otheruser_gender){
            if ($sexual_preference == 'male'){
                if ($otheruser_gender == 'male' || $otheruser_gender == 'none')
                    return (true);
                else
                    return (false);
            }
            else if ($sexual_preference == 'female'){
                if ($otheruser_gender == 'female' || $otheruser_gender == 'none')
                    return (true);
                else
                    return (false);
            }
            else{
                return (true);
            }
        }

        private function age_match($user_id, $user_dob, $id, $age){
            $query = "SELECT username, id FROM `tbl_users` WHERE date_of_birth BETWEEN DATE_ADD('$user_dob', INTERVAL -$age YEAR) AND DATE_ADD('$user_dob', INTERVAL $age YEAR) AND id = $id AND id != $user_id;";
            //$query = "SELECT username, id FROM `tbl_users` WHERE date_of_birth BETWEEN DATE_ADD('$user_dob', INTERVAL -$age YEAR) AND DATE_ADD('$user_dob', INTERVAL $age YEAR);";
            
            if (($data = parent::rawQuery($query, true))){
                $data = (object)$data;
                if ($data->rowCount > 0){
                    $data = $data->rows;
                    return ($data);
                }
            }
            return (false);
        }

        private function city($id, $address){
            if ($address){
                $tmp = explode(',', $address);
                if (count($tmp) > 0){
                    foreach ($tmp as $element){
                        if (($city = is_city($element)))
                            return ($city);
                    }

                    if (isset($tmp[count($tmp) - 1]))
                        return ($tmp[count($tmp) - 1]);
                }
            }else{
                if (($locations = parent::select('tbl_user_locations', array('user_id', '=', $id), 'ORDER BY date_created DESC LIMIT 1', true))){
                    if ($locations->rowCount > 0){
                        $locations = (object)$locations->rows[0];
                        $location = (object)json_decode(html_entity_decode($locations->location), true);
                        $ret = array();
                        if (isset($location->time_zone)){
                            $tmp = explode('/', $location->time_zone);
                            if (count($tmp) > 1)
                                $ret[] = $tmp[1];
                            else
                                $ret[] = $tmp[0];
                        }
                        if (isset($location->country_name))
                            $ret[] = $location->country_name;
                        
                        if (count($ret) > 0){
                            foreach ($ret as $element){
                                if (($city = is_city($element)))
                                    return ($city);
                            }
                            return ($ret[0]);
                        }
                    }
                }
            }
            return (false);
        }
    }
?>