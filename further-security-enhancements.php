<?php
/**
 * Plugin Name: Mizzou Further Security Enhancements TEST
 * Description: KERNL integration test version.  blocks remote enumeration of usernames and removes wordpress version number from generators
 * Author: Paul Gilzow, Mizzou Creative, University of Missouri
 * Version: 0.1.4
 *
 * @package Plugins
 * @subpackage Security
 * @author Paul F. Gilzow, Mizzou Creative, University of Missouri
 * @copyright 2015 Curators of the University of Missouri
 * @version 0.1.4
 */

/**
 * Blocks remote attackers from enumerating user names
 * @param $strRedirectionURL
 * @param $strRequestedURL
 * @return mixed
 */
function mizzouBlockUserEnumeration($strRedirectionURL, $strRequestedURL)
{
    if (1 === preg_match('/\?author=([\d]*)/', $strRequestedURL)) {
        $strRedirectionURL = false;
    }

    return $strRedirectionURL;
}

add_filter('redirect_canonical','mizzouBlockUserEnumeration', 10,2);

/**
 * Removes username from the body class list.  Why does wordpress include the user name in the body class?  So you can
 * add per-user custom classes, but that seems like a very fringe case vs giving hackers all of your user names.
 *
 * @param $aryClasses array of classes to include in the body element
 * @return array filtered list of classes
 */
function mizzouRemoveUserNameFromBodyClass($aryClasses){
    if(is_author() && in_array('author',$aryClasses)){
        /**
         * match all classes of 'author-<username>' but not 'author-id'
         *
         * match: author-admin
         * match: author-gilzowp
         * NO match: author-5
         *
         */
        $aryUserNames = preg_grep('/^author-(?!\d+$).+$/',$aryClasses);
        if(count($aryUserNames) > 0){
            $aryClasses = array_diff($aryClasses,$aryUserNames);
        }
    }
    return $aryClasses;
}
add_filter('body_class','mizzouRemoveUserNameFromBodyClass',100);


/**
 * Force wordpress to use /?author=id for author permalink
 */
function mizzouChangeAuthorPermalinks()
{
    global $wp_rewrite;
    $wp_rewrite->author_structure= '';
}

add_action('init','mizzouChangeAuthorPermalinks');

/**
 * Removes the wordpress version number from EVERYTHING
 * @return string empty
 */
function mizzouRemoveGenerator()
{
    return '';
}

add_filter('the_generator','mizzouRemoveGenerator');
remove_action( 'wp_head', 'wp_generator' );

//disable the internal editor
if(!defined('DISALLOW_FILE_EDIT') || FALSE === DISALLOW_FILE_EDIT){
    define('DISALLOW_FILE_EDIT', TRUE);
}

/**
 * Removes the X-Pingback header
 * @param array $aryHeaders
 * @return array
 */
function mizzouRemoveXPingbackHeader($aryHeaders){
    if(isset($aryHeaders['X-Pingback'])){
        unset($aryHeaders['X-Pingback']);
    }

    return $aryHeaders;
}
add_filter('wp_headers','mizzouRemoveXPingbackHeader');

/**
 * Dsiable XMLRPC
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Remove version number from feeds
 */
remove_action('wp_head','feed_links_extra',3);