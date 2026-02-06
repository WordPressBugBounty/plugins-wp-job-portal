<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALEncoder {

    private $securekey, $wpjobportal_iv;

    function __construct($wpjobportal_textkey) {
        if($wpjobportal_textkey != ''){
            $this->securekey = hash('sha256', $wpjobportal_textkey, TRUE);
        }else{
            $this->securekey = '';
        }

        $this->iv = mcrypt_create_iv(32);
    }

    function encrypt($wpjobportal_input) {
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->securekey, $wpjobportal_input, MCRYPT_MODE_ECB, $this->iv);
        return wpjobportalphplib::wpJP_safe_encoding($output);

    }

    function decrypt($wpjobportal_input) {
        $wpjobportal_input = wpjobportalphplib::wpJP_safe_decoding($wpjobportal_input);
        return wpjobportalphplib::wpJP_trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->securekey, $wpjobportal_input, MCRYPT_MODE_ECB, $this->iv));
    }

}

?>
