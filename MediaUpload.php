<?php

/**
 * Permissions NOT handled here!
 *
 * A series of related methods for managing file uploads within
 * WordPress.
 *
 * @author Zane M. Kolnik zanematthew[at]gmail[dot]com
 */
require_once 'ImageMeta.php';
Class MediaUpload {

    public $upload_dir;
    private $attachment_id;

    public function __construct(){

        $this->upload_dir = wp_upload_dir();

        if ( is_admin() )
            add_action( 'post_edit_form_tag' , array( &$this, 'addEnctype' ) );
    }

    /**
     * Handles the saving, i.e. creates a post type of attachment.
     *
     * During form submission run the method:
     * $class->fileUpload( $field_name='form_field_name' );
     *
     * @return $final_file An array of array of f*cking cool stuff
     * I guess if you think arrays are cool i like (*)(*)s
     * $final_file['attachment_id'] = $this->attachment_id;
     * $final_file['file'] = $uploaded_file['file'];
     * $final_file['file_info'] = $file_info[];
     */
    public function saveUpload( $field_name=null, $user_id=null ) {

        if ( is_null( $field_name ) )
            die('Need field_name');

        // Move the file to the uploads directory, returns an array
        // of information from $_FILES
        $uploaded_file = $this->handleUpload( $_FILES[ $field_name ] );

        if ( ! isset( $uploaded_file['file'] ) )
            return false;

        // If we were to have a unique user account for uploading
        if ( is_null( $user_id ) ) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
        }

        // Build the Global Unique Identifier
        $guid = $this->buildGuid( $uploaded_file['file'] );

        // Build our array of data to be inserted as a post
        $attachment = array(
            'post_mime_type' => $_FILES[ $field_name ]['type'],
            'guid' => $guid,
            'post_title' => 'Uploaded : ' . $this->mediaTitle( $uploaded_file['file'] ),
            'post_content' => '',
            'post_author' => $user_id,
            'post_status' => 'inherit',
            'post_date' => date( 'Y-m-d H:i:s' ),
            'post_date_gmt' => date( 'Y-m-d H:i:s' )
        );

        // Add the file to the media library and generate thumbnail.
        $this->attachment_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

        // @todo bug, this does NOT work when used in a PLUGIN!, so you'll have to make
        // your OWN thumbnail sizes!
        require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
        $meta = wp_generate_attachment_metadata( $this->attachment_id, $uploaded_file['file'] );

        $image_meta = wp_read_image_metadata( $uploaded_file['file'] );
        $meta['image_meta'] = $image_meta;

        $image = New ImageMeta;
        $meta['image_meta']['keywords'] = $image->iptcParser( 'keywords', $uploaded_file['file'] );
        $meta['image_meta']['city']     = $image->iptcParser( 'city',     $uploaded_file['file'] );
        $meta['image_meta']['region']   = $image->iptcParser( 'region',   $uploaded_file['file'] );
        $meta['image_meta']['country']  = $image->iptcParser( 'country',  $uploaded_file['file'] );
        wp_update_attachment_metadata( $this->attachment_id, $meta );

        $file_info = pathinfo( $uploaded_file['file'] );

        // Set the feedback flag to false, since the upload was successful
        $upload_feedback = false;

        $final_file = array();
        $final_file['attachment_id'] = $this->attachment_id;
        $final_file['file'] = $uploaded_file['file'];
        $final_file['file_info'] = $file_info;

        return $final_file;
    }

    /**
     * Do some set-up before calling the wp_handle_upload function
     */
    public function handleUpload( $file=array() ){
        require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
        return wp_handle_upload( $file, array( 'test_form' => false ), date('Y/m') );
    }

    /**
     * Builds the GUID for a given file from the media library
     * @param full/path/to/file.jpg
     * @return guid
     */
    public function buildGuid( $file=null ){
        // $wp_upload_dir = wp_upload_dir();
        return $this->upload_dir['baseurl'] . '/' . _wp_relative_upload_path( $file );
    }

    /**
     * Parse the title of the media based on the file name
     * @return title
     */
    public function mediaTitle( $file ){
        return addslashes( preg_replace('/\.[^.]+$/', '', basename( $file ) ) );
    }

    /**
     * Adds the enctype for file upload, used with the hook
     * post_edit_form_tag for adding uploader to post meta
     */
    public function addEnctype(){
        echo ' enctype="multipart/form-data"';
    }

    /**
     * Resize images based on the "type"
     *
     * Normally this is done in WordPress, but for some reason
     * wp_generate_attachment_metadata() does not work when
     * used in a plugin.
     *
     * @param $file = /my/file/path/image.jpg
     * @param $type = thumb|square|main
     *
     * @todo Since images are NOT "registered" with WordPress
     * they will NOT be deleted from the media library when the
     * original image is deleted!
     *
     * @todo use wp_update_attachment_metadata() to update
     * the postmeta thumbnails ref. the array in
     * wp_generate_attachment_metadata()
     *
     * @todo remove hardcoded sizes and suffix, possibly a
     * public variable.
     *
     * @return same as image_resize() wp_error
     */
    public function resizeImage( $file=null, $type=null ){
        switch ( $type ) {
            case 'thumb':
                $max_w = 104;
                $max_h = 70;
                $suffix = 'zm-thumb';
                break;

            case 'square':
                $max_w = 50;
                $max_h = 50;
                $suffix = 'zm-square';
                break;

            case 'main':
                $max_w = 454;
                $max_h = 300;
                $suffix = 'zm-main';
                break;

            default:
                # code...
                break;
        }

        return image_resize( $file, $max_w, $max_h, $crop=true, $suffix, $path=$this->upload_dir['path'] );
    }
} // End 'MediaUpload'