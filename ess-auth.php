<?php 
    /*
    ** Version : 1.0.0
    ** Create by : Benz.Surachai
    */

    header('Access-Control-Allow-Origin: *');
    header("Content-Type:application/json");

    require( dirname( __FILE__ ) . '/wp-load.php' );
    
    define("_token","#");
    $req_data = json_decode(file_get_contents('php://input'), true);
    
    global $wpdb;
    $obj = new ESSlogno;
    $obj_token_status = $obj->validateToken( $req_data['req_token'] ); // Validate Token from Client
    $obj_token = json_decode( $obj->validateToken( $req_data['req_token'] ) ); // Validate Token from Client
    $obj_name = json_decode( $obj->validateName( $req_data['username'] ) );
    $obj_email = json_decode( $obj->validateEmail( $req_data['email'] ) );
    $obj_pass = json_decode( $obj->validatePassword( $req_data['pass'] ) );

    if($obj_token->Status == 200) { 
        if($obj_name->Status == 200 && $obj_email->Status == 200 && $obj_pass->Status == 200 ) { 

            /* Register New User */
            $force_regis = register_new_user($req_data['username'], $req_data['email'], $req_data['pass']);

            if( ! $force_regis ){
                $res = array('Status' => 404, 'error_desc' => 'Can\'t register!! Something went wrong.');
                echo json_encode($res);
            }else{
                
                $result = $wpdb->get_results("select * from # where user_email = '".$req_data['email']."' order by user_registered desc limit 1");
                $result_check = $wpdb->insert( '#', array(
                    'user_name' => $req_data['username'],
                    'password' => $result[0]->user_pass,
                    'email' => $req_data['email'],
                    'member_since' => date('Y-m-d'),
                    'membership_level' => '2',
                    'phone' => $req_data['phone'],
                    'country' => 'Thailand',
                    'gender' => $req_data['gender'],
                    'account_state' => $req_data['account_state'],
                    'subscription_starts' => date('Y-m-d'),
                    'last_accessed' => date('Y-m-d'),
                    'last_accessed_from_ip' => '8.8.8.8') );
                
                if($result_check){
                    $res = array('Status' => 200, 'error_desc' => 'Success');
                    echo json_encode($res);
                }else{
                    $res = array('Status' => 404, 'error_desc' => 'Something went wrong');
                    echo json_encode($res);
                }
                
            }

        } else {
            $res = array('Status' => 201, 'error_desc' => 'Username or Email has been register !!');
            echo json_encode($res);
        }

    } else {
        echo $obj_token_status;
    }

    /*
    ** Class Authorization
    */
    class ESSlogno {
        public function validateName($username) {
            if ( $username == '' ) {
                $error_txt = 'Please enter a username.';
                $Status = 404;
            } elseif ( ! validate_username( $username ) ) {
                $error_txt = 'This username is invalid because it uses illegal characters. Please enter a valid username.';
                $Status = 404;
            } elseif ( username_exists( $username ) ) {
                $error_txt = 'This username is already registered. Please choose another one.';
                $Status = 404;
            } else {
                $Status = 200;
            }

            $res = array('Status' => $Status, 'error_desc' => $error_txt);
            return json_encode($res);

        }

        public function validateEmail($email) {
            if ( $email == '' ) {
                $error_txt = 'Please type your email address.';
                $Status = 404;
            } elseif ( ! is_email( $email ) ) {
                $error_txt = 'The email address isn\'t correct';
                $Status = 404;
            } elseif ( email_exists( $email ) ) {
                $error_txt = 'This email is already registered, please choose another one.';
                $Status = 404;
            } else {
                $Status = 200;
            }

            $res = array('Status' => $Status, 'error_desc' => $error_txt);
            return json_encode($res);

        }

        public function validatePassword($pass) {
            if ( $pass == '' ) {
                $error_txt = 'Please enter a password.';
                $Status = 404;
            } else {
                $Status = 200;
            }
            $res = array('Status' => $Status, 'error_desc' => $error_txt);
            return json_encode($res);
        }

        public function validateToken($req_token) {
            if( constant('_token') == $req_token ) {
                $res = array('Status' => 200);
            } else { 
                $res = array('Status' => 404, 'error_desc' => 'Can\'t access to server. You token not match.');
            }
            return json_encode($res);
        }

    }
    
?>