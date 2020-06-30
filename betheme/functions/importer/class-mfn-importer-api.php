<?php
/**
 * Install pre-built websites remote API
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

if ( ! defined( 'ABSPATH' ) ){
	exit;
}

class Mfn_Importer_API extends Mfn_API {

	protected $code = '';
	protected $demo = '';

	protected $path_be	= '';
	protected $path_websites	= '';
	protected $path_demo = '';

	/**
	 * The constructor
	 */
	public function __construct( $demo = false ){

		if( ! $demo ){
			return false;
		}

		$this->code = mfn_get_purchase_code();
		$this->demo = $demo;

		$upload_dir = wp_upload_dir();
		$this->path_be = wp_normalize_path( $upload_dir['basedir'] .'/betheme' );
		$this->path_websites = wp_normalize_path( $this->path_be .'/websites' );

		$this->make_dir();
	}

	/**
	 * Directories creation
	 */
	protected function make_dir(){

		$this->path_demo = wp_normalize_path( $this->path_websites .'/'. $this->demo );

		if( ! file_exists( $this->path_be ) ){
			wp_mkdir_p( $this->path_be );
		}

		if( ! file_exists( $this->path_websites ) ){
			wp_mkdir_p( $this->path_websites );
		}

		if( ! file_exists( $this->path_demo ) ){
			wp_mkdir_p( $this->path_demo );
		}
	}

	/**
	 * Delete temporary directory
	 */
	public function delete_temp_dir(){

		// filesystem
		$wp_filesystem = Mfn_Helper::filesystem();

		// director is located outside wp uploads dir
		$upload_dir = wp_upload_dir();
		if( false === strpos( $this->path_demo, $upload_dir['basedir'] ) ){
			return false;
		}

		$wp_filesystem->delete( $this->path_demo, true );
	}

	/**
	 * Remote get demo
	 */
	
	function remoteFileExists($url) {
    $curl = curl_init($url);

    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_NOBODY, true);

    //do request
    $result = curl_exec($curl);

    $ret = false;

    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

        if ($statusCode == 200) {
            $ret = true;   
        }
    }

    curl_close($curl);

    return $ret;
}



	public function remote_get_demo(){

			// If the function it's not available, require it.
	if ( ! function_exists( 'download_url' ) ) {
       require_once ABSPATH . 'wp-admin/includes/file.php';
	}
 
	$file_url = BECLOUD_PATH . $this->demo . '.zip';
	$exists = Mfn_Importer_API::remoteFileExists($file_url);
	
	
	if(!$exists)
	{
	return new WP_Error( 'error_download', __( 'This demo package is not available on the cloud server. Contact @babak.' . 'Package Name: ' . $file_url , 'mfn-opts' ) );		
	}

	$tmp_file = download_url( $file_url );
	
	
	if( empty( $tmp_file ) ){
			return new WP_Error( 'error_download', __( 'The package could not be found. Notify the site administrator..', 'mfn-opts' ) );
		}

		// filesystem
	$wp_filesystem = Mfn_Helper::filesystem();
     
	
	$path_zip = wp_normalize_path( $this->path_demo .'/'. $this->demo .'.zip' );
	$path_unzip = wp_normalize_path( $this->path_demo .'/'. $this->demo );
	
    $filepath = ABSPATH . 'wp-content/uploads/betheme/websites/' . $this->demo . '/';
 
// Copies the file to the final destination and deletes temporary file.
	
	
	if( empty( $path_zip ) ){
			return new WP_Error( 'error_unzip', __( 'The package could not be extract. Notify the site administrator..', 'mfn-opts' ) );
		}
	
	if( empty( $path_unzip ) ){
			return new WP_Error( 'error_unzip', __( 'The package could not be extract. Notify the site administrator..', 'mfn-opts' ) );
		}


	copy( $tmp_file, $path_zip );
	@unlink( $tmp_file );

	
	$unzip = unzip_file( $path_zip, $this->path_demo );
		if( is_wp_error( $unzip ) ){
			return new WP_Error( 'error_unzip', $path_zip, 'mfn-opts'  );
		}

		if( ! is_dir( $path_unzip ) ) {
			return new WP_Error( 'error_folder', sprintf( __( 'Demo data folder does not exist (%s).', 'mfn-opts' ), $path_unzip ) );
		}

		return $path_unzip;
	}


}
