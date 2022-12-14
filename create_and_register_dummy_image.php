<?php
/**
 *
 * Plugin Name: Create and Register Dummy Image
 * Plugin URI:
 * Description:
 * Version:     0.1
 * Author:      Koharu Homma
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: create-and-register-dummy-image
 * Domain Path: /languages
 */

// 

class Create_and_Register_Dummy_Image {
	public function __construct() {
		add_action('acf/render_field/type=image', [$this, 'render_button_to_create_image']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

		add_action('wp_ajax_nopriv_create_and_register_dummy_image', [$this, 'create_and_register_dummy_image']);
		add_action('wp_ajax_create_and_register_dummy_image', [$this, 'create_and_register_dummy_image']);
	}

	public function render_button_to_create_image( $field ) {
		echo <<<HTML
		<div class="crdi-create-image">
			<p>
				幅: <input class="crdi-input" type="number" name="width">
				高さ: <input class="crdi-input" type="number" name="height">
			</p>
			<a href="#" class="crdi-create-image-btn">画像を生成</a>
		</div>
		HTML;
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'create_and_register_dummy_image_js', plugin_dir_url(__FILE__) . 'js/main.js', ['jquery'], date_i18n('U'), true );

		wp_enqueue_style( 'create_and_register_dummy_image_css', plugin_dir_url(__FILE__) . 'css/styles.css', [], date_i18n('U') );

		wp_localize_script('create_and_register_dummy_image_js', 'ajaxUrl', esc_url( admin_url('admin-ajax.php') ));
	}

	public function create_and_register_dummy_image() {
		$this->create_dummy_image();
	}

	public function create_dummy_image() {
		$width = $_REQUEST['width'];
		$height = $_REQUEST['height'];

		$image = imagecreate($width, $height);
		// TODO: できたら文字色も調整？
		$image_base_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
		$image_text_color = imagecolorallocate($image, 255, 255, 255);

		imagestring($image, 10, $this->get_text_x_axis($width), $this->get_text_y_axis($height), 'DUMMY', $image_text_color);

		header('Content-Type: image/png');

		$upload_dir = wp_upload_dir();
		$dummy_image_name = 'dummy_' . date_i18n('YmdHis') . '.png';
		$image_path = $upload_dir['path'] . '/' . $dummy_image_name;

		applog($image_path);

		imagepng($image, $image_path);

		$attachment = array(
			'guid'           => $image_path,
			'post_mime_type' => 'image/png',
			'post_title'     => sanitize_file_name( $dummy_image_name ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $image_path );
		$attach_metadata = wp_generate_attachment_metadata( $attach_id, $image_path );
		wp_update_attachment_metadata($attach_id, $attach_metadata);
		update_post_meta( $attach_id, '_wp_attached_file', ltrim( $upload_dir['subdir'], '/' ) . '/' . $dummy_image_name );
		wp_update_post(['ID' => $attach_id]);

		echo json_encode( ['image_name' => $dummy_image_name] );
		exit;
	}

	public function get_text_x_axis( $width ) {
		return ($width / 2) - 30;
	}

	public function get_text_y_axis( $height ) {
		return $height / 2;
	}
}

new Create_and_Register_Dummy_Image();