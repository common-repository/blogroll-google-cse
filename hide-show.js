/*
 * This file is included as part of the WordPress Blogroll to Google CSE plugin
 * The plugin is Copyright (C) 2008 Corey Wallis <techxplorer@gmail.com>
 * The plugin is covered by the GPL. Full details in the header of the 
 * blogroll-google-cse.php file
 */

/*
 * JavaScript code leveraging the JQuery library 
 * to show and hide the live and abandoned links.
 *
 */

// Use JQuery in compatibility / no conflict mode 

 
function show_live_links() {

	var $j = jQuery;

	// Select the appropriate links
	$j("li.blogroll_google_cse_abandoned").hide();
	$j("li.blogroll_google_cse_live").show();
	$j("#view_live").hide();
	$j("#view_abandoned").show();
	
	return false;
}

function show_abandoned_links() {

	var $j = jQuery;

	// Select the appropriate links
	$j("li.blogroll_google_cse_abandoned").show();
	$j("li.blogroll_google_cse_live").hide();
	$j("#view_live").show();
	$j("#view_abandoned").hide();
	
	return false;
}

function show_all_links() {

	var $j = jQuery;

	// Select the appropriate links
	$j("li.blogroll_google_cse_abandoned").show();
	$j("li.blogroll_google_cse_live").show();
	$j("#view_live").show();
	$j("#view_abandoned").show();
	
	return false;

}

 
 
