<?php

//////////////////////////////////////////////////////////////
//
//    Copyright (C) Bratislava 2008 Thomas Kock                                         
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
// http://www.gnu.org/copyleft/gpl.html
//
//////////////////////////////////////////////////////////////

$wgExtensionFunctions[] = "wfCategoryBrowserExtension";
$wgHooks['ArticleSaveComplete'][] = 'wfCategoryBrowserSaveComplete';
function wfCategoryBrowserExtension() {
	global $wgParser;
	$wgParser->setHook( "CategoryBrowser", "renderCategoryBrowser1" );
	$wgParser->setHook( "CategoryBrowser2", "renderCategoryBrowser2" );
}

function findLevelRanking($FullGraph) {
    global $fchw;
    $output = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $relations = $dbr->tableName( 'fchw_relation' );
    $Categorylinks = $dbr->tableName( 'categorylinks' );
    $sql = "SELECT from_title, relation, to_title as level FROM $relations ".
      " LEFT OUTER JOIN ${Categorylinks} ON (${Categorylinks}.cl_from = from_id) ".
      " WHERE $Categorylinks.cl_to = '".$fchw['CurrentCategory']."' and (not (${Categorylinks}.cl_sortkey like '%:Category:".$fchw['CurrentCategory']."')) and relation='Level' ".
      " group by from_title, relation, to_title order by to_title, from_title LIMIT 500";
    $res = $dbr->query( $sql );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
	$group = -1;
	$groupcache = ""; 
	while( $row = $dbr->fetchObject( $res ) ) {
	    if ((!$FullGraph) && (!isset($fchw['NearLevels'][$row->level])))
	      continue;
//	    if (isset($fchw['Categories'][$row->from_title]))
//	      continue;
	    if ($group != $row->level) {
		if ($groupcache != "") {
		    $groupcache .= " }\n";
		    $output .= $groupcache;
		}	  
		$group = $row->level;
		$groupcache = "{ rank = same; ";
	    }
	    $groupcache .= "\"".$row->from_title."\"; ";
	}
    	if ($groupcache != "") {
	    $groupcache .= " }\n";
	    $output .= $groupcache;
	}	  
    }
    $dbr->freeResult( $res );
    return $output;
}

