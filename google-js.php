<?
/*
 * This file is included as part of the WordPress Blogroll to Google CSE plugin
 * The plugin is Copyright (C) 2008-2009 Corey Wallis <techxplorer@gmail.com>
 * The plugin is covered by the GPL. Full details in the header of the 
 * blogroll-google-cse.php file
 *
 * However, the JavaScript code included in this file is derived from Code provide by Google 
 * and therefore use of the JavaScript code is governed by the Google Custom Search Terms of Service
 *
 */

/**
 * Include file to hold Google JavaScript
 */
 
$google_search_box = <<<________EOS
<!-- Google CSE Search Box Begins  -->
<!-- Use of this code assumes agreement with the Google Custom Search Terms of Service. -->
<!-- The terms of service are available at http://www.google.com/coop/docs/cse/tos.html -->
<!-- Modified to add additional style possibilities -->
<div id="lintlinkcsesearchbox">
<form id="cref_iframe" action="[search-results]">
  <input type="hidden" name="cref" value="[xml-src]" />
  <input type="hidden" name="cof" value="[display-ads]" />
  <input type="text" name="q" size="40" />
  <input type="submit" name="sa" value="Search" />
</form>
<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=cref_iframe"></script>
</div>
<!-- Google CSE Search Box Ends -->
________EOS;

$google_search_results = <<<________EOS
<div id="google_search_results" style="width: [frame-width]px">
<!-- Google Search Result Snippet Begins -->
<div id="results_cref"></div>
<script type="text/javascript">
  var googleSearchIframeName = "results_cref";
  var googleSearchFormName = "searchbox_cref";
  var googleSearchFrameWidth = [frame-width];
  var googleSearchFrameborder = 0;
  var googleSearchDomain = "www.google.com";
  var googleSearchPath = "/cse";
</script>
<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>
<!-- Google Search Result Snippet Ends -->
</div>
________EOS;



?>
