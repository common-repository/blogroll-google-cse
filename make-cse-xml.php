<?
/**
 * Generate the Google CSE XML
 */
/*
 * This file is included as part of the WordPress Blogroll to Google CSE plugin
 * The plugin is Copyright (C) 2008 Corey Wallis <techxplorer@gmail.com>
 * The plugin is covered by the GPL. Full details in the header of the 
 * blogroll-google-cse.php file
 */

// Bring in the supporting WordPress infrastructure

// wp-config location
$root_override = '';
if($root_override == '') {
	$root = dirname(dirname(dirname(dirname(__FILE__))));
} else {
	$root = $root_override;
}
	
if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	$config_path = $root.'/wp-load.php';
} else {
	// Before 2.6
	$config_path = $root.'/wp-config.php';
}

require_once($config_path);

// Set the content type
header('Content-Type: application/xml');

// Get the options
$options = get_option('blogroll-google-cse');

// Set other options
$background_label = 'blogroll-google-cse';

// Scope the WordPress database class appropriately
global $wpdb;
		
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
	
} else {

	// End now, nothing else to do
	die;
}

// Build the Facet XML
foreach($categories as $category) {
	$facet_xml .= '<Facet><FacetItem>';
	$facet_xml .= '<Label name="' . $background_label . '-' . $category->term_id .'" mode="FILTER" weight="1">';
	$facet_xml .= '<Rewrite></Rewrite>';
	$facet_xml .= '<IgnoreBackgroundLabels>false</IgnoreBackgroundLabels>';
	$facet_xml .= '</Label>';
	$facet_xml .= '<Title>' . $category->name . '</Title>';
	$facet_xml .= '</FacetItem></Facet>';
}

// Start output buffering
ob_start();

// Build the XML
// Start with the context
// Determine non-profit status
if($options['display_ads'] == 'non-profit') {
	$non_profit = ' nonprofit="true" ';
} else {
	$non_profit = '';
}

echo <<<_____EOS
<?xml version="1.0" encoding="UTF-8" ?>
<GoogleCustomizations>
	<CustomSearchEngine version="1.0" keywords="{$options['google_keywords']}">
		<Title>{$options['google_title']}</Title>
		<Description>{$options['google_title']}</Description>
		<Context>
			{$facet_xml}	
			<BackgroundLabels>
        		<Label name="{$background_label}" mode="FILTER" weight="1" />
        	</BackgroundLabels>
		</Context>
		<LookAndFeel resultsurl="{$options['results_page']}" {$non_profit}>
			<Colors 
				url="{$options['google_lf_url']}"
				background="{$options['google_lf_background']}"
				border="{$options['google_lf_border']}"
				title="{$options['google_lf_title']}"
				text="{$options['google_lf_text']}"
				visited="{$options['google_lf_visited']}"
				light="{$options['google_lf_light']}"
			/>
		</LookAndFeel>
	</CustomSearchEngine>
_____EOS;

// Add the Annotations
print '<Annotations>';

foreach($categories as $category) {


	// Get links for this category
	$sql = "SELECT link_url
			FROM $wpdb->links l, $wpdb->term_relationships r, $wpdb->term_taxonomy tt
			WHERE l.link_id = r.object_id
			AND r.term_taxonomy_id = tt.term_taxonomy_id
			AND tt.term_id = $category->term_id
			ORDER BY l.link_name";
					
	$result = $wpdb->query($sql);
					
	if ($result > 0) {
		
		// Get the entire column
		$links = $wpdb->get_col($sql, 0);
			
		// Output each link
		foreach($links as $link) {
			// Start the tag
			print '<Annotation about="';
			
			// Print the URL 
			$url_toks = parse_url($link);
			
			if(isset($url_toks['host']) && isset($url_toks['path'])) {
				print $url_toks['host'] . dirname($url_toks['path']) . '/*';
			} elseif (isset($url_toks['host'])) {
				print $url_toks['host'] . '/*';
			}
			
			// Close the start of the tag
			print '" score="1">';
			
			// Print label & finish tag
			print '<Label name="' . $background_label . '-' . $category->term_id . '" />';
			print '<Label name="' . $background_label . '" /></Annotation>';
		}
	}
}

// Finalise Annotation list
print "</Annotations>";

// Finalise the XML
print "</GoogleCustomizations>";

// Flush the output buffer
@ob_end_flush();
?>
