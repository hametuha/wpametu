<?php

namespace WPametu\Http;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;


/**
 * Input utility class
 *
 * @package WPametu\Http
 */
class Input extends Singleton
{

    use i18n;

    /**
     * Return GET Request
     *
     * @param string $key
     * @return null|string|array
     */
    public function get($key){
        if( isset($_GET[$key]) ){
            return $_GET[$key];
        }else{
            return null;
        }
    }

    /**
     * Return POST Request
     *
     * @param string $key
     * @return null|string|array
     */
    public function post($key){
        if( isset($_POST[$key]) ){
            return $_POST[$key];
        }else{
            return null;
        }
    }

    /**
     * Return REQUEST
     *
     * @param string $key
     * @return null|string|array
     */
    public function request($key){
        if( isset($_REQUEST[$key]) ){
            return $_REQUEST[$key];
        }else{
            return null;
        }
    }

    /**
     * Return current request method
     *
     * @return bool
     */
    public function request_method(){
        if( isset($_SERVER['REQUEST_METHOD']) ){
            return $_SERVER['REQUEST_METHOD'];
        }else{
            return false;
        }
    }

    /**
     * Get file input
     *
     * @param string $key
     * @return array
     */
    public function file_info($key){
        if( isset($_FILES[$key]['error']) && $_FILES[$key]['error'] == UPLOAD_ERR_OK ){
            return $_FILES[$key];
        }else{
            return [];
        }
    }

    /**
     * Get file upload error message
     *
     * @param string $key
     * @return string
     */
    public function file_error_message($key){
        if( $this->file_info($key) ){
            return '';
        }elseif( !isset($_FILES[$key]) ){
            return $this->__('File is not specified.');
        }else{
            switch($_FILES[$key]['error']){
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_INI_SIZE:
                    return $this->__('Uploaded file size exceeds allowed limit.');
                    break;
                default:
                    return $this->__('Failed to upload');
                    break;
            }
        }
    }

    /**
     * Returns post body
     *
     * This method is useful for typical XML API.
     *
     * @return string
     */
    public function post_body(){
        return file_get_contents('php://input');
    }

	/**
	 * Verify nonce
	 *
	 * @param string $action
	 * @param string $key Default '_wpnonce'
	 *
	 * @return bool
	 */
	public function verify_nonce($action, $key = '_wpnonce'){
		return wp_verify_nonce($this->request($key), $action);
	}

    /**
     * Sanitize super globals
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitize($value){
        // TODO: Sanitize
        return $value;
    }
}
