<?php
    class Friends extends Database{
        public function suggestions($user_id){
            $error = 'Could\' not get suggestions at this time, please try again in few minutes';
            $res = Config::get('response_format');
            new Database();

            return (Config::response($res, 'response/message', $error));
        }

        public function search($value){
            $error = 'Could\' not get search results';
            $res = Config::get('response_format');
            $db = new Database();
            $conn = $db->connection();

            $query = "SELECT * FROM tbl_users WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ?;";
            $params = array("$value%", "$value%", "$value%");
            $stmt = $conn->prepare($query);

            if (!$stmt->execute($params))
                return (Config::response($res, 'response/message', $error));
            if (($rows = (array)parent::getRows($stmt, false))){                
                $data = $rows;
                $i = 0;
                foreach($data as $user){
                    if (($images = $db->select('tbl_user_images', array('user_id', '=', $user['id']), null, true))){
                        if ($images->rowCount > 0){
                            
                            foreach ($images->rows as $image){
                                if ($image['code'] == 1)
                                    $data[$i]['img_url'] = $image['url'];
                            }
                        }
                    }
                    $data[$i]['visits'] = User::visits($user['id']);
                    $i++;
                }

                $res = Config::response($res, 'data', $data);
            }
            $res = Config::response($res, 'response/state', 'true');
            $res = Config::response($res, 'response/message', 'Search success');
            return ($res);
            return (Config::response($res, 'response/message', $error));
        }

        public function advanced_search($input){
            $res = Config::get('response_format');
            $age_min = self::ages(true);
            $age_max = self::ages(false);
            $fame_min = self::fames(true);
            $fame_max = self::fames(false);
            $sort = "ASC";
            $order_by = "age";
            $location = '';
            
            if (isset($input['age_min']) && $input['age_min'])
                $age_min = $input['age_min'];
            if (isset($input['age_max']) && $input['age_max'])
                $age_max = $input['age_max'];
            if (isset($input['fame_min']) && $input['fame_min'])
                $fame_min = $input['fame_min'];
            if (isset($input['fame_max']) && $input['fame_max'])
                $fame_max = $input['fame_max'];
            
            if (isset($input['sort_by'])){
                if ($input['sort_by'] === 'ASC' || $input['sort_by'] == 'DESC')
                    $sort = $input['sort_by'];
            }
            if (isset($input['order_by'])){
                if ($input['order_by'] === 'age' || $input['order_by'] === 'fame' || $input['order_by'] === 'location' || $input['order_by'] === 'tags')
                    $order_by = $input['order_by'];
            }
            if (isset($input['location']))
                $location = $input['location'];

            //age
            //SELECT id FROM tbl_users WHERE ((COALESCE(DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(tbl_users.date_of_birth)), '%Y')+0, 0)) >= 1 AND (COALESCE(DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(tbl_users.date_of_birth)), '%Y')+0, 0)) <= 99) AND address LIKE "%%"
            //fame
            //SELECT id as 'user_id' FROM tbl_users tbl WHERE ((SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = tbl.id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history) >= 3 AND (SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = tbl.id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history) <= 50) AND address LIKE "%%"

            $query = "SELECT (SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = tbl.id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history) as 'fame', id as 'user_id', (COALESCE(DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(tbl.date_of_birth)), '%Y')+0, 0)) as 'age' FROM tbl_users tbl WHERE ((SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = tbl.id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history) >= $fame_min AND (SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = tbl.id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history) <= $fame_max) AND ((COALESCE(DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(tbl.date_of_birth)), '%Y')+0, 0)) >= $age_min AND (COALESCE(DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(tbl.date_of_birth)), '%Y')+0, 0)) <= $age_max) AND address LIKE '%$location%' ORDER BY $order_by $sort;";
            //echo "<h3>$query</h3>";
            if (($data = parent::rawQuery($query, true))){
                $data = (object)$data;
                if ($data->rowCount){
                    $data = $data->rows;
                    $user_data = array();

                    foreach ($data as $user){
                        $info = User::info(array('id' => $user['user_id']));
                        if (isset($info['response']) && $info['response']['state'] === 'true'){
                            $user_data[] = $info['data'];
                        }
                    }

                    if (count($user_data) > 0){
                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'Search success');
                        $res = Config::response($res, 'data', $user_data);
                        return ($res);
                    }
                }
            }            
            //echo "$age_min - $age_max; $fame_min - $fame_max; $order_by - $sort<br>";
            return (Config::response($res, 'response/message', 'no data'));
        }

        public function invite($user_session, $to_id){
            $error = 'Could not invite user.';
            $res = Config::get('response_format');
            new Database();

            $user_from_info = User::info(array('token' => $user_session));
            if ($user_from_info['response']['state'] === 'true'){
                $user_from_info = (object)$user_from_info['data'];
                $where = array(
                    'user_id_from', '=', $user_from_info->id,
                    'AND',
                    'user_id_to', '=', $to_id 
                );
                if (($data = parent::select('tbl_user_connections', $where, null, true))){
                    $inputHistory = array(
                        'user_id_from' => $user_from_info->id,
                        'user_id_to' => $to_id
                    );

                    if ($data->rowCount == 0){
                        //Users have no connection, add it
                        $input = array(
                            'user_id_from' => $user_from_info->id,
                            'user_id_to' => $to_id
                        );

                        if (parent::insert('tbl_user_connections', $input)){
                            $inputHistory['action'] = 'connect';
                            parent::insert('tbl_user_history', $inputHistory);

                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'connected');
                            return ($res);
                        }
                    }else{
                        //Users have connection... so unconnect them
                        $users_friendship = (object)$data->rows[0];
                        
                        if ($users_friendship->status == 0){
                            //Remove it connection...
                            //echo 'Delete<br><br>';
                            if (parent::delete('tbl_user_connections', $where)){
                                $inputHistory['action'] = 'unconnect';
                                parent::insert('tbl_user_history', $inputHistory);

                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'unconnected');
                                return ($res);
                            }else
                                return (Config::response($res, 'response/message', "Could not unconnect with user"));
                        }else{
                            //Update connection status...
                            //echo 'Update<br><br>';
                            $updates = array(
                                'status' => 0
                            );

                            if (parent::update('tbl_user_connections', $updates, $where)){
                                $inputHistory['action'] = 'unconnect';
                                parent::insert('tbl_user_history', $inputHistory);

                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'unconnected');
                                return ($res);
                            }else
                                return (Config::response($res, 'response/message', "Could not unconnect with user"));
                        }
                    }
                }
            }else
                return (Config::response($res, 'response/message', $error.' Incorrect user session.'));

            return (Config::response($res, 'response/message', $error));
        }

        public function accept_invite($user_session, $from_id){
            $error = 'Could not accept invite';
            $res = Config::get('response_format');
            new Database();

            $user_from_info = User::info(array('token' => $user_session));
            if ($user_from_info['response']['state'] === 'true'){
                $user_from_info = (object)$user_from_info['data'];
                $query = "SELECT * FROM tbl_user_connections WHERE (user_id_from = $user_from_info->id AND user_id_to = $from_id) OR (user_id_from = $from_id AND user_id_to = $user_from_info->id);";

                //if (($data = parent::select('tbl_user_connections', $where, null, true))){
                if (($data = parent::rawQuery($query, true))){
                    $data = (object)$data;
                    if ($data->rowCount > 0){
                        $data = (object)$data->rows[0];
                        $where = array(
                            'id', '=', $data->id
                        );
                        
                        if ($data->status == 0){
                            //No connection... make connection
                            $input = array(
                                'status' => 1
                            );

                            if (parent::update('tbl_user_connections', $input, $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'connected');
                                return ($res);
                            }
                        }else{
                            //Connection... remove connection
                            $input = array(
                                'status' => 0
                            );

                            if (parent::delete('tbl_user_connections', $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'unconnected');
                                return ($res);
                            }
                        }
                    }else
                        return (Config::response($res, 'response/message', 'User has not sent you a connection'));
                }
            }

            return (Config::response($res, 'response/message', $error));
        }

        public function _list($user_session){
            $error = 'Could not get connetion list.';
            $res = Config::get('response_format');
            new Database();

            $user_from_info = User::info(array('token' => $user_session));
            if (isset($user_from_info['response']) && $user_from_info['response']['state'] === 'true'){
                $user_from_info = (object)$user_from_info['data'];

                $query = "SELECT *, CAST(tbl_users.id AS UNSIGNED) AS 'user_id' 
                            FROM tbl_users, tbl_user_connections 
                            WHERE tbl_user_connections.status = 1 AND (user_id_from = tbl_users.id || user_id_to = tbl_users.id) AND (user_id_from = $user_from_info->id || user_id_to = $user_from_info->id) ORDER BY username;";
                //FROM tbl_user_images WHERR tbl_user_images.id = tbl_users.id AND tbl_user_images.code = 1 AND
                if (($data = parent::rawQuery($query, true))){
                    $data = (object)$data;
                    if ($data->rowCount > 0){
                        $new_data = array();

                        foreach ($data->rows as $d){     
                            $query = "SELECT url FROM tbl_user_images, tbl_users WHERE tbl_user_images.user_id = tbl_users.id AND tbl_users.id = ". $d['user_id'] ." AND tbl_user_images.code = 1;";
                            if (($data_imgs = parent::rawQuery($query, true))){
                                $data_imgs = (object)$data_imgs;
                                if ($data_imgs->rowCount > 0){
                                    $data_imgs = (object)$data_imgs->rows[0];
                                    $d['profile_url'] = $data_imgs->url;
                                }
                            }
                            
                            $block = User::is_blocked($user_from_info->id, $d['user_id'], 1);
                            if (isset($block['response']) && $block['response']['state'] == 'true'){
                                $d['bloked_user'] = 'true';
                            }
                            $new_data[] = $d;
                        }

                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', $data->rowCount);
                        $res = Config::response($res, 'data', $new_data);
                        return ($res);
                    }else
                        return (Config::response($res, 'response/message', 'No connections yet'));
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function block($session, $id){
            $error = 'Could\' not block user, please try again in few minutes';
            $res = Config::get('response_format');

            if (($data = User::info(array('token' => $session)))){
                if (isset($data['response']) && $data['response']['state'] === 'true'){
                    $user = (object)$data['data'];
                    $where = array(
                        'user_id_from', '=', $user->id,
                        'AND',
                        'user_id_to', '=', $id
                    );

                    if (($block = parent::select('tbl_user_block', $where, null, true))){
                        if (!$block->rowCount){
                            $input = array(
                                'user_id_from' => $user->id,
                                'user_id_to' => $id
                            );
                            if (parent::insert('tbl_user_block', $input)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'data', array('blocked' => 'true'));
                                return (Config::response($res, 'response/message', 'User successfully blocked'));
                            }
                        }else
                            return (Config::response($res, 'response/message', 'User already blocked'));
                    }
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function unblock($session, $id){
            $error = 'Could\' not block user, please try again in few minutes';
            $res = Config::get('response_format');

            if (($data = User::info(array('token' => $session)))){
                if (isset($data['response']) && $data['response']['state'] === 'true'){
                    $user = (object)$data['data'];
                    $where = array(
                        'user_id_from', '=', $user->id,
                        'AND',
                        'user_id_to', '=', $id
                    );

                    if (($block = parent::select('tbl_user_block', $where, null, true))){
                        if ($block->rowCount){
                            $where = array(
                                'user_id_from', '=', $user->id,
                                'AND',
                                'user_id_to', '=', $id
                            );

                            parent::delete('tbl_user_report', $where);
                            if (parent::delete('tbl_user_block', $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                return (Config::response($res, 'response/message', 'User successfully unblocked'));
                            }
                        }else
                            return (Config::response($res, 'response/message', 'You haven\'t blocked this user'));
                    }
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        private function ages($min = true){
            $query = "SELECT id, COALESCE(DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(tbl_users.date_of_birth)), '%Y')+0, 0) AS 'age' FROM tbl_users ORDER BY age ASC;";

            if (($data = parent::rawQuery($query, true))){
                $data = (object)$data;
                if ($data->rowCount){
                    $data = $data->rows;
                    if ($min)
                        return ($data[0]['age']);
                    return ($data[count($data) - 1]['age']);
                }
            }
            return (0);
        }

        private function fames($min = true){
            $query = "SELECT username, id as 'user_id', (SELECT COALESCE(((SELECT COUNT(tbl_user_history.id) FROM tbl_user_history WHERE tbl_user_history.user_id_to = user_id) / COUNT(tbl_user_history.id)) * 100, 0) as 'count' FROM tbl_user_history) as 'fame' FROM tbl_users ORDER BY fame ASC;";

            if (($data = parent::rawQuery($query, true))){
                $data = (object)$data;
                if ($data->rowCount){
                    $data = $data->rows;
                    if ($min)
                        return (number_format($data[0]['fame'], 2, '.', ''));
                    return (number_format($data[count($data) - 1]['fame'], 2, '.', ''));
                }
            }
            return (0);
        }
    }
?>