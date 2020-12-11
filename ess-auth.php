<?php 
    session_start();
    header('Access-Control-Allow-Origin: https://www.myhr.thanulux.com/');
    require( dirname( __FILE__ ) . '/wp-load.php' );
    define("_token","#");
    
    global $wpdb;
    $obj = new ESSlogno;
    $obj_token = json_decode( $obj->validateToken( $_POST['req_token'] ) ); // Validate Token from Client
    $obj_name = json_decode( $obj->validateName( $_POST['username'] ) );
    $obj_email = json_decode( $obj->validateEmail( $_POST['email'] ) );
    $obj_pass = json_decode( $obj->validatePassword( $_POST['pass'] ) );

    if($obj_token->Status == 200) { 
        if($obj_name->Status == 200 && $obj_email->Status == 200 && $obj_pass->Status == 200 ) { 

            /* Register New User */
            $force_regis = register_new_user($_POST['username'], $_POST['email'], $_POST['pass']);

            if( ! $force_regis ){
                $res = array('Status' => 404, 'error_desc' => 'Can\'t register!! Something went wrong.');
                echo json_encode($res);
            }else{
                
                $result = $wpdb->get_results("select * from # where user_email = '".$_POST['email']."' order by user_registered desc limit 1");
                $result_check = $wpdb->insert( '#', array(
                    'user_name' => $_POST['username'],
                    'password' => $result[0]->user_pass,
                    'email' => $_POST['email'],
                    'member_since' => date('Y-m-d'),
                    'membership_level' => '2',
                    'phone' => $_POST['phone'],
                    'country' => 'Thailand',
                    'gender' => $_POST['gender'],
                    'account_state' => $_POST['account_state'],
                    'subscription_starts' => date('Y-m-d'),
                    'last_accessed' => date('Y-m-d'),
                    'last_accessed_from_ip' => '10.0.40.250') );
                
                if($result_check){
                    $res = array('Status' => 200, 'error_desc' => 'Success');
                    echo json_encode($res);
                }else{
                    $res = array('Status' => 404, 'error_desc' => 'Something went wrong');
                    echo json_encode($res);
                }
                
            }

        } else {
            $res = array('Status' => 404, 'error_desc' => 'Can\'t register!! Please check you input.');
            echo json_encode($res);
        }

    } else {
        echo $obj_token_status;
    }

    /*
    ** Class Authorization
    ** Version : 1.0.0
    ** Create by : TNL Developer Team.
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