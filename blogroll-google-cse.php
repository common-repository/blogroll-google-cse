<?php
/*
Plugin Name: Blogroll to Google CSE
Plugin URI: http://techxplorer.com/projects/blogroll-google-cse/
Version: 1.3
Author: techxplorer
Author URI: http://techxplorer.com
Description: A plugin to manage links in the blogroll that are searched using a Google CSE.
*/
/*
 * Blogroll to Google CSE - A WordPress plugin to manage links in the 
 * blogroll that are searched using a Google CSE.
 * Copyright (C) 2008 - 2009 Corey Wallis <techxplorer@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * blogroll-google-cse class contains all of the code for the operation of the plugin
 */
 
if(!class_exists('blogrollGoogleCSE')) {

	class blogrollGoogleCSE {

		// Name of the options variable stored in the database
		var $admin_opt_name = 'blogroll-google-cse';
		
		/**
		 * Get the list of admin options.
		 * Will write updated options to DB, to cater for upgrades later
		 */
		function get_admin_options() {
			$admin_options = array('link_cats' => array(), 'live_link_cat' => '', 'abandoned_link_cat' => '',
								   'view_live_link_text' => 'View only live links',
								   'view_abandoned_link_text' => 'View only abandoned links',
								   'view_all_link_text' => 'View all links',
								   'google_title' => 'My CSE',
								   'google_desc' => 'Search a list of interesting sites',
								   'google_keywords' => '',
								   'google_lf_background' => '#FFFFFF',
								   'google_lf_border' => '#336699',
								   'google_lf_title' => '#0000CC',
								   'google_lf_text' => '#000000',
								   'google_lf_url' => '#3366CC',
								   'google_lf_visited' => '#FFBD10',
								   'google_lf_light' => '#000000',
								   'search_page' => '',
								   'results_page' => '',
								   'display_ads' => 'FORID:10',
								   'frame_width' => '600',
								   'timestamp' => '',
								   'version' => '1.3',
								   'show_promo_link' => 'no'
								  );
			
		 	// get the options from the database
		 	$options = get_option($this->admin_opt_name);
		 	if (!empty($options) && is_array($options)) {
		 		// options have been found in the db
		 		// populate our variable with what was found
		 		foreach($options as $key => $value) {
		 			$admin_options[$key] = $value;
		 		}
		 		
		 		// update the stored options if this is an upgrade
		 		if($options['version'] == '') {
		 			
		 			// delete unecessary keys, if necessary
		 			if(array_key_exists('search_page_uri', $options)) {
		 				unset($options['search_page_uri']);
		 			}
		 			if(array_key_exists('results_page_uri', $options)) {
		 				unset($options['results_page_uri']);
		 			}
		 			
		 			// update the version element
		 			$options['version'] = '1.3';
		 		}
		 		
		 		// update the version number
		 		if($options['version'] != '1.3') {
		 			$options['version'] = '1.3';
		 		}	
		 	}
		 	
		 	// Update the options in the DB
		 	// no real need to do this is there?
		 	//update_option($this->admin_opt_name, $admin_options);
		 	
		 	// Return a list of options
			return $admin_options;
		} // function for getting options
		
		/**
		 * is_colour function used to ensure a value is a hex colour definition
		 * @param string $value value to be validated
		 * @returns boolean
		 */
		private function is_colour($value) {
		
			// Ensure value is 7 or 4 in length
			if(strlen($value) != 7 && strlen($value) != 4) {
				return FALSE;
			}
			
			// Ensure value starts with a hash
			if(strpos($value, '#') !== 0) {
				return FALSE;
			}
			
			// Ensure value is only #, 0-9, A-F,
			$value = strtoupper($value);
			$value = trim($value, '#0..9A..F');
			if ($value != '') {
				return FALSE;
			}
			
			// All tests passed
			return TRUE;
		} // end is_colour function
		
		/**
		 * Display the admin options page
		 */
		function display_admin_page() {
		
			// get the options
			$options = $this->get_admin_options();
			
			if(isset($_POST['changeit'])) {
			
				//check the nonce
				if(function_exists('check_admin_referer')) {
					check_admin_referer('blogroll-google-cse_admin_options-update');
				}
			
				$all_ok = true;
				$error = '<ul>';
				
				// options have been posted
				// List of link categories to use
				if(isset($_POST['categories'])) {
					$options['link_cats'] = $_POST['categories'];
				} else {
					$error .= '<li>You must select at least one link category</li>';
					$all_ok = false;
				}
				
				// Live link category
				if(isset($_POST['live_links'])) {
					$options['live_link_cat'] = $_POST['live_links'];
				} else {
					$error .= '<li>Missing post variable - "live_links"</li>';
					$all_ok = false;
				}
				
				// Abandoned link category
				if(isset($_POST['abandoned_links'])) {
					$options['abandoned_link_cat'] = $_POST['abandoned_links'];
				} else {
					$error .= '<li>Missing post variable - "abandoned_links"</li>';
					$all_ok = false;
				}
				
				// UI Elements
				if(isset($_POST['ui_live'])) {
					if(trim($_POST['ui_live']) != '' && isset($_POST['live_links'])) {
						$options['view_live_link_text'] = htmlspecialchars($_POST['ui_live']);
					} else {
						$error = '<li>You must specify the live link text</li>';
						$all_ok = false;
					}
				} else {
					$error .= '<li>Missing post variable - "ui_live"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['ui_abandoned'])) {
					if(trim($_POST['ui_abandoned']) != '' && isset($_POST['abandoned_links'])) {
						$options['view_abandoned_link_text'] = htmlspecialchars($_POST['ui_abandoned']);
					} else {
						$error = '<li>You must specify the abandoned link text</li>';
						$all_ok = false;
					}
				} else {
					$error .= '<li>Missing post variable - "ui_abandoned"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['ui_all'])) {
					if(trim($_POST['ui_all']) != '' 
						&& (isset($_POST['live_links']) || isset($_POST['abandoned_links']))) {
							$options['view_all_link_text'] = htmlspecialchars($_POST['ui_all']);
					} else {
						$error = '<li>You must specify the view all links text</li>';
						$all_ok = false;
					}
				} else {
					$error .= '<li>Missing post variable - "ui_all"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['show_promo_link'])) {
					if($_POST['show_promo_link'] == 'yes') {
						$options['show_promo_link'] = 'yes';
					} else {
						$options['show_promo_link'] = 'no';
					}
				}else {
					$options['show_promo_link'] = 'no';
				}
				
				// Google CSE XML Elements
				if(isset($_POST['google_title'])) {
					$options['google_title'] = htmlspecialchars($_POST['google_title']);
				} else {
					$error .= '<li>Missing post variable - "google_title"</li>';
					$all_ok = false;
				}

				if(isset($_POST['google_desc'])) {
					$options['google_desc'] = htmlspecialchars($_POST['google_desc']);
				} else {
					$error .= '<li>Missing post variable - "google_desc"</li>';
					$all_ok = false;
				}

				if(isset($_POST['google_keywords'])) {
					$options['google_keywords'] = htmlspecialchars($_POST['google_keywords']);
				} else {
					$error .= '<li>Missing post variable - "google_keywords"</li>';
					$all_ok = false;
				}
				
				// Google CSE Look and Feel
				if(isset($_POST['google_lf_background'])) {
					if($this->is_colour(trim($_POST['google_lf_background']))) {
						$options['google_lf_background'] = trim($_POST['google_lf_background']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the background colour value</li>';
						$all_ok = false;
					}
				} else {
					$error .= '<li>Missing post variable - "google_lf_background"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_lf_border'])) {
					if($this->is_colour(trim($_POST['google_lf_border']))) {
						$options['google_lf_border'] = trim($_POST['google_lf_border']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the border colour value</li>';
						$all_ok = false;
					}
				} else {
					$error .= '<li>Missing post variable - "google_lf_border"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_lf_title'])) {
					if($this->is_colour(trim($_POST['google_lf_title']))) {
						$options['google_lf_title'] = trim($_POST['google_lf_title']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the title colour value</li>';
						$all_ok = false;
					}					
				} else {
					$error .= '<li>Missing post variable - "google_lf_title"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_lf_text'])) {
					if($this->is_colour(trim($_POST['google_lf_text']))) {
						$options['google_lf_text'] = trim($_POST['google_lf_text']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the text colour value</li>';
						$all_ok = false;
					}					
				} else {
					$error .= '<li>Missing post variable - "google_lf_text"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_lf_url'])) {
					if($this->is_colour(trim($_POST['google_lf_url']))) {
						$options['google_lf_url'] = trim($_POST['google_lf_url']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the url colour value</li>';
						$all_ok = false;
					}					
				} else {
					$error .= '<li>Missing post variable - "google_lf_url"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_lf_visited'])) {
					if($this->is_colour(trim($_POST['google_lf_visited']))) {
						$options['google_lf_visited'] = trim($_POST['google_lf_visited']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the visted colour value</li>';
						$all_ok = false;
					}					
				} else {
					$error .= '<li>Missing post variable - "google_lf_visited"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_lf_light'])) {
					if($this->is_colour(trim($_POST['google_lf_light']))) {
						$options['google_lf_light'] = trim($_POST['google_lf_light']);
					} else {
						$error .= '<li>Colours must be specified in hexidecimal notation<br/>Check the light colour value</li>';
						$all_ok = false;
					}
					
				} else {
					$error .= '<li>Missing post variable - "google_lf_light"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['search_page'])) {
				
					// Do a simple check, 
					// More complex not worth effort really
					if(trim($_POST['search_page']) != '') {
						$options['search_page'] = trim($_POST['search_page']);
					
					} else {
						$error .= '<li>You must specify the URL to the search page</li>';
						$all_ok = false;
					}
					
				} else {
					$error .= '<li>Missing post variable - "search_page"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['results_page'])) {
				
					// Do a simple check, 
					// More complex not worth effort really
					if(trim($_POST['results_page']) != '') {
						$options['results_page'] = trim($_POST['results_page']);
					
					} else {
						$error .= '<li>You must specify the URL to the search results page</li>';
						$all_ok = false;
					}

				} else {
					$error .= '<li>Missing post variable - "results_page"</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['display_ads'])) {
					$options['display_ads'] = $_POST['display_ads'];
				} else {
					$error .= '<li>You must specify your choice about how to display ads</li>';
					$all_ok = false;
				}
				
				if(isset($_POST['google_iframe_width'])) {
					if(is_numeric(trim($_POST['google_iframe_width']))) {
						if(is_int(intval(trim($_POST['google_iframe_width']))) != 0) {
							$options['frame_width'] = intval(trim($_POST['google_iframe_width']));
						} else {
							$error .= '<li>The IFrame width must be a whole number.<br/>Do not use px at the end</li>';
							$all_ok = false;
						}
					} else {
						$error .= '<li>The IFrame width must be numeric</li>';
						$all_ok = false;
					}
				} else {
					$error .= '<li>Missing post variable - "google_iframe_width"</li>';
					$all_ok = false;
				}
								
				
				if($all_ok) {
				
					// update the options
					update_option($this->admin_opt_name, $options);
					
					// print message
					print '<div id="message" class="updated fade"><p><strong>Settings have been updated.</strong></p></div>';
				} else {
					print '<div id="message" class="error fade"><p><strong>Settings have not been updated.</strong><br/>';
					print 'Check your values and try again</p>';
					
					if($error != '<ul>') {
						print $error . '</ul>';
						print '</div>';
					} else {
						print '</div>';
					}
				}
			}
			
			// build the categories list
			$categories = get_categories('type=link&hide_empty=0');
			$cat_html = '<ul>';
			foreach($categories as $category) {
				if(in_array($category->term_id, $options['link_cats'])) {
					$cat_html .= '<li><input type="checkbox" checked="checked" name="categories[]"';
					$cat_html .= ' value="' . $category->term_id . '"/>' . $category->name . "</li>";
				} else {
					$cat_html .= '<li><input type="checkbox" name="categories[]"';
					$cat_html .= ' value="' . $category->term_id . '"/>' . $category->name . "</li>";
				}
			}
			$cat_html .= '</ul>';
			
			// build the live category dropdown
			$live_category = '<select name="live_links" id="live_links" size="1">';
			$live_category .= '<option value="null">Select a category, if applicable</option>';
			foreach($categories as $category) {
				if($category->term_id == $options['live_link_cat']) {
					$live_category .= '<option value="' . $category->term_id . '" selected="selected">' . $category->name . '</option>';
				} else {
					$live_category .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
				}
			}
			$live_category .= '</select>';
			
			// build the abandoned category dropdown
			$abandoned_category = '<select name="abandoned_links" id="abandoned_links" size="1">';
			$abandoned_category .= '<option value="null">Select a category, if applicable</option>';
			foreach($categories as $category) {
				if($category->term_id == $options['abandoned_link_cat']) {
					$abandoned_category .= '<option value="' . $category->term_id . '" selected="selected">' . $category->name . '</option>';
				} else {
					$abandoned_category .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
				}
			}
			$abandoned_category .= '</select>';
			
			// Build the display ads dropdown
			$display_ads = '<select name="display_ads" size="1">';
			
			if($options['display_ads'] == 'FORID:9') {
				$display_ads .= '<option value="FORID:9" selected="selected">Right</option>';
			} else {
				$display_ads .= '<option value="FORID:9">Right</option>';
			}
			
			if($options['display_ads'] == 'FORID:10') {
				$display_ads .= '<option value="FORID:10" selected="selected">Top and Right</option>';
			} else {
				$display_ads .= '<option value="FORID:10">Top and Right</option>';
			}
			
			if($options['display_ads'] == 'FORID:11') {
				$display_ads .= '<option value="FORID:11" selected="selected">Top and Bottom</option>';
			} else {
				$display_ads .= '<option value="FORID:11">Top and Bottom</option>';
			}
			
			if($options['display_ads'] == 'non-profit') {
				$display_ads .= '<option value="non-profit" selected="selected">No Ads - Non profit organisation</option>';
			} else {
				$display_ads .= '<option value="non-profit">No Ads - Non profit organisation</option>';
			}
			
			$display_ads .= '</select>';
			
			// display a promo link
			if($options['show_promo_link'] == 'no') {
				$show_promo_link = '<input type="checkbox" name="show_promo_link" id="show_promo_link" value="yes"/>';
			} else {
				$show_promo_link = '<input type="checkbox" name="show_promo_link" id="show_promo_link" value="yes" checked="checked"/>';
			}
			
			// build the NONCE
			if (function_exists('wp_nonce_field')) {
				$nonce = wp_nonce_field('blogroll-google-cse_admin_options-update');
			}
			
			// output the form
			echo <<<____________EOS
			<div class="wrap">
				<h2>Blogroll Google CSE options</h2>
				<form method="post" id="blogroll_google_cse-options" action="{$_SERVER['REQUEST_URI']}">
					<input type="hidden" name="changeit" id="changeit" value="yes"/>
					{$nonce}
					<h3>Link Category Options</h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="categories[]">
									Categories of links
								</label>
							</th>
							<td>
								{$cat_html}
								<br />
								Select the categories that contain links to be included in the CSE.
								<br />
								<strong>Please note:</strong> <em>Do not</em> include the categories that you specify for Live links or Abandonded links. <br/>
								It is assumed that those categories already contain links in the categories that you select here.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="live_links">
									Live Link Category
								</label>
							</th>
							<td>
								{$live_category}
								<br/>
								Select the category of links that are to be considered live. <em>(If Applicable)</em>.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="abandoned_links">
									Abandoned Link Category
								</label>
							</th>
							<td>
								{$abandoned_category}
								<br/>
								Select the category of links that are to be considered abandoned. <em>(If Applicable)</em>.
							</td>
						</tr>
					</table>
					<h3>User Interface Options</h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="ui_live">
									Live link text
								</label>
							</th>
							<td>
								<input type="text" size="25" name="ui_live" id="ui_live" value="{$options['view_live_link_text']}"/>
								<span id="ui_live_na"><strong>Not Applicable</strong></span>
								<br />
								Text of the link that the user clicks on to view all links that are considered live. <em>(If Applicable)</em>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="ui_abandoned">
									Abandoned link text
								</label>
							</th>
							<td>
								<input type="text" size="25" name="ui_abandoned" id="ui_abandoned" value="{$options['view_abandoned_link_text']}"/>
								<span id="ui_abandoned_na"><strong>Not Applicable</strong></span>
								<br />
								Text of the link that the user clicks on to view all links that are considered abandoned. <em>(If Applicable)</em>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="ui_all">
									View all links text
								</label>
							</th>
							<td>
								<input type="text" size="25" name="ui_all" id="ui_all" value="{$options['view_all_link_text']}"/>
								<span id="ui_all_na"><strong>Not Applicable</strong></span>
								<br />
								Text of the link that the user clicks on to view all links. <em>(If Applicable)</em>.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="ui_all">
									Display plugin promotion link
								</label>
							</th>
							<td>
								{$show_promo_link}
								<br />
								Display a link to the plugin page on the search and search results pages
							</td>
						</tr>
					</table>
					<h3>Google CSE Options</h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="google_title">
									Google CSE title
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_title" id="google_title" value="{$options['google_title']}"/>
								<br />
								The title of the generated Google CSE
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_desc">
									Google CSE description
								</label>
							</th>
							<td>
								<input type="text" size="50" name="google_desc" id="google_desc" value="{$options['google_desc']}"/>
								<br />
								The description of the generated Google CSE
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_keywords">
									Google CSE Keywords
								</label>
							</th>
							<td>
								<input type="text" size="50" name="google_keywords" id="google_keywords" value="{$options['google_keywords']}"/>
								<br />
								A list of keywords that are associated with this CSE and will help to tune searches
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="display_add">
									Should ads be displayed?
								</label>
							</th>
							<td>
								{$display_ads}
								<br />
								Choose to display ads, and if so where on the page they should be displayed.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_iframe_width">
									Width of the IFrame that contains the search results
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_iframe_width" id="google_iframe_width" value="{$options['frame_width']}" />
								<br />
								Choose to display ads, and if so where on the page they should be displayed.
							</td>
						</tr>
					</table>
					<h3>Google CSE Colours</h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_background">
									Background Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_background" id="google_lf_background" class="colorwell" value="{$options['google_lf_background']}" />
								<br />
								The colour of the background of the search results box
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
							<td rowspan="7" style="background-color: #FFFFFF">
								<div id="picker" style="float: right;"></div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_background">
									Border Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_border" id="google_lf_border" class="colorwell" value="{$options['google_lf_border']}" />
								<br />
								The colour of the border around ads and above the search results
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_title">
									Title Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_title" id="google_lf_title" class="colorwell" value="{$options['google_lf_title']}" />
								<br />
								The colour of the title of the search result
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_text">
									Title Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_text" id="google_lf_text" class="colorwell" value="{$options['google_lf_text']}" />
								<br />
								The colour of the text beneath the search result title
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_url">
									URL Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_url" id="google_lf_url" class="colorwell" value="{$options['google_lf_url']}" />
								<br />
								The colour of the url beneath the text of the search result
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_visited">
									URL Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_visited" id="google_lf_visited" class="colorwell" value="{$options['google_lf_visited']}" />
								<br />
								The colour of the url when it has been visted
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="google_lf_light">
									URL Colour
								</label>
							</th>
							<td>
								<input type="text" size="25" name="google_lf_light" id="google_lf_light" class="colorwell" value="{$options['google_lf_light']}" />
								<br />
								The colour of the other text for a search result (e.g. Cached)
								<br />
								<strong>Note: </strong>Ensure all colours are specified using Hexidecimal Notation
							</td>
						</tr>
					</table>
					<h3>Search and Results Page URLs</h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="search_page">
									Search Page URL
								</label>
							</th>
							<td>
								<input type="text" size="50" name="search_page" id="search_page" value="{$options['search_page']}" />
								<br />
								The complete URL to the WordPress page that has the list of links and the search box.
								<br />
								Create a page and add, on a line by itself, the following text (including square brackets): 
								<br />
								[search-blogroll-google-cse]
								<br />
								This page will display the search form and the list of links.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="results_page">
									Results Page URL
								</label>
							</th>
							<td>
								<input type="text" size="50" name="results_page" id="results_page" value="{$options['results_page']}" />
								<br />
								The complete URL to the WordPress page that will show the list of search results
								<br />
								Create a page and add, on a line by itself, the following text (including square brackets): 
								<br />
								[results-blogroll-google-cse]
								<br />
								This page will display the search form and the list of links.
							</td>
						</tr>
					</table>						
					<p class="submit">
						<input type="submit" name="Submit" value="Save Changes" class="button" />
					</p>
				</form>
			</div>			
____________EOS;
		} // function to display admin options page
		
		/**
		 * Function to replace the content place holder with the list of links
		 */
		function add_content_list($atts, $content = null) {
		
			// process the list of attributes
			// based on idiom at: http://codex.wordpress.org/Shortcode_API
			$attributes = shortcode_atts(array(
				'links' => 'yes'
				), $atts);
				
						
			// Get the admin options
			$options = $this->get_admin_options();
				
			// Check to make sure at least one category has been selected
			if(count($options['link_cats']) == 0) {
				$error = '<p><strong>Warning!</strong> No link category selected,<br/>
						  Please update the Blogroll to Google CSE settings and try again.</p>
						 ';
				return $error;
			}
				
			// Build the Google Search Box
			// Bring in the Google CSE JavaScript
			require(dirname(__FILE__) . '/google-js.php');
				
			// Prepare the Google Search Box
			$google_search_box = str_replace('[search-results]', $options['results_page'], $google_search_box);
			$google_search_box = str_replace('[xml-src]', plugins_url('/blogroll-google-cse/make-cse-xml.php?' . $options['timestamp']), $google_search_box);
			
			// Add ad display parameter
			if ($options['display_ads'] != 'non-profit') {
				$google_search_box = str_replace('[display-ads]', $options['display_ads'], $google_search_box);
			} else {
				$google_search_box = str_replace('[display-ads]', 'FORID:9', $google_search_box);
			}
			
			// define additional variables
			$link_list = '';
			
			// check to see if we're displaying links as well
			if(strtolower($attributes['links']) == 'yes') {
		
				// Scope the WordPress database class appropriately
				global $wpdb;
				//$wpdb->show_errors(); // Show db errors, for debugging
					
				// Get the list of live blogs, if applicable
				if($options['live_link_cat'] != '') {
					$sql = "SELECT object_id
							FROM $wpdb->term_taxonomy tt, $wpdb->term_relationships tr
							WHERE tt.term_id = {$options['live_link_cat']}
							AND tt.term_taxonomy_id = tr.term_taxonomy_id";
							
					$result = $wpdb->query($sql);
					
					if ($result > 0) {
						$live_links = $wpdb->get_col($sql);
					}
				}
		
				// Get the list of abandoned blogs, if applicable
				if($options['abandoned_link_cat'] != '') {
					$sql = "SELECT object_id
							FROM $wpdb->term_taxonomy tt, $wpdb->term_relationships tr
							WHERE tt.term_id = {$options['abandoned_link_cat']}
							AND tt.term_taxonomy_id = tr.term_taxonomy_id";
							
					$result = $wpdb->query($sql);
					
					if ($result > 0) {
						$abandoned_links = $wpdb->get_col($sql);
					}
				}
				
				// Get expanded data on the link categories we want
				$sql = "SELECT term_id, name FROM $wpdb->terms WHERE term_id = ";
				
				foreach($options['link_cats'] as $id) {
					$sql .= $id . ' OR term_id = ';
				}
			
				$sql = substr($sql, 0, strlen($sql) - 14);
				$sql .= ' ORDER BY term_id';
				
				$result = $wpdb->query($sql); 
				
				if ($result > 0) {
				
					// At least one category was returned
					$categories = $wpdb->get_results($sql, 'OBJECT');
					
					// UI hooks for showing / hiding links						
					if (isset($live_links) && isset($abandoned_links)) {
						$link_list .= '<ul class="blogrollGoogleCSEshowhide">';
						$link_list .= '<li id="view_live"><a href="#"  title="' . $options['view_live_link_text'] . '" ';
						$link_list .= 'onclick="show_live_links(); return false;">' . $options['view_live_link_text'] . '</a> | </li>';
						$link_list .= '<li id="view_abandoned"><a href="#" title="' . $options['view_abandoned_link_text'] . '" ';
						$link_list .= 'onclick="show_abandoned_links(); return false;">' . $options['view_abandoned_link_text'] . '</a> | </li>';
						$link_list .= '<li id="view_all"><a href="#" title="' . $options['view_all_link_text'] . '" ';
						$link_list .= 'onclick="show_all_links(); return false;">' . $options['view_all_link_text'] . '</a></li>';
						$link_list .= '</ul>';
					} else {
						if (isset($live_links)) {
							$link_list .= '<ul class="blogrollGoogleCSEshowhide">';
							$link_list .= '<li id="view_live"><a href="#" title="' . $options['view_live_link_text'] . '" ';
							$link_list .= 'onclick="show_live_links(); return false;">' . $options['view_live_link_text'] . '</a> | </li>';
							$link_list .= '<li id="view_all"><a href="#" title="' . $options['view_all_link_text'] . '" ';
							$link_list .= 'onclick="show_all_links(); return false;">' . $options['view_all_link_text'] . '</a></li>';
							$link_list .= '</ul>';
						}
						if (isset($abandoned_links)) {
							$link_list .= '<ul class="blogrollGoogleCSEshowhide">';
							$link_list .= '<li id="view_abandoned"><a href="#" title="' . $options['view_abandoned_link_text'] . '" ';
							$link_list .= 'onclick="show_abandoned_links(); return false;">' . $options['view_abandoned_link_text'] . '</a> | </li>';
							$link_list .= '<li id="view_all"><a href="#" title="' . $options['view_all_link_text'] . '" ';
							$link_list .= 'onclick="show_all_links(); return false;">' . $options['view_all_link_text'] . '</a></li>';
							$link_list .= '</ul>';
						}
					}
					
					foreach($categories as $category) {
						
						// Get links for this category
						$sql = "SELECT link_id, link_name, link_url, link_description
								FROM $wpdb->links l, $wpdb->term_relationships r, $wpdb->term_taxonomy tt
								WHERE l.link_id = r.object_id
								AND r.term_taxonomy_id = tt.term_taxonomy_id
								AND tt.term_id = $category->term_id
								ORDER BY l.link_name";
						
						$result = $wpdb->query($sql);
						
						if ($result > 0) {
						
							// Get results
							$links = $wpdb->get_results($sql, 'OBJECT');
							
							// Build output list
							$link_list .= '<h3>' . $category->name . '</h3>';
							
							$link_list .= '<div id="blogroll_google_cse_' . $category->term_id . '">';
							
							$link_list .= '<ul>';
							
							foreach($links as $link) {
							
								// Is this a live or abandoned link?
								if(isset($live_links)) {
									if(in_array($link->link_id, $live_links)) {
										$link_list .= '<li class="blogroll_google_cse_live">';
										$link_list .= '<a href="' . $link->link_url . '" title="' . $link->link_description . '"> ';
										$link_list .= $link->link_name . '</a></li>';
									}
								}
								
								if (isset($abandoned_links)) {
									if(in_array($link->link_id, $abandoned_links)) {
										$link_list .= '<li class="blogroll_google_cse_abandoned">';
										$link_list .= '<a href="' . $link->link_url . '" title="' . $link->link_description . '"> ';
										$link_list .= $link->link_name . '</a></li>';
									}
								}
								
								if(!isset($live_links) && !isset($abandoned_links))
								{
									$link_list .= '<li><a href="' . $link->link_url . '" title="' . $link->link_description . '">';
									$link_list .= $link->link_name . '</a></li>';
								}
							}
							
							// Finish off the list
							$link_list .= '</ul>';
							$link_list .= '</div>';
						}
					}
				}
			}
			
			// show promo link
			if($link_list != '' && $options['show_promo_link'] == 'yes') {
				$link_list .= '<div id="blogroll_google_cse_promo"><p>This list powered by the <a href="http://techxplorer.com/projects/blogroll-google-cse/" target="_blank" title="More information about the plugin" rel="nofollow">Blogroll to Google CSE plugin</a>.</p></div>';
			}
			
			if($link_list != '') {
				return $google_search_box . $link_list;
			} else {
				return $google_search_box;
			}
		
		} // add list of links to page
		
		/**
		 * Function to replace the content place holder with the list of links
		 */
		function add_content_results($content = '') {
		
			// Build the Google Search Box
			// Bring in the Google CSE JavaScript
			require(dirname(__FILE__) . '/google-js.php');
			
			// Get the admin options
			$options = $this->get_admin_options();
				
			// Prepare the Google Search Box
			$google_search_box = str_replace('[search-results]', $options['results_page'], $google_search_box);
			$google_search_box = str_replace('[xml-src]', plugins_url('/blogroll-google-cse/make-cse-xml.php?' . $options['timestamp']), $google_search_box);
			
			// Add ad display parameter
			if ($options['display_ads'] != 'non-profit') {
				$google_search_box = str_replace('[display-ads]', $options['display_ads'], $google_search_box);
			} else {
				$google_search_box = str_replace('[display-ads]', 'FORID:9', $google_search_box);
			}
			
			$google_search_results = str_replace('[frame-width]', $options['frame_width'], $google_search_results);
			
			//Replace the placeholder with the list of tags
			return $google_search_box . $google_search_results;
			
		}
		
		/**
		 * Function to add content to the <head> section of the admin page for the plugin
		 */
		function admin_head() {
		 
			print "<!-- JavaScript and CSS includes for the blogroll-google-cse plugin -->\n";
			
	 		wp_enqueue_script('blogroll-google-cse-farbtastic', plugins_url('/blogroll-google-cse/includes/farbtastic/farbtastic.js'), array('jquery'), '1.0');
			wp_enqueue_script('blogroll-google-cse-colour-well', plugins_url('/blogroll-google-cse/colour-well.js'), array('blogroll-google-cse-farbtastic'), '1.0');
			wp_print_scripts();
			
			// Custom CSS 
			print '<link rel="stylesheet" type="text/css" href="' . plugins_url('/blogroll-google-cse/includes/farbtastic/farbtastic.css') . "\"/>\n";
			print '<link rel="stylesheet" type="text/css" href="' . plugins_url('/blogroll-google-cse/colour-well.css') . "\"/>\n";

		} // add css and javascript code to admin page header
		
		/**
		 * Function to add content to the <head> section of pages
		 */
		function page_head() {
				
			if(is_page()) {
			
				// Get the plugin options
				$options = $this->get_admin_options();
				
				$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				
				// only output custom header if required
				if(parse_url($options['search_page'], PHP_URL_PATH) == $request_path || parse_url($options['results_page'], PHP_URL_PATH) == $request_path) {
							
					print "\n<!-- JavaScript and CSS includes for the blogroll-google-cse plugin -->\n";
				
					// restrict this to just pages
					wp_enqueue_script('blogroll-google-cse', plugins_url('/hide-show.js'), array('jquery'), '1.0');
					wp_print_scripts();
			
					// additional CSS
					print <<<________________________EOS
					<style type="text/css">
						.blogrollGoogleCSEshowhide li
						{
							display: inline;
							list-style-type: none;
						}

						#google_search_results IFRAME {
							width: {$options['frame_width']}px;
						}
					</style>
________________________EOS;
				
				}
			}			
		} // add css and javascript code to page header
		
		/**
		 * Function to update the timestamp
		 * Each time a link is deleted, added, or updated this function will be called
		 * Ensures a forced load of the XML spec when necessary
		 */
		function update_timestamp($link_id) {
		
			// Get the options
			$options = $this->get_admin_options();
			
			// Update the timestamp
			$options['timestamp'] = time();
			
			// Write the updated options
			update_option($this->admin_opt_name, $options);
			
		} // function to update the timestamp option
			
	}
} // class defintion


// Initialise the class
if(class_exists('blogrollGoogleCSE')) {
	$blogroll_google_cse = new blogrollGoogleCSE();
}

// Function to print the admin options panel
if(!function_exists('blogroll_google_cse_options')) {
	function blogroll_google_cse_options() {
		global $blogroll_google_cse;
		
		if (!isset($blogroll_google_cse)) {
			// class instance is missing so just return
			return;
		}
		
		if(function_exists(add_options_page)) {
			$page = add_options_page('Blogroll Google CSE', 'Blogroll Google CSE', 9,
					basename(__FILE__), array(&$blogroll_google_cse, display_admin_page));
			add_action("admin_head-$page", array(&$blogroll_google_cse, 'admin_head'));
		}
	}
}

// Associate with appropriate actions and filters
if(isset($blogroll_google_cse)) {

	// Actions
	// Admin page action
	add_action('admin_menu', 'blogroll_google_cse_options');
	// WP Head generation
	add_action('wp_head', array(&$blogroll_google_cse, 'page_head'));
	// Link Related actions
	add_action('add_link', array(&$blogroll_google_cse, 'update_timestamp'));
	add_action('edit_link', array(&$blogroll_google_cse, 'update_timestamp'));
	add_action('delete_link', array(&$blogroll_google_cse, 'update_timestamp'));
	
	// Filters
	// don't use filters any more, use shortcodes for more flexibility
	if(function_exists('add_shortcode')) {
	
		// search box and list of links
		add_shortcode('search-blogroll-google-cse', array(&$blogroll_google_cse, 'add_content_list'));
		
		// search results
		add_shortcode('results-blogroll-google-cse', array(&$blogroll_google_cse, 'add_content_results'));
	}
}

?>
