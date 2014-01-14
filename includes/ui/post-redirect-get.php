<?php

namespace WPametu\UI;

use WPametu\Pattern;

/**
 * Class which provides session-based PRG(Post Redirect Get)
 *
 * @package WPametu\UI
 */
class PostRedirectGet extends Pattern\Singleton
{

    use \WPametu\Traits\i18n;

    /**
     * Hook name which chould be executed on theme
     *
     * @const string PUBLIC_HOOK
     */
    const PUBLIC_HOOK = 'wpametu_notices';

    /**
     * Key to store message
     *
     * @var string
     */
    protected $message_key = 'prg_message';

    /**
     * Key to store error message
     *
     * @var string
     */
    protected $error_key = 'prg_error';

    /**
     * Constructor
     *
     * @param array $argument
     */
    protected function __construct(array $argument){
        add_action('admin_notices', [$this, 'flushMessage']);
        add_action(self::PUBLIC_HOOK, [$this, 'flushMessage']);
        // Start Session
        if( !session_id() ){
            if( ! session_start() ){
                error_log( $this->__('セッションを開始できません。サーバーの設定を確認してください。'));
            }
        }
    }

    /**
     * Add Message
     *
     * @param string $message
     * @param string $from
     */
    public function addMessage($message, $from = ''){
        $this->writeSession($message, $from, false);
    }

    /**
     * Add error message
     *
     * @param string $message
     * @param string $from
     */
    public function addErrorMessage($message, $from = ''){
        $this->writeSession($message, $from, true);
    }

    /**
     * Write message to session
     *
     * @param $message
     * @param string $from
     * @param bool $is_error
     */
    private function writeSession($message, $from = '', $is_error = false){
        if( session_id() ){
            $key = $is_error ? $this->error_key : $this->message_key;
            // Initialize
            if( !isset($_SESSION[$key]) || !is_array($_SESSION[$key]) ){
                $_SESSION[$key] = [];
            }
            // Add message
            $_SESSION[$key][] = ( empty($from) ? '' : sprintf('<strong>[%s]</strong> ', $from) ).$message;
        }
    }

    /**
     * Show message on screen
     *
     */
    public function flushMessage(){
        if( session_id() ){
            foreach( [ $this->error_key => 'error', $this->message_key => 'updated' ] as $key => $class_name ){
                if( isset($_SESSION[$key]) && !empty($_SESSION[$key]) ){
                    if( !is_admin() ){
                        /**
                         * Post redirect get message's class
                         */
                        $class_name = apply_filters('wpametu_prg_message_class', $class_name);
                    }
                    printf('<div class="%s"><p>%s</p></div>', $class_name, implode('<br />', $_SESSION[$key]));
                    $_SESSION[$key] = [];
                }
            }
        }
    }
}