function findPages($FullGraph) {
    global $fchw, $wgParser, $wgScriptPath, $wgScriptExtension;
    $path = $wgScriptPath."/index".$wgScriptExtension;
    $output = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $page = $dbr->tableName( 'page' );
    $CategoryLinks = $dbr->tableName( 'catlinks' );
    $res = $dbr->query( "SELECT $page.page_namespace, $page.page_title, $page.page_is_redirect, $CategoryLinks.cl_to, rel1.to_title, rel2.to_title as level  FROM $page ".
       "LEFT OUTER JOIN categorylinks $CategoryLinks ON $CategoryLinks.cl_from = $page.page_id ".
       "LEFT OUTER JOIN fchw_relation rel1 ON (rel1.from_id = $page.page_id) and (rel1.relation = 'Type') ".
       "LEFT OUTER JOIN fchw_relation rel2 ON (rel2.from_id = $page.page_id) and (rel2.relation = 'Level') ".
       "WHERE $CategoryLinks.cl_to = '".$fchw['CurrentCategory']."' and (not (${CategoryLinks}.cl_sortkey like '%:Category:".$fchw['CurrentCategory']."'))".
       "group by $page.page_namespace, $page.page_title, $page.page_is_redirect, $CategoryLinks.cl_to, rel1.to_title, rel2.to_title ORDER BY $page.page_title  LIMIT 500" );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
	while( $row = $dbr->fetchObject( $res ) ) {
	    if ($row->page_title == "Type") 
		continue;
	    if ((!$FullGraph) && (!isset($fchw['NearLevels'][$row->level])))
	      continue;
	    $params = "";
	    $params .= "URL=\"".((isset($fchw['Categories'][$row->page_title])) ? "$path/Category:" : "").$row->page_title."\",";
	    $color  = "";
/*	    // OBJECT TYPE
	    if ($row->to_title == "Event") {
	       $params .= "shape=hexagon,";
	       $color .= "color=azure3, fontcolor=black, style=filled,";
	    }
	    if ($row->to_title == "Decision") { 
	       $params .= "shape=diamond,";
	       $color .= "color=azure3, fontcolor=black, style=filled,";
	    }
	    if ($row->to_title == "Function") {
	       $params .= "shape=parallelogram,";
	       $color .= "color=azure3, fontcolor=black, style=filled,";
	    }
	    if ($row->to_title == "DataSource") {
	       $params .= "shape=rect,";
	       $color .= "color=khaki1, fontcolor=black, style=filled,";
	    }
	    if ($row->to_title == "Person") {
	       $params .= "shape=box,";
	       $color .= "color=chartreuse1, fontcolor=black, style=filled,";
	    }
	    if ($row->to_title == "Department") {
	       $params .= "shape=ellipse,";
	       $color .= "color=chartreuse1, fontcolor=black, style=filled,";
	    }
	    if ($row->to_title == "Product") {
	       $params .= "shape=rect,";
	       $color .= "color=yellow, fontcolor=black, style=filled,";
	    }
*/
	    if (isset($fchw['GraphDefs'][$row->to_title])) {		    
	       $params .= "shape=".$fchw['GraphDefs'][$row->to_title]['Shape'].",";
	       $color .= "color=".$fchw['GraphDefs'][$row->to_title]['BackColor'].", fontcolor=".$fchw['GraphDefs'][$row->to_title]['FontColor'].", style=filled,";
	    }
	    // SELECT CURRENT PAGE
	    if ($row->page_title == $fchw['CurrentPage2']) 
	       $color .= "color=black, fontcolor=white, style=filled, ";
	    $params .= $color;
	    $output .= "\"". $row->page_title."\" [".$params."];\n";
	}
    }
    $dbr->freeResult( $res );
    return $output;
}

function findLinks($FullGraph) {
    global $fchw;
    $output = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $relations = $dbr->tableName( 'fchw_relation' );
    $CategoryLinks = $dbr->tableName( 'categorylinks' );
    $sql = "SELECT rel0.from_title, rel0.relation, rel0.to_title, rel1.to_title as level1, rel2.to_title as level2 FROM $relations rel0 ".
      " LEFT OUTER JOIN $CategoryLinks ON ($CategoryLinks.cl_from = rel0.from_id) ".
       "LEFT OUTER JOIN fchw_relation rel1 ON (rel1.from_id = rel0.from_id) and (rel1.relation = 'Level') ".
       "LEFT OUTER JOIN fchw_relation rel2 ON (rel2.from_id = rel0.to_id) and (rel2.relation = 'Level') ".
      " WHERE $CategoryLinks.cl_to = '".$fchw['CurrentCategory']."' and (not (categorylinks.cl_sortkey like '%:Category:".$fchw['CurrentCategory']."')) and (rel0.relation <> 'ModelType') ".
      " group by rel0.from_title, rel0.relation, rel0.to_title, rel1.to_title, rel2.to_title order by rel0.from_title, rel0.to_title LIMIT 500";
    $res = $dbr->query($sql );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
	while( $row = $dbr->fetchObject( $res ) ) {
	    if (!$FullGraph) {
	      if (((!isset($fchw['NearLevels'][$row->level1]))) || 
	          ((!isset($fchw['NearLevels'][$row->level2]))))
	      continue;
	    }
	    if ($row->relation == "Type") 
	      continue;
	    if ($row->relation == "Level") 
	      continue;
//	    if ((isset($fchw['Categories'][$row->from_title])) ||  
//	        (isset($fchw['Categories'][$row->from_title]))) 
//	      continue;
	    $output .= $row->from_title."->".$row->to_title.";\n";
	}
    }
    $dbr->freeResult( $res );
    return $output;
}

