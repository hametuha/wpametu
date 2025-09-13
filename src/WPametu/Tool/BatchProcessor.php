<?php

namespace WPametu\Tool;


use WPametu\AutoLoader;
use WPametu\File\Path;
use WPametu\Http\Input;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Traits\Reflection;

/**
 * Class BatchProcessor
 *
 * @package WPametu
 * @property-read array $option
 * @property-read Input $input
 */
class BatchProcessor extends Singleton {


	use Path;
	use i18n;
	use Reflection;

	/**
	 * @var array
	 */
	protected $batches = array();

	/**
	 * @var string
	 */
	protected $option_key = 'wpametu_batch_log';


	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = array() ) {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Register menu
	 */
	public function admin_menu() {
		add_submenu_page( 'tools.php', $this->__( 'Do Batch Actions' ), $this->__( 'Batch Actions' ), 'manage_options', 'wpametu-batch', array( $this, 'render_screen' ) );
	}

	/**
	 * Admin init
	 */
	public function admin_init() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		} else {
			add_action( 'wp_ajax_wpametu_batch', array( $this, 'ajax' ) );
		}
	}

	/**
	 * Render Admin screen
	 */
	public function render_screen() {
		$this->register_batches();
		?>
		<div class="wrap">
			<h2><?php $this->_e( 'Do Batch Actions' ); ?></h2>
			<?php if ( $this->batches ) : ?>
			<form id="batch-form" method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
				<input type="hidden" name="action" value="wpametu_batch">
				<?php wp_nonce_field( 'wpametu_batch' ); ?>
				<input type="hidden" name="page_num" id="page_num" value="1" />
				<ol class="batch-container">
				<?php
					$counter = 0;
				foreach ( $this->batches as $batch_class ) :
					++$counter;
					/** @var Batch $batch */
					$batch  = $batch_class::get_instance();
					$option = $this->option;
					if ( isset( $option[ $batch_class ] ) ) {
						$last = $option[ $batch_class ];
					} else {
						$last = false;
					}
					?>
				<li>
					<input type="radio" id="batch-<?php echo $counter; ?>" name="batch_class" value="<?php echo esc_attr( get_class( $batch ) ); ?>" />
					<label for="batch-<?php echo $counter; ?>">
						<span class="dashicons dashicons-yes"></span>
						<strong><?php echo esc_html( $batch->title ); ?></strong><small>ver <?php echo esc_html( $batch->version ); ?></small>
						<span class="description"><?php echo nl2br( esc_html( $batch->description ) ); ?></span>
					<?php if ( false === $last ) : ?>
							<span class="executed not-yet"><?php $this->_e( 'Never executed' ); ?></span>
						<?php else : ?>
							<span class="executed"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last ); ?></span>
						<?php endif; ?>
					</label>
				</li>
				<?php endforeach; ?>
				</ol>
				<?php submit_button( $this->__( 'Execute' ) ); ?>
				<div class="loader">
					<div><span class="dashicons dashicons-update"></span></div>
				</div>
			</form>
			<div id="batch-result">
				<h3><span class="dashicons dashicons-media-code"></span> <?php $this->_e( 'Console' ); ?></h3>
				<div class="console">

				</div>
			</div>
			<?php else : ?>
			<div class="error"><p><?php $this->_e( 'There is no classes.' ); ?></p></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Enqueue script
	 *
	 * @param string $page
	 */
	public function admin_enqueue_scripts( $page ) {
		if ( 'tools_page_wpametu-batch' === $page ) {
			wp_enqueue_script( 'wpametu-batch-helper' );
			wp_localize_script(
				'wpametu-batch-helper',
				'WpametuBatch',
				array(
					'confirm' => $this->__( 'Are you sure to execute this?' ),
					'alert'   => $this->__( 'No Batch is selected.' ),
					'done'    => $this->__( '[DONE] Batch process done.' ),
				)
			);
			wp_enqueue_style( 'wpametu-batch-screen' );
		}
	}

	/**
	 * Register Batch classes
	 */
	protected function register_batches() {
		static $batch_registered = false;
		if ( ! $batch_registered ) {
			// Register Batches
			/** @var AutoLoader $auto_loader */
			$auto_loader = AutoLoader::get_instance();
			$root        = $auto_loader->namespace_root;
			$namespace   = $auto_loader->namespace;
			if ( $namespace && $root && is_dir( $root . '/Batches' ) ) {
				// Scan directory and find batch children
				foreach ( scandir( $root . '/Batches' ) as $file ) {
					if ( preg_match( '/\.php$/', $file ) ) {
						$class_name = $namespace . '\\Batches\\' . str_replace( '.php', '', $file );
						if ( class_exists( $class_name ) && $this->is_sub_class_of( $class_name, Batch::class ) ) {
							$this->batches[] = $class_name;
						}
					}
				}
			}
			$batch_registered = true;
		}
	}

	/**
	 * Process Ajax
	 *
	 */
	public function ajax() {
		try {
			if ( ! $this->input->verify_nonce( 'wpametu_batch' ) ) {
				throw new \Exception( $this->__( 'You have no permission' ), 403 );
			}
			$this->register_batches();
			$class_name = stripslashes( $this->input->post( 'batch_class' ) );
			if ( false === array_search( $class_name, $this->batches, true ) ) {
				throw new \Exception( sprintf( $this->__( 'Batch %s doesn\'t exist.' ), $class_name ), 404 );
			}
			$page = $this->input->post( 'page_num' );
			if ( ! is_numeric( $page ) ) {
				throw new \Exception( $this->__( 'Invalid arguments.' ), 500 );
			}
			// O.K. Here we go!
			/** @var Batch $instance */
			$instance = $class_name::get_instance();
			$result   = $instance->process( $page );
			if ( ! $result ) {
				throw new \Exception( $this->__( 'Sorry, but batch returns unexpected result.' ) );
			}
			$json = array(
				'success'   => true,
				'processed' => $result->processed,
				'total'     => $result->total,
				'next'      => $result->has_next,
			);
			if ( $result->message ) {
				$json['message'] = $result->message;
			} else {
				$json['message'] = $result->total
					? sprintf( $this->__( '%s / %s have been processed.' ), number_format_i18n( $result->processed ), number_format_i18n( $result->total ) )
					: sprintf( $this->__( '%s have been processed.' ), number_format_i18n( $result->processed ) );
			}
			// If this is last, update
			if ( ! $result->has_next ) {
				$option                = $this->option;
				$option[ $class_name ] = current_time( 'timestamp', true );
				update_option( $this->option_key, $option );
			}
		} catch ( \Exception $e ) {
			$json = array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
		wp_send_json( $json );
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed|null|void
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'option':
				return get_option( $this->option_key, array() );
				break;
			case 'input':
				return Input::get_instance();
				break;
			default:
				return null;
				break;
		}
	}
}
