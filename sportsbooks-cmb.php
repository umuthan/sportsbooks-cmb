<?php
/*
 * Plugin Name:       Sports Book CMB
 * Plugin URI:        https://github.com/umuthan/sportsbooks-cmb
 * Description:       Custom Metabox for Sports Books
 * Version:           1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Umuthan Uyan
 * Author URI:        https://umuthan.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/umuthan/sportsbooks-cmb
 * Text Domain:       sportsbooks-cmb
 */

define('SPORTSBOOKS_JSON_URL', 'https://www.viscaweb.com/developers/test-front-end/pages/step2-sportsbooks.json');

abstract class sportsbook_CMB {

	/**
	 * Set up and add the meta box.
	 */
	public static function add() {
		$screens = [ 'page', 'post', 'sportsbook_cpt' ];
		foreach ( $screens as $screen ) {
			add_meta_box(
				'sportsbook_cmb',          // Unique ID
				'Sports Book', // Box title
				[ self::class, 'html' ],   // Content callback, must be of type callable
				$screen                  // Post type
			);
		}
	}


	/**
	 * Save the meta box selections.
	 *
	 * @param int $post_id  The post ID.
	 */
	public static function save( int $post_id ) {
		if ( array_key_exists( 'sportsbook_field', $_POST ) ) {
			update_post_meta(
				$post_id,
				'_sportsbook_meta_key',
				$_POST['sportsbook_field']
			);
		}
	}
  
  	/**
	 * Get contents from URL
	 *
	 */
  	public static function url_get_contents ($url) {
      if (function_exists('curl_exec')){ 
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        $url_get_contents_data = (curl_exec($conn));
        curl_close($conn);
      }elseif(function_exists('file_get_contents')){
        $url_get_contents_data = file_get_contents($url);
      }elseif(function_exists('fopen') && function_exists('stream_get_contents')){
        $handle = fopen ($url, "r");
        $url_get_contents_data = stream_get_contents($handle);
      }else{
        $url_get_contents_data = false;
      }
      return $url_get_contents_data;
    }

	/**
	 * Display the meta box HTML to the user.
	 *
	 * @param WP_Post $post   Post object.
	 */
	public static function html( $post ) {
      $sportsbook = self::url_get_contents(SPORTSBOOKS_JSON_URL);
	  $sports = json_decode($sportsbook);
		$value = get_post_meta( $post->ID, '_sportsbook_meta_key', true );
		?>
		<select name="sportsbook_field" id="sportsbook_field" class="postbox">
			<option value="">Select a sports book...</option>
          	<?php foreach($sports as $sport_key => $sport_value) { ?>
				<option value="<?php echo $sport_key; ?>" <?php selected( $value, $sport_key ); ?>><?php echo $sport_value; ?></option>				    
			<?php } ?>
		</select>
		<?php
	}
  
    public static function add_sportsbooks_content($content){
	  global $post;
      
      $original_content = $content;
      $sportsbooksvalue = get_post_meta($post->ID, '_sportsbook_meta_key', true);
      $sportsbooks_content = '';
      if($sportsbooksvalue) $sportsbooks_content = '<h2>You choose the sportsbook:"'.$sportsbooksvalue.'"</h2>';
      $new_content = $original_content . $sportsbooks_content;

      return $new_content;
    }
}

add_action( 'add_meta_boxes', [ 'sportsbook_CMB', 'add' ] );
add_action( 'save_post', [ 'sportsbook_CMB', 'save' ] );
add_action( 'the_content', [ 'sportsbook_CMB', 'add_sportsbooks_content' ] );
