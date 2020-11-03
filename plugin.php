<?php
    
    /*
     Plugin Name: Update Shortened URL
     Plugin URI: https://github.com/timcrockford/yourls-api-edit-url
     Description: Define a custom API action 'update' and 'geturl'
     Version: 0.2.3
     Author: Tim Crockford
     Author URI: http://codearoundcorners.com/
     */
    
    yourls_add_filter( 'api_action_update', 'api_edit_url_update' );
    yourls_add_filter( 'api_action_geturl', 'api_edit_url_get' );
    yourls_add_filter( 'api_action_change_keyword', 'api_edit_url_change_keyword' );

    function process_title( $title, $url, $keyword ) {
        if ( strcasecmp($title, 'keep') == 0 ) {
            return  yourls_get_keyword_title( $keyword, '' );
        } elseif ( strcasecmp($title, 'auto') == 0 ){
            return yourls_get_remote_title( $url );
        } else {
            return $title;
        }
    }
    
    function api_edit_url_update() {
        if ( ! isset( $_REQUEST['shorturl'] ) ) {
            return array(
                'statusCode' => 400,
                'status'     => 'fail',
                'simple'     => "Need a 'shorturl' parameter",
                'message'    => 'error: missing param',
            );
        }

        if ( ! isset( $_REQUEST['url'] ) ) {
            return array(
                'statusCode' => 400,
                'status'     => 'fail',
                'simple'     => "Need a 'url' parameter",
                'message'    => 'error: missing param',
            );
        }

        $shorturl = $_REQUEST['shorturl'];
        $url = $_REQUEST['url'];

        if ( yourls_get_protocol( $shorturl ) ) {
            $keyword = yourls_get_relative_url( $shorturl );
        } else {
            $keyword = $shorturl;
        }

        if ( ! yourls_is_shorturl( $keyword ) ) {
            return array(
                'statusCode' => 404,
                'status'     => 'fail',
                'simple '    => "Error: keyword $keyword not found",
                'message'    => 'error: not found',
            );
        }

        $title = '';
        if ( isset($_REQUEST['title']) ) $title = $_REQUEST['title'];
        $title = process_title( $title, $url, $keyword );

        if( yourls_edit_link( $url, $keyword, $keyword, $title ) ) {
            return array(
                'statusCode' => 200,
                'simple'     => "Keyword $keyword updated to $url",
                'message'    => 'success: updated',
            );
        } else {
            return array(
                'statusCode' => 500,
                'status'     => 'fail',
                'simple'     => 'Error: could not edit keyword, not sure why :-/',
                'message'    => 'error: unknown error',
            );
        }
    }

    function api_edit_url_change_keyword() {
        if ( ! isset( $_REQUEST['newshorturl'] ) ) {
            return array(
                'statusCode' => 400,
                'status'     => 'fail',
                'simple'     => "Need a 'newshorturl' parameter",
                'message'    => 'error: missing param',
            );
        }

        if ( ! isset( $_REQUEST['url'] ) && ! isset( $_REQUEST['oldshorturl'] )) {
            return array(
                'statusCode' => 400,
                'status'     => 'fail',
                'simple'     => "Need a 'url' or 'oldshorturl' parameter",
                'message'    => 'error: missing param',
            );
        }

        if ( isset( $_REQUEST['url'] ) && ! isset( $_REQUEST['oldshorturl'] )) {
            $url = urldecode($_REQUEST['url']);
            if ( ! yourls_get_longurl_keywords( $url )) {
                return array(
                    'statusCode' => 500,
                    'status'     => 'fail',
                    'simple'     => "Error: could not find keyword for url $url",
                    'message'    => 'error: not found $url',
                );
            }

            $keywords = yourls_get_longurl_keywords( $url );
            if ( sizeof($keywords) > 1) {
                return array(
                    'statusCode' => 400,
                    'status'     => 'fail',
                    'simple'     => "Given URL has multiple shortcodes",
                    'message'    => 'error: ambiguous url',
                );
            } else {
                $oldshorturl = array_values( $keywords )[0];
            }
        } elseif ( ! isset( $_REQUEST['url'] )) {
            $oldshorturl = $_REQUEST['oldshorturl'];
            if ( ! yourls_is_shorturl( $oldshorturl ) ) {
                return array(
                    'statusCode' => 404,
                    'status'     => 'fail',
                    'simple '    => "Error: keyword $keyword not found",
                    'message'    => 'error: not found',
                );
            }

            $url = yourls_get_keyword_longurl( $oldshorturl );
        } else {
            $oldshorturl = $_REQUEST['oldshorturl'];
            $url = urldecode($_REQUEST['url']);
        }

        $newshorturl = $_REQUEST['newshorturl'];

        if ( yourls_get_protocol( $oldshorturl ) ) {
            $oldkeyword = yourls_get_relative_url( $oldshorturl );
        } else {
            $oldkeyword = $oldshorturl;
        }

        if ( yourls_get_protocol( $newshorturl ) ) {
            $newkeyword = yourls_get_relative_url( $newshorturl );
        } else {
            $newkeyword = $newshorturl;
        }

        if ( yourls_keyword_is_taken( $newkeyword ) ) {
            return array(
                'statusCode' => 409,
                'status'     => 'fail',
                'simple '    => "Error: keyword $newkeyword already exists",
                'message'    => 'error: already exists',
            );
        }

        if ( ! yourls_keyword_is_taken( $oldkeyword ) ) {
            return array(
                'statusCode' => 404,
                'status'     => 'fail',
                'simple '    => "Error: keyword $oldkeyword not found",
                'message'    => 'error: not found',
            );
        }

        $title = '';
        if ( isset($_REQUEST['title']) ) $title = $_REQUEST['title'];
        $title = process_title( $title, $url, $oldshorturl );

        if( yourls_edit_link( $url, $oldkeyword, $newkeyword, $title ) ) {
            return array(
                'statusCode' => 200,
                'simple'     => "Keyword $oldkeyword updated to $newkeyword for $url",
                'message'    => 'success: updated',
            );
        } else {
            return array(
                'statusCode' => 500,
                'status'     => 'fail',
                'simple'     => 'Error: could not edit keyword, not sure why :-/',
                'message'    => 'error: unknown error',
            );
        }
    }

    function api_edit_url_get() {
        if ( ! isset( $_REQUEST['url'] ) ) {
            return array(
                'statusCode' => 400,
                'status'     => 'fail',
                'simple'     => "Need a 'url' parameter",
                'message'    => 'error: missing param',
            );
        }

        $url = $_REQUEST['url'];
        $url_exists = yourls_url_exists($url);

        if ( $url_exists ) {
            if ( isset( $_REQUEST['exactly_one'] ) && ! filter_var( $_REQUEST['exactly_one'], FILTER_VALIDATE_BOOLEAN )) {
                $keywords = yourls_get_longurl_keywords( $url );
                return array(
                    'statusCode' => 200,
                    'simple'     => "Keywords for $url are " . json_encode ( $keywords ),
                    'message'    => 'success: found',
                    'keyword'    => json_encode( $keywords ),
                );
            } else {
                return array(
                    'statusCode' => 200,
                    'simple'     => "Keyword for $url is " . $url_exists->keyword,
                    'message'    => 'success: found',
                    'keyword'    => $url_exists->keyword,
                );
            }
        } else {
            return array(
                'statusCode' => 500,
                'status'     => 'fail',
                'simple'     => "Error: could not find keyword for url $url",
                'message'    => 'error: not found',
                'keyword'    => '',
            );
        }
    }
?>
