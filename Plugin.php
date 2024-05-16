<?php
/**
 * Plugin class.
 */

namespace SimplePluginForm;

defined( 'ABSPATH' ) || exit;

class Plugin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private $error;

	private function __construct() {
		$this->init_hooks();
		$this->error = new \WP_Error();
	}


	private function init_hooks() {
		add_action( 'init', array( $this, 'after_wp_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_loaded', array( $this, 'handle_form' ) );
	}

	public function handle_form() {
		if ( ! isset( $_POST['_simple_form_plugin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_simple_form_plugin_nonce'] ) ), 'simple-form-plugin-action' ) ) {
			return;
		}
		if ( empty( $_POST['sfp_name'] ) ) {
			$this->error->add( 'invalid_name', 'Name is required' );
		}
		if ( empty( $_POST['sfp_email'] ) ) {
			$this->error->add( 'invalid_email', 'Email is required' );
		} else {  // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
			if ( ! is_email( $_POST['sfp_email'] ) ) {
				$this->error->add( 'invalid_email', 'Invalid email' );
			}
		}
		if ( empty( $_POST['sfp_bio'] ) ) {
			$this->error->add( 'invalid_bio', 'Bio is required' );
		}

		if ( ! empty( $this->error->errors ) ) {
			return;
		}

		// ? why $wpdb is shown unknown word by snippet.

		global $wpdb;
		$table_name = $wpdb->prefix . 'form_data';
		$wpdb->insert(
			$table_name,
			array(
				'name'  => sanitize_text_field( wp_unslash( $_POST['sfp_name'] ) ),
				'email' => sanitize_email( wp_unslash( $_POST['sfp_email'] ) ),
				'info'  => sanitize_textarea_field( wp_unslash( $_POST['sfp_bio'] ) ),
			)
		);
	}

	public function enqueue() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'simple-form-plugin' ) ) {
			wp_enqueue_style( 'bootstrap' );
			wp_enqueue_script( 'bootstrap' );
			wp_enqueue_style( 'custom_style' );
		}
	}

	public function after_wp_init() {
		wp_register_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2' );
		wp_register_style( 'custom_style', plugins_url( 'style.css', __FILE__ ), array(), filemtime( __DIR__ . '\style.css' ) );
		wp_register_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js', array( 'jquery' ), '5.3.2', true );
		$this->add_shortcode();
	}

	private function add_shortcode() {
		add_shortcode( 'simple-form-plugin', array( $this, 'render_shortcode' ) );
	}

	public function render_shortcode() {
		global $wp;
		ob_start();
		?>
		<form method="post" action="<?php echo esc_url( home_url( add_query_arg( array(), $wp->request ) ) ); ?>" novalidate  id="sfp-form" >
			<?php wp_nonce_field( 'simple-form-plugin-action', '_simple_form_plugin_nonce' ); ?>
				<div class="form-group mb-4">
					<label for="sfp_name">Name
					</label>
					<input
						type="text"
						name="sfp_name"
						class="form-control"
						id="sfp_name"
						placeholder="Enter name"
					/>
					<?php if ( ! empty( $this->error->errors['invalid_name'] ) ) : ?>
						<div class="invalid-input">
							<?php echo esc_html( current( $this->error->errors['invalid_name'] ) ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="form-group mb-4">
					<label for="sfp_email">Email address</label>
					<input
					type="email"
					class="form-control"
					name="sfp_email"
					id="sfp_email"
					placeholder="john@doe.com"
				/>
				<?php if ( ! empty( $this->error->errors['invalid_email'] ) ) : ?>
						<div class="invalid-input">
							<?php echo esc_html( current( $this->error->errors['invalid_email'] ) ); ?>
						</div>
					<?php endif; ?>
			</div>
			<div class="form-group">
				<label for="sfp_bio">Bio</label>
				<textarea
					name="sfp_bio"
					class="form-control"
					id="sfp_bio"
					rows="3"
				></textarea>
				<?php if ( ! empty( $this->error->errors['invalid_bio'] ) ) : ?>
						<div class="invalid-input">
							<?php echo esc_html( current( $this->error->errors['invalid_bio'] ) ); ?>
						</div>
					<?php endif; ?>
			</div>
			<button type="submit" class="btn btn-primary mt-3">Submit</button>
		</form>
		<script>


		</script>
		<?php
		return ob_get_clean();
	}
}
?>