function renderCategoryBrowser1($input, $params, &$parser) {
    return renderCategoryBrowser($input, $params, $parser, 1);
}

function renderCategoryBrowser2($input, $params, &$parser) {
    return renderCategoryBrowser($input, $params, $parser, 2);
}

function renderCategoryBrowser($input, $params, &$parser, $Mode)
{
    global $fchw, $wgTitle, $wgUploadDirectory, $wgParser;
    $html = "";
    $fchw['Categories'] = fchw_GetCategories();
    if (is_object($wgTitle))
        $myTitle = $wgTitle->mPrefixedText;
      else
        $myTitle = $wgTitle;
    $fchw['CurrentCategory'] = fchw_GetCurrentCategory($myTitle);    
    $GraphFileName = $myTitle;
    if ($wgTitle->mNamespace == NS_CATEGORY) {
//    if (strpos(strtoupper($GraphFileName), strtoupper($wgCanonicalNamespaceNames[NS_CATEGORY]).":") === 0) {
	$fchw['CurrentCategory'] = substr($GraphFileName, strpos($GraphFileName, ":")+1);
    }
    if ($input) {
        $fchw['CurrentCategory'] = $input;
	$GraphFileName = $myTitle."_".$input;
    }    
    $fchw['CurrentPage']		= $wgParser->getTitle()->mTextform;
    $fchw['CurrentPage2']	= $fchw['CurrentPage'];
    if (!(strpos($fchw['CurrentPage'], ":") === FALSE)) {
	$fchw['CurrentPage2'] = substr($fchw['CurrentPage'], strpos($fchw['CurrentPage'], ":")+1);
    }
    if ($fchw['CurrentPage'] != $myTitle) {
	$GraphFileName .= "_".$fchw['CurrentPage'];
    }
//    $html = "CURRENT PAGE:: ".$fchw['CurrentPage']." CURRENT PAGE2:: ".$fchw['CurrentPage2']."CURRENT CATEGORY:: ".$fchw['CurrentCategory']." myTitle $myTitle<br />";
    $fchw['CurrentLevel'] 	= fchw_GetCurrentLevel();
    $fchw['Levels'] 		= fchw_GetLevels($fchw['CurrentCategory']);
    $fchw['NearLevels'] 		= fchw_GetNearLevels($fchw['Levels'], $fchw['CurrentLevel']);
    $fchw['GraphDefs'] 		= fchw_GetGraphDefinitions(fchw_GetCategoryModelType($fchw['CurrentCategory']));
    $GraphHeight = count($fchw['Levels']) * 0.6;
    if (($fchw['CurrentLevel'] == "") || ($Mode == 1)) {
	$output  = "digraph G { size =\"7,$GraphHeight\"; ".findLevelRanking(true).findPages(true).findLinks(true)."}";
	$html    .= Graphviz($GraphFileName, $output);
//	    $html    .= "<pre>$output</pre>";
    } else {
        $html    .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td width='10' style='padding-right: 16px' valign='top'>";
	$output  = "digraph G { size =\"3,$GraphHeight\"; ".findLevelRanking(true).findPages(true).findLinks(true)."}";
	$html    .= Graphviz($GraphFileName."_process", $output);
//	    $html    .= "<pre>$output</pre>";
	$html    .= "</td><td valign='top'>";
	$output  = "digraph G { size =\"5,$GraphHeight\"; ".findLevelRanking(false).findPages(false).findLinks(false)."}";
	$html    .= Graphviz($GraphFileName, $output);
//	    $html    .= "<pre>$output</pre>";
//    $html    .= $output;
	$html    .= "</td></tr></table>";
    }
	return $html;
//    return $output;
}

function wfCategoryBrowserSaveComplete(&$article, &$user, &$text, &$summary, &$minoredit, &$watchthis, &$sectionanchor, &$flags, $revision) {
    return true;
}


