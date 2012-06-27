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

    public function __construct(){
        if ( is_admin() )
            add_action( 'post_edit_form_tag' , array( &$this, 'addEnctype' ) );
    }

    /**
     * Handles the saving, i.e. creates a post type of attachment.
     *
     * During form submission run the method:
     * $class->fileUpload( $field_name='form_field_name' );
     *
     * @return attachmentID on success false on failure
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
        $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

        require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
        $meta = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );

        $image = New ImageMeta;
        $meta['image_meta']['keywords'] = $image->iptcParser( 'keywords', $uploaded_file['file'] );
        $meta['image_meta']['city']     = $image->iptcParser( 'city',     $uploaded_file['file'] );
        $meta['image_meta']['region']   = $image->iptcParser( 'region',   $uploaded_file['file'] );
        $meta['image_meta']['country']  = $image->iptcParser( 'country',  $uploaded_file['file'] );

        wp_update_attachment_metadata( $attach_id, $meta );

        // Set the feedback flag to false, since the upload was successful
        $upload_feedback = false;

        return $attach_id;
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
        $wp_upload_dir = wp_upload_dir();
        return $wp_upload_dir['baseurl'] . '/' . _wp_relative_upload_path( $file );
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
} // End 'MediaUpload'