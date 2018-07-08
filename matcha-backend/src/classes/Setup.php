<?php
    class Setup extends Database {
        public function database(){
            $res = Config::get('response_format');

            if (($server = parent::server_connection())){
                $query = "CREATE DATABASE IF NOT EXISTS ". Config::get('server/db_name') .";";
                if (($stmt = parent::rawQuery($query, false, $server))){
                    $res = Config::response($res, 'response/state', "true");
                    return (Config::response($res, 'response/message', "Database created"));
                }
            }
            return (Config::response($res, 'response/message', "Connection error"));
        }

        public function tables(){
            new Database();
            $res = Config::get('response_format');
            $table_names = Config::get('setup_formats/table_names');
            $table_queries = Config::get('setup_formats/table_queries');
            
            $i = 0;
            foreach ($table_queries as $el){
                $query = "CREATE TABLE IF NOT EXISTS `$table_names[$i]` ($el);";
                if (!parent::rawQuery($query)){
                    return (Config::response($res, 'response/message', "Error creating table: `$table_names[$i]`)"));
                }
                $i++;
            }
            $res = Config::response($res, 'response/state', "true");
            return (Config::response($res, 'response/message', "All tables created successfully"));
        }

        public function populate_database(){
            new Database();
            $res = Config::get('response_format');
            $res = Config::get('response_format');
            $query = "INSERT INTO `tbl_users`(`username`, `email`, `password`, `firstname`, `lastname`, `gender`, `date_of_birth`, `sexual_preference`, `biography`, `address`, `token`, `salt`) VALUES"
                        ."('nrarane', 'lonwaborarne@gmail.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Nyameko', 'Rarane', 'male', '1994-03-12', 'female', 'I am groot', '54 Mentz street, Booysens, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('agirlhasnoname', 'lonwaborarane@gmail.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Arya', 'Stark', 'female', '2000-05-25', 'male', 'A girl has no name', '21 Mentz street, Booysens, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('goldenhand', 'jaime@golden.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Jaime', 'Lannister', 'male', '1989-07-11', 'female', 'I am golden', '81 Keyes Ave, Rosebank, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('cersei', 'cersei@queenin.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Cersei', 'Lannister', 'female', '1991-12-01', 'male', 'Do I even exist', '21 Keyes Ave, Rosebank, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('khaleesi', 'khaleesi@dracarys.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Daenerys', 'Targaryen', 'female', '1995-01-17', 'male', 'I am queen', '51 Main street, Rosettenville, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('jonsnow', 'jon@bastards.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Jon', 'Snow', 'male', '1992-04-13', 'female', 'This is jon snow', '3 Chamber street, Booysens, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('ramsay', 'ramsay@dogfood.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Ramsay', 'Bolton', 'male', '1997-03-12', 'female', 'Whatever', '54 Melville street, Booysens, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('missandei', 'missi@missy.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Missandei', 'Rarane', 'female', '1998-08-12', 'male', 'I am out of ideas', '54 9th street, Linksfield, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('melisandre', 'mel@redlady.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Melisandre', 'Unknown', 'female', '1980-01-13', 'male', 'I am not groot', '39 Rissik street, Marshalltown, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('naledi', 'nrarane@student.wethinkcode.co.za', 'e4eb522a1a75eba7ddce63ba2bca1cfd0d11f2b46d94cc03c663105ebe9c75f7b4a67dfe34edd9e8c13eb9880fe8230bca446b6198deb3951493a9101eae90b1', 'Naledi', 'Something', 'female', '1986-07-01', 'male', 'I am good', '30-4 Inver Ave, Crosby, Johannesburg, South Africa', '', 'iGPeRwgWApo5RQB')";
            
            if (($check = parent::select("tbl_users", null, null, true))){
                if (!$check->rowCount){
                    if (!parent::rawQuery($query))
                        return (Config::response($res, 'response/message', "Error populating records"));
                }
            }
            $res = Config::response($res, 'response/state', "true");
            return (Config::response($res, 'response/message', "Population success"));
        }

        public function all(){
            $res = Config::get('response_format');
            $create_db = (object)self::database()['response'];
            
            if ($create_db->state == "true"){
                $create_tables = self::tables()['response'];

                if ($create_db->state == "true"){
                    $res = Config::response($res, 'response/state', "true");
                    return (Config::response($res, 'response/message', "Database OK"));
                }
            }
            return (Config::response($res, 'response/message', "Could not create database"));
        }
        
        public function re(){
            $res = Config::get('response_format');

            if (($server = parent::server_connection())){
                $query = "DROP DATABASE IF EXISTS ". Config::get('server/db_name') .";";

                if (parent::rawQuery($query, false, $server)){
                    $create_all = (object)self::all()['response'];

                    if ($create_all->state == "true"){
                        $res = Config::response($res, 'response/state', "true");
                        return (Config::response($res, 'response/message', "Database re-created"));
                    }
                    return ("{}");
                }
            }
            return (Config::response($res, 'response/message', "Could not re-create database"));
        }
    }
?>
