<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    header('Access-Control-Allow-Origin: *');
    /* */
    header('Access-Control-Allow-Methods: *');
    header('Content-Type: application/json');
    /* */

    require '../vendor/autoload.php';

    require '../config/config.php';
    require '../src/functions/init.php';
    require '../src/classes/init.php';
    
    $app = new \Slim\App;
    $app->get('/test', function (Request $request, Response $response) {
        //echo date('Y');
        echo ft_get_age('2000-02-20');

        /*try{
            $res = Friends::invite('qRSBY1Y6xYojnoyjvXq8aevu5qhLqTiRLasJcrQJpf2MEB69ywhILMdQduCDoUu95XThoJ1NZ3ADFHMdd4WZ', 2);
            echo json_encode($res);
            
            //print_r(Config::get('response_format/response'));
            /*$ret = Config::response(Config::response(), 'response/state', 'true');
            print_r(Config::response($ret, 'response/message', 'Ok... test works'));*\/
        }catch(Exception $exc){
            echo $exc->getMessage();
        }*/
    });

    $app->get('/profile', function (Request $request, Response $response) {
        $input = ft_escape_array($request->getParsedBody());        
        //$input['session'] = 'SZ28FctI8N9ktW4efDIQhAScMdGEfyE7yGgeTJW1x5l01qeh4X7mx0yjefYtODAnkRBdBxf8CA9E1nV6l3sT';

        if (isset($input['username'])){
            $db = new Database();

            //run raw query "WHERE blocked_user.id != id..."
            if (($data = $db->select('tbl_users', array('username', '=', $input['username']), null, true))){
                if ($data->rowCount){
                    $data = $data->rows[0];
                    //$images_data = ''; //#Remove...!

                    //Appending users Photos on user's info...
                    //echo $query = "SELECT * FROM tbl_user_images WHERE user_id = ".$data['id'].";";
                    if (($images = $db->select('tbl_user_images', array('user_id', '=', $data['id']), null, true))){
                        if ($images->rowCount > 0){
                            $data['images'] = $images->rows;
                            
                            foreach ($images->rows as $image){
                                $data['img'.$image['code']] = $image;
                            }
                        }
                    }

                    //Appending Friendship info of logged user with viewed user if not him/her self (logged user)...
                    if (isset($input['session'])){
                        if ($logged_user = User::info(array('token' => $input['session']))){
                            $logged_user = (object)$logged_user['data'];
                            $viewed_user = (object)$data;

                            //echo "Here";
                            if ($logged_user->id !== $viewed_user->id){
                                $query = "SELECT * FROM tbl_user_connections WHERE (user_id_from = $logged_user->id AND user_id_to = $viewed_user->id) OR (user_id_from = $viewed_user->id AND user_id_to = $logged_user->id);";

                                //echo "Here 1";
                                if (($conn_data = Database::rawQuery($query, true))){
                                    //echo "Here 2";
                                    $conn_data = (object)$conn_data;
                                    if ($conn_data->rowCount > 0){
                                        //echo "Here 3";
                                        $conn_data = (object)$conn_data->rows[0];
                                        $relationship['status'] =  $conn_data->status;
                                        $relationship['user_id_from'] = $conn_data->user_id_from;
                                        $relationship['user_id_to'] = $conn_data->user_id_to;
                                        
                                        $data['relationship'] = $relationship;

                                    }
                                }
                            }

                            if ($logged_user->username !== $input['username']){
                                $where = array(
                                    'user_id_from', '=', $logged_user->id,
                                    'AND',
                                    'user_id_to', '=', $viewed_user->id,
                                    'AND',
                                    'action', '=', 'visit'
                                );
                                //if (($views = Database::select('tbl_user_history', $where, null, true))){
                                    //if ($views->rowCount == 0){
                                        $inputHistory = array(
                                            'user_id_from' => $logged_user->id,
                                            'user_id_to' => $viewed_user->id,
                                            'action' => 'visit'
                                        );
                                        Database::insert('tbl_user_history', $inputHistory);
                                    //}
                                //}
                            }
                            
                            $data['visits'] = User::visits($viewed_user->id);
                            if (($visit_data = User::get_visits($viewed_user->id)))
                                $data['visits_data'] = $visit_data;
                            if (($likes = User::get_likes($viewed_user->id)))
                                $data['likes_data'] = $likes;

                            $tags = User::tags($viewed_user->id);
                            if (isset($tags['response']) && $tags['response']['state'] == 'true')
                                $data['tags'] = $tags['data'];

                            $block = User::is_blocked($logged_user->id, $viewed_user->id);
                            if (isset($block['response']) && $block['response']['state'] == 'true')
                                $data['blocked_user'] = $block['data'];
                        }
                    }

                    $res = Config::response(Config::response(), 'response/state', 'true');
                    $res = Config::response($res, 'data', $data);
                    echo json_encode($res);
                    return ;
                }
                echo json_encode(Config::response(Config::response(), 'response/message', $input['username'] .' was not found.'));
                return ;
            }
        }
        echo '{}';
    });

    $app->get('/suggestions', function (Request $request, Response $response) {
        try{
            $input = ft_escape_array($request->getParsedBody());

            print_r($input);
            //$res = friends::suggestions(2);
            //echo json_encode($res);
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    $app->get('/info', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['session'] = '8ZJm6D3WrBFTyktNzcUqIFcPxFH0Q3vY2fk8So4k0Kdz4HmETh4CWhruW8uMc0Lef6n8vstwcH5horViI0UF';

        if (isset($input['session'])){
            $res = User::info(array('token' => $input['session']));
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/update-profile', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['session']) && isset($input['fname']) && isset($input['lname']) && isset($input['gender']) &&
                isset($input['dob']) && isset($input['sexual_preference']) && isset($input['bio'])){

            $res = User::update_profile($input['session'], $input['fname'], $input['lname'], $input['gender'], $input['dob'], $input['sexual_preference'], $input['bio'], $input['address']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/login', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //echo ">> ";
        //print_r($input);
        // $input['isSession'] = 1;
        // $input['session'] = "Pphb5JauOBmuxP5dXwKtqKwaSe9WoxDdXVp2FzwtbTYIJ3WGAHa0rGakUjgjRUwOQVRvvoZGhYp11lHC2SCu";

        if (!isset($input['isSession']))
            echo '{}';
        if ($input['isSession'] == 1){
            if (isset($input['session'])){
                $res = User::info(array('token' => $input['session']));
                
                if (isset($res['response'])){
                    $sugg = User::get_suggestions($input['session']);
                    //if ($sugg['response']['state'] == 'true'){
                    if (isset($sugg['response'])){
                        
                        
                        $suggestions = array();
                        $suggestions['suggestions'] = array(
                            'message' => $sugg['response']['message'],
                            'data' => $sugg['data']
                        );
                        $data = array_merge($res['data'], $suggestions);

                        //$sugg = $sugg['data'];
                        //$data = array_merge($res['data'], array('suggestions' => $sugg));
                        $res['data'] = $data;
                    }
                }
                echo json_encode($res);
            }
        }
        else {
            if (isset($input['login']) && isset($input['password'])){
                $res = User::login($input['login'], $input['password']);
                echo json_encode($res);
            }
        }
    });

    $app->post('/profile-images', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        
        if (isset($input['session']) && isset($input['image']) && isset($input['code'])){
            $res = User::upload_profile($input['session'], $input['image'], $input['code']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/register', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['fname']) && isset($input['lname']) && isset($input['username']) && isset($input['email']) && isset($input['password']) && isset($input['dob'])){
            $res = User::register($input['fname'], $input['lname'], $input['username'], $input['email'], $input['password'], $input['dob']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->get('/logut', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['session'])){
            $res = User::logout($input['session']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/confirm-registration', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        
        if (isset($input['token'])){
            $res = User::confirm_registration($input['token']);
            //$ret = Config::response(Config::response(), 'response/state', 'true');
           // $res = Config::response($ret, 'response/message', 'Ok... test works');
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/search', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['search_value'] = 'luiez';
        
        if (isset($input['search_value'])){
            $res = Friends::search($input['search_value']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/invite', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
        //$input['session'] = 'OeWPVBOI1SfqgEp9UYQjOg4C1hBKeBQ2QMSMoHvqAKRRpg0jeQC26HF8YgSdSIgJv9vUQ0krLciasiuG97Jg';
        //$input['username'] = 'pkaygo';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_to = (object)$data->rows[0];
                    $res = Friends::invite($input['session'], $user_to->id);
                    echo json_encode($res);
                    return ;
                }
            }
            echo json_encode(Config::response(Config::response(), 'response/message', 'Selected user was not found.'));
        }else
            echo '{}';
    });

    $app->post('/accept-invite', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
        //$input['session'] = '0i2ljuJrrJPRSOeo1mJNQzvZg35scXPdgRzAli1M1QEUFTiHf1u6BZ5S3akf89to02YmlZ9nQNhwHAdWCH3d';
        //$input['username'] = 'mkgosisejo';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_to = (object)$data->rows[0];
                    $res = Friends::accept_invite($input['session'], $user_to->id);
                    echo json_encode($res);
                    return ;
                }
            }
            echo json_encode(Config::response(Config::response(), 'response/message', 'Selected user was not found.'));
        }else
            echo '{}';
    });

    $app->get('/friend-list', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['session'] = 'SZ28FctI8N9ktW4efDIQhAScMdGEfyE7yGgeTJW1x5l01qeh4X7mx0yjefYtODAnkRBdBxf8CA9E1nV6l3sT';

        if (isset($input['session'])){
            $res = Friends::_list($input['session']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/block-user', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
        //$input['session'] = '7RfYgKbvKt4ie8u5AFKut4jm7GcKU4O2V30cOcIzGMSUUm0v1KZvPiSWZ4GT8uV4yWgn9YWPKOKFbKadvaIk';
        //$input['username'] = 'mkgosise';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    $res = Friends::block($input['session'], $user->id);
                    echo json_encode($res);
                }
            }
        }else
            echo '{}';
    });

    $app->post('/unblock-user', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
        //$input['session'] = '7RfYgKbvKt4ie8u5AFKut4jm7GcKU4O2V30cOcIzGMSUUm0v1KZvPiSWZ4GT8uV4yWgn9YWPKOKFbKadvaIk';
        //$input['username'] = 'kaygoo';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    $res = Friends::unblock($input['session'], $user->id);
                    echo json_encode($res);
                }
            }
        }else
            echo '{}';
    });

    $app->post('/get-chat', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $db = new Database();
        $res = Config::get('response_format');
        $conn = $db->connection();
         
        if (isset($input['other_id']) && isset($input['user_id'])){
            $query = "SELECT * FROM tbl_user_messages WHERE (user_id_from = :from || user_id_to = :from) AND (user_id_from = :to || user_id_to = :to) ORDER BY date_created DESC;";
            $stmt = $conn->prepare($query);
            $stmt->bindparam(':from', $input['other_id']);
            $stmt->bindparam(':to', $input['user_id']);

            if ($stmt->execute()){
                if ($db->getCount($stmt) > 0){
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'records:'.$db->getCount($stmt));
                    echo json_encode(Config::response($res, 'data', $db->getRows($stmt)));
                    return ;
                }
            }
            echo json_encode(Config::response($res, 'response/message', 'records:0'));
        }else
            echo '{}';
    });

    $app->post('/send-message', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $db = new Database();
        $res = Config::get('response_format');
        $conn = $db->connection();
         
        if (isset($input['from']) && isset($input['to']) && isset($input['mssg'])){
            $input = array(
                'user_id_from' => $input['from'],
                'user_id_to' => $input['to'],
                'message' => $input['mssg']
            );
            
            if ($db->insert('tbl_user_messages', $input)){
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'success');
                echo json_encode($res);
                return ;
            }
            echo json_encode(Config::response($res, 'response/message', 'error'));
        }else
            echo '{}';
    });

    $app->post('/add-tag', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
         
        if (isset($input['tag']) && isset($input['user'])){
            $res = User::add_tag($input['tag'], $input['user']);
            $tags = User::tags($input['user']);
            if ($res['response']['state'] == 'true' && $tags['response']['state'] == 'true')
                $res['data'] = $tags['data'];
            echo json_encode($res);
        }else
            echo '{}';
    });

    /*
    $app->post('/get-tags', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
         
        if (isset($input['user'])){
            $res = User::tags($input['user']);
            echo json_encode($res);
        }else
            echo '{}';
    });
    */

    $app->post('/delete-tag', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $res = Config::get('response_format');
        new Database();
        $where = array(
            'interest_id', '=', $input['id'],
            'AND',
            'user_id', '=', $input['userid']
        );

        if (isset($input['userid']) && isset($input['id'])){
            if (($data = Database::select('tbl_user_interests', $where, null, true))){
                if ($data->rowCount > 0){
                    if (Database::delete('tbl_user_interests', $where)){
                        //Removing tag...
                        $tag = (object)$data->rows[0];
                        $where = array(
                            'interest_id', '=', $tag->interest_id
                        );
                        if (($data = Database::select('tbl_user_interests', $where, null, true))){
                            if ($data->rowCount == 0){
                                $where = array(
                                    'id', '=', $tag->interest_id
                                );
                                Database::delete('tbl_interests', $where);
                            }
                        }

                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'success');

                        $tags = User::tags($input['userid']);
                        if ($res['response']['state'] == 'true' && $tags['response']['state'] == 'true')
                            $res['data'] = $tags['data'];
                        echo json_encode($res);
                        return ;
                    }
                }
                echo json_encode(Config::response($res, 'response/message', 'Tag not found'));
            }
        }else
            echo '{}';
    });

    $app->post('/track-user', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $res = Config::get('response_format');
        //$input['session'] = 'nIh7CcwjIxb3rbr4tk269mT6WXlMceUzWcGzodS6L39cfBhJWjQ5FJcCHyoTMjsJv9jTnc08gakwLBfHV5NB';
        //$input['location'] = "Maf";
        
        if (isset($input['session']) && isset($input['location'])){
            if (($user = User::info(array('token' => $input['session'])))){
                $user = (object)$user;
                if ($user->response['state'] == 'true'){
                    $user = (object)$user->data;
                    $res = User::track($user->id, $input['location']);
                    echo json_encode($res);
                    return ;
                }
            }
            echo json_encode(Config::response($res, 'response/message', 'Could not track user'));
        }else
            echo '{}';
    });

    $app->post('/changepassword', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $res = Config::get('response_format');
        new Database();

        /*
        $input['session'] = '1ljofMSqFjsTqgo4pYKqKGMNqYIsyU9AgsZvfRVVboOCEdG4brvW7hJygs0PmapCCo8Rg4MstB48OLc0cHXk';
        $input['oldp'] = '123456';
        $input['newp'] = '123456';
        */
         
        if (isset($input['session']) && isset($input['oldp']) && isset($input['newp'])){
            $user = (object)User::info(array('token' => $input['session']));
            if ($user->response['state'] == 'true'){
                $user = (object)$user->data;

                $res = User::changepassword($user->username, $input['oldp'], $input['newp']);
                echo json_encode($res);
            }
        }else
            echo '{}';
    });

    $app->get('/get-visits', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
         
        if (isset($input['session'])){
            $user = (object)User::info(array('token' => $input['session']));
            if ($user->response['state'] == 'true'){
                $user = (object)$user->data;

                $data = User::get_visits($user->id);
                if (!$data){
                    echo json_encode(Config::response($res, 'response/message', 'no data'));
                    return ;
                }
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'success');
                $res = Config::response($res, 'data', $data);
                echo json_encode($res);
                return ;
            }
            echo json_encode(Config::response($res, 'response/message', 'no data'));
        }else
            echo '{}';
    });

    $app->get('/get-likes', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $res = Config::get('response_format');
        new Database();
         
        if (isset($input['session'])){
            $user = (object)User::info(array('token' => $input['session']));
            if ($user->response['state'] == 'true'){
                $user = (object)$user->data;

                $data = User::get_likes($user->id);
                if (!$data){
                    echo json_encode(Config::response($res, 'response/message', 'no data'));
                    return ;
                }
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'success');
                $res = Config::response($res, 'data', $data);
                echo json_encode($res);
                return ;
            }
            echo json_encode(Config::response($res, 'response/message', 'no data'));
        }else
            echo '{}';
    });
    
    $app->get('/get-suggestions', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $res = Config::get('response_format');
        new Database();
        //$input['session'] = 'SZ28FctI8N9ktW4efDIQhAScMdGEfyE7yGgeTJW1x5l01qeh4X7mx0yjefYtODAnkRBdBxf8CA9E1nV6l3sT';

        if (isset($input['session'])){
            $res = User::get_suggestions($input['session']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/advanced-search', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['session'] = 'pLd2VjN5gsDT5brXTcVfkyFxeiIDYg0n1epXxd2OFFHLR9ILmE1GdGerYX5Wd5kfjKcQXfEDVmoNw7ySMAzZ';
        //$input['age_min'] = 0;

        if (isset($input['session'])){
            $user = (object)User::info(array('token' => $input['session']));
            if (isset($user->response['state']) && $user->response['state'] == 'true'){
                $res = Friends::advanced_search($input);
                echo json_encode($res);
                return ;
            }
            echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'no data'));
        }else
            echo '{}';
    });

    $app->post('/generate-user-token', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['session'] = '';
        //$input['new_email'] = 'lonwaborarane@gmail.com';

        if (isset($input['session']) && isset($input['new_email'])){
            if (empty($input['session'])){
                $db = new Database();
                $where = array(
                    'email', '=', $input['new_email']
                );
                
                if (($user = $db->select('tbl_users', $where, null, true))){
                    if ($user->rowCount){
                        $user = (object)$user->rows[0];
                        echo json_encode(User::generate_new_token($user->id, $user->username, $user->email, 1));
                    }else
                        echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Email was not found'));
                }else
                    echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Error looking up email'));
            }else{
                $user = (object)User::info(array('token' => $input['session']));
                if (isset($user->response['state']) && $user->response['state'] == 'true'){
                    echo json_encode(User::generate_new_token($user->data['id'], $user->data['username'], $input['new_email']));
                    return ;
                }
            }
        }else
            echo '{}';
    });

    $app->post('/change-email', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        /*$input['session'] = '7RfYgKbvKt4ie8u5AFKut4jm7GcKU4O2V30cOcIzGMSUUm0v1KZvPiSWZ4GT8uV4yWgn9YWPKOKFbKadvaIk';
        $input['token'] = 'gRRQ9w';
        $input['new_email'] = 'test@host.com';*/

        if (isset($input['session']) && isset($input['token']) && isset($input['new_email'])){
            $user = (object)User::info(array('token' => $input['session']));
            if (isset($user->response['state']) && $user->response['state'] == 'true'){
                echo json_encode(User::change_email($user->data['id'], $input['token'], $input['new_email']));
                return ;
            }
        }else
            echo '{}';
    });

    $app->post('/verify-token-forgotpassword', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $db = new Database();
        //$input['token'] = 'nK2PUw';
        //$input['email'] = 'lonwaborarane@gmail.com';

        if (isset($input['token']) && isset($input['email'])){
            $where = array(
                'email', '=', $input['email']
            );

            if (($data = $db->select('tbl_users', $where, null, true))){
                if ($data->rowCount){
                    $user = (object)$data->rows[0];
                    
                    if ($user->token === $input['token']){
                        $data = array(
                            'token' => $input['token'],
                            'email' => $input['email']
                        );

                        $res = Config::response(Config::get('response_format'), 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'Success');
                        echo json_encode(Config::response($res, 'data', $data));
                    }else
                        echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Tokens do not match'));
                }else
                    echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Email: "'. $input['email'] .'", is not registered'));
            }else
                echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Error looking up info'));
        }else
            echo '{}';
    });

    $app->post('/change-forgotpassword', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $db = new Database();
        /*$input['token'] = '5QAHAW';
        $input['password'] = '123456789';
        $input['email'] = 'lonwaborarane@gmail.com';*/

        if (isset($input['token']) && isset($input['email'])){
            $where = array(
                'email', '=', $input['email']
            );

            if (($data = $db->select('tbl_users', $where, null, true))){
                if ($data->rowCount){
                    $user = (object)$data->rows[0];
                    
                    if ($user->token === $input['token']){
                        $salt = Hash::salt(15);
                        $input = array(
                            'salt' => $salt,
                            'password' => Hash::make($input['password'], $salt),
                            'token' => ''
                        );

                        if ($db->update('tbl_users', $input, $where)){
                            $res = Config::response(Config::get('response_format'), 'response/state', 'true');
                            echo json_encode(Config::response($res, 'response/message', 'Success'));
                        }else
                            echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Something went wrong changing password, please try again'));    
                    }else
                        echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Tokens do not match'));
                }else
                    echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Email: "'. $input['email'] .'", is not registered'));
            }else
                echo json_encode(Config::response(Config::get('response_format'), 'response/message', 'Error looking up info'));
        }else
            echo '{}';
    });
    
    $app->post('/report-user', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['session'] = '7RfYgKbvKt4ie8u5AFKut4jm7GcKU4O2V30cOcIzGMSUUm0v1KZvPiSWZ4GT8uV4yWgn9YWPKOKFbKadvaIk';
        //$input['user_id_to'] = 2;
        //$input['desc'] = 'rude...!';
         
        if (isset($input['session'])){
            $user = (object)User::info(array('token' => $input['session']));
            if (isset($user->response) && $user->response['state'] == 'true'){
                $user = (object)$user->data;
                echo json_encode(User::report($input['session'] ,$user, $input['user_id_to'], $input['desc']));
                return ;
            }
        }
        echo '{}';
    });

    $app->get('/setup/{action}', function(Request $request, Response $response){
        $action = ft_escape_str($request->getAttribute('action'));

        if ($action == "db")
                echo json_encode(Setup::database());
        else if ($action == "tables"){
            if (ft_is_response_true($tables = Setup::tables())){
                if (!ft_is_response_true($populate = Setup::populate_database())){
                    echo json_encode($populate);
                    return ;
                }
            }
            echo json_encode($tables);
        }
        else if ($action == "re"){
            if (ft_is_response_true($re = Setup::re())){
                
                if (!ft_is_response_true($populate = Setup::populate_database())){
                    echo json_encode($populate);
                    return ;
                }
            }
            echo json_encode($re);
        }
        else if ($action == "all"){
            if (ft_is_response_true($all = Setup::all())){
                if (!ft_is_response_true($populate = Setup::populate_database())){
                    echo json_encode($populate);
                    return ;
                }
            }
            echo json_encode($all);
        }
    });
    
    $app->run();
?>