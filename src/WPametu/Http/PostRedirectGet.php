<?php

namespace WPametu\Http;

use WPametu\Pattern, \WPametu\Traits\i18n;

/**
 * Class which provides session-based PRG(Post Redirect Get)
 *
 * @package WPametu\Http
 */
class PostRedirectGet extends Pattern\Singleton
{

    use i18n;

    /**
     * Hook name which should be executed on theme
     *
     * Add the snippet below on your theme.
     *
     * <code>
     * <?php do_action(\WPametu\Http\PostRedirectGet::PUBLIC_HOOK); ?>
     * </code>
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
        // Start session on admin screen
        add_action('admin_init', array($this, 'start_session'));
        /**
         * wpametu_auto_start_session
         *
         * Filter Whether if session will be automatically start on public pages.
         *
         * @param bool $start Default true
         * @return bool
         */
        if( apply_filters('wpametu_auto_start_session', false) ){
            add_action('template_redirect', array($this, 'start_session'));
        }
    }

    /**
     * Start session if not exist
     */
    public function start_session(){
        if( !session_id() ){
            if( ! session_start() ){
                error_log( $this->__('Cannot start session. WPametu\Http\PostRedirectGet requires session.'));
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
     */
    public function flushMessage(){
        if( session_id() ){
            foreach( [ $this->error_key => 'error', $this->message_key => 'updated' ] as $key => $class_name ){
                if( isset($_SESSION[$key]) && !empty($_SESSION[$key]) ){
                    $markup = sprintf('<div class="%s"><p>%s</p></div>', $class_name, implode('<br />', $_SESSION[$key]));
                    if( is_admin() ){
                        echo $markup;
                    }else{
                        /**
                         * Post redirect get message's filter
                         *
                         * @param string $markup html string
                         * @param array $messages Messages' array
                         * @param string $class_name updated or error.
                         * @return string
                         */
                        echo apply_filters('wpametu_prg_message_class', $markup, $_SESSION[$key], $class_name);
                    }
                    // Make session empty.
                    $_SESSION[$key] = [];
                }
            }
        }
    }
}
