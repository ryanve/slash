<?php
/**
 * URI5 is a compact set of URI-related PHP functions. It provides reliable ways to parse URIs and
 * get/compare their parts. It doesn't depend on any libraries or frameworks. To install, drop this 
 * file into a directory and use require_once() or include_once() to load it.
 * @author Ryan Van Etten (c) 2011
 * @link http://uri5.com
 * @license MIT
 * @version 1.1.0
 */

/**
 * uri5_remove_front - Remove the scheme and :// from a URI. In other words, the first 
 * double slash and anything to its left is removed.
 * @example
 *  uri5_remove_front('foo://example.com/1') #  'example.com/1'
 *  uri5_remove_front('//example.com/2')     #  'example.com/2'
 *  uri5_remove_front('www.example.com/3')   #  'www.example.com/3'
*/
if (!function_exists('uri5_remove_front')) {
function uri5_remove_front( $uri ) {
	$parts = explode( '//', $uri ); // Split URI into parts. (Using explode() is safer than parse_url().)
	$count = count($parts);         // Count the array.
	if ( $count === 1 && ctype_alnum(substr($parts[0], 0, 1 ))) { return $parts[0]; } // Already removed => return.
	elseif ( $count === 2 && ctype_alnum(substr($parts[1], 0, 1)) && empty($parts[0]) || substr($parts[0], -1) === ':' ) { return $parts[1]; } // Expected inputs.
	else { return preg_replace( '/^([^a-z0-9]*)/', '', $uri ); } // Unexpected inputs. Replace non-alphanumeric leading chars.
}}

/**
 * uri5_scheme_relative - convert a URI into a scheme-relative URI. (protocol-relative URI) 
 * @param (string) $uri is the (absolute-ish) URI you want to convert.
 * @example
 *  uri5_scheme_relative('foo://example.com/dir/file.htm')  #  '//example.com/dir/file.htm'
 *  uri5_scheme_relative('//example.com/dir/file.htm')      #  '//example.com/dir/file.htm'
 *  uri5_scheme_relative('example.com/dir/file.htm')        #  '//example.com/dir/file.htm'
 *  uri5_scheme_relative('foo://example.com//dir//double')  #  '//example.com//dir//double'
*/
if (!function_exists('uri5_scheme_relative')) {
function uri5_scheme_relative( $uri ) {
	return '//' . uri5_remove_front($uri);
}}

/**
 * uri5_get_authority - Return the authority of a URI.
 * @param (string) $uri is the URI that you want to get the authority of. 
 * @example
 *  uri5_get_authority('foo://example.com/dir/file.htm')       #  'example.com'
 *  uri5_get_authority('foo://www.example.com/dir/file.htm')   #  'www.example.com'
 *  uri5_get_authority('foo://user:pass@example.com:800/dir/') #  'user:pass@example.com:800'
 */
if (!function_exists('uri5_get_authority')) {
function uri5_get_authority( $uri ) {
	return array_shift( explode( '/', uri5_remove_front($uri) ) );
}}

/**
 * uri5_get_hostname - Return the hostname of a URI.
 * @param (string) $uri is the URI that you want to get the hostname of. In cases where 
 * you are certain that your input does not have a username and port, then consider using 
 * the slightly more efficient uri5_get_authority() function above.
 * @example
 *  uri5_get_hostname('foo://example.com/dir/file.htm')       #  'example.com'
 *  uri5_get_hostname('foo://www.example.com/dir/file.htm')   #  'www.example.com'
 *  uri5_get_hostname('foo://user:pass@example.com:800/dir/') #  'example.com'
 */
if (!function_exists('uri5_get_hostname')) {
function uri5_get_hostname( $uri ) {
	return array_shift( explode( ':', array_pop( explode( '@', uri5_get_authority($uri) ) ) ) );
}}

/**
 * uri5_slash_authority - Return the authority of a URI prepended by 
 * a double slash. This is useful for sanitizing dns-prefetch URIs. Technically
 * you want only the hostname for prefetches, but when you know that there's 
 * no username or port in your input, then this gives you the same result and 
 * is slightly more efficient.
 * @example
 *  uri5_slash_authority('foo://example.com/dir/file.htm')       #  '//example.com'
 *  uri5_slash_authority('foo://www.example.com/dir/file.htm')   #  '//www.example.com'
 *  uri5_slash_authority('foo://user:pass@example.com:800/dir/') #  '//user:pass@example.com:800'
 */
if (!function_exists('uri5_slash_authority')) {
function uri5_slash_authority( $uri ) {
	return '//' . uri5_get_authority( $uri );
}}

#end