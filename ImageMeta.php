<?php
/**
 * Handle manipulating and processing of image meta information.
 */
Class ImageMeta {

    /**
     * Parse the iptc info and retrive the given value.
     *
     * Ref. http://codex.wordpress.org/Function_Reference/wp_read_image_metadata#Parameters
     * WP already adds some IPTC data
     *
     * @param $value The item you want returned
     * @param $image The image you want info from
     */
    public function iptcParser( $value=null, $image=null ){

        $size = getimagesize( $image, $info );

        if ( ! isset( $info['APP13'] ) )
            return;

        $iptc = iptcparse( $info['APP13'] );

        switch( $value ){
            case 'keywords':
                return $iptc['2#025'];
            case 'city':
                return $iptc['2#090'][0];
            case 'region':
                return $iptc['2#095'][0];
            case 'country':
                return $iptc['2#101'][0];
            default:
                return false;
        }
    }

}
