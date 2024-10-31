<?php
/**
 Plugin Name: Quick Cache comment garbagecollector
 Plugin URI: http://www.mijnpress.nl
 Description: Add-on for Quick Cache. Will regenerate single page/post caches if a comment has been placed
 Version: 1.0
 Author: Ramon Fincken
 Author URI: http://www.mijnpress.nl
 */

function qc_comment_gc_init($comment_ID, $comment_data){
	if($comment_data->comment_approved == 'spam')
	{
		return;
	}
	$id = $comment_data->comment_post_ID; // This is the post_id aka $post->ID

	// Single
	if($url = get_permalink ($id));
	{
		gc_comment_gc_worker($url);
	}
	
	if (preg_match ("/^single-fp$/", $GLOBALS["WS_PLUGIN__"]["qcache"]["o"]["clear_on_update"]))
	{
		$url = site_url ("/");
		gc_comment_gc_worker($url);
	}
	
	// TODO: Implement purge all:  if (!$once && preg_match ("/^all$/", $GLOBALS["WS_PLUGIN__"]["qcache"]["o"]["clear_on_update"]) && ($once = true))
}

function gc_comment_gc_worker($url)
{
	if (is_array ($parse = c_ws_plugin__qcache_utils_urls::parse_url ($url)) && ($host_uri = preg_replace ("/^http(s?)\:\/\//i", "", $url)))
	{
		$host_uri = preg_replace ("/^(" . preg_quote ($parse["host"], "/") . ")(\:[0-9]+)(\/)/i", "$1$3", $host_uri);
		/**/
		list ($cache) = (array)glob (WP_CONTENT_DIR . "/cache/qc-c-*-" . md5 ($host_uri) . "-*"); /* Match md5_2. */
		/**/
		if ($cache) /* If a cache file exists for this $host_uri. */
		{
			if (is_writable ($cache) && unlink ($cache))
			{
				// Yes! We have deleted the page
				/*
				if (!is_multisite () || !c_ws_plugin__qcache_utils_conds::is_multisite_farm () || is_main_site ())
				{
				$notice = 'Quick Cache updated: <code>' . esc_html ($host_uri) . '</code> automatically :-)';
				c_ws_plugin__qcache_admin_notices::enqueue_admin_notice ($notice, $pages);
				}
				*/
			}
			else /* Notify site owners when their /cache directory files are NOT writable. */
			{
				/*
				 if (!is_multisite () || !c_ws_plugin__qcache_utils_conds::is_multisite_farm () || is_main_site ())
				 {
				 $notice = 'Quick Cache was unable to update: <code>' . esc_html ($host_uri) . '</code>. File not writable.';
				 c_ws_plugin__qcache_admin_notices::enqueue_admin_notice ($notice, $pages, true);
				 }
				 */
			}
		}
	}
}

add_action('wp_insert_comment', 'qc_comment_gc_init',20, 2);
?>