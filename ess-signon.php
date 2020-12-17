<?php 
    /*
    ** Class Authorization
    ** Version : 1.0.0
    ** Create by : Benz.Surachai
    */

    header('Access-Control-Allow-Origin: *');
    require( dirname( __FILE__ ) . '/wp-load.php' );

    global $wpdb;

    $obj = new ESSlogno;
    $obj_check_user = json_decode( $obj->validateUser($_GET['uuid']) );
    $custom_signon = $obj->wpdocs_custom_login($obj_check_user->username,$obj_check_user->pass);
    
    /*
    ** Class Authorization
    */
    class ESSlogno {
        
        public function validateUser($req_uuid) {

            $data_req = explode("#", base64_decode($req_uuid));
            $res_data = array('username' => $data_req[0], 'pass' => $data_req[1]);
            return json_encode($res_data);
        }

        function wpdocs_custom_login($username,$password) {
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password
            );
         
            $user = wp_signon( $creds, false );
         
            if ( is_wp_error( $user ) ) {
                $res = array('Status' => 404, 'error_desc' => 'Can\'t access to server. Please check username or password!!');
                echo json_encode($res);
            }
        }

    }
    
?>