<?php

//////////////////////////////////////////////////////////////
//
//    Copyright (C) Thomas Kock, Delmenhorst, 2008, 2009
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

// Get current category name
function fchw_GetCurrentCategory($Title) {
//    global $wgTitle;
	global $wgDBprefix;
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->query( "select cl_to from ".$wgDBprefix."categorylinks where cl_sortkey = '".$dbr->strencode($Title)."'");
    $count = $dbr->numRows( $res );
    if ($count > 0 ) {
	while ($row = $dbr->fetchObject( $res )) {
    	    return $row->cl_to;
	}
    }
    return "";
}

// Get list of categories
function fchw_GetCategories() {
	global $wgDBprefix;
    $CatBroCategories = NULL;
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->query( "select cl_to from ".$wgDBprefix."categorylinks group by cl_to order by cl_to");
    $count = $dbr->numRows( $res );
    if ($count > 0 ) {
	while ($row = $dbr->fetchObject( $res )) {
	    $CatBroCategories[$row->cl_to] = 1;	
	}
    }
    return $CatBroCategories;
}

// Get list of redirected pages
function fchw_GetRedirectedPages() {
	global $wgDBprefix;
    $RedirectedPages = NULL;
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->query( "select rd_from, page2.page_id, rd_title from ".$wgDBprefix."redirect left outer join ".$wgDBprefix."page page2 on page2.page_title = ".$wgDBprefix."redirect.rd_title");
    $count = $dbr->numRows( $res );
    if ($count > 0 ) {
	while ($row = $dbr->fetchObject( $res )) {
	    $RedirectedPages[$row->rd_from]['to_id'] = $row->page_id;	
	    $RedirectedPages[$row->rd_from]['to_title'] = $row->rd_title;	
	}
    }
    
    while (true) {
	$Counter = 0;
	if (is_array($RedirectedPages)) {
            foreach($RedirectedPages as $Key=>$Value) {
	        if (isset($RedirectedPages[$Value['to_id']])) {
		    if ($RedirectedPages[$Key]['to_id'] <> $RedirectedPages[$Value['to_id']]['to_id']) {
	    	        $RedirectedPages[$Key]['to_id'] = $RedirectedPages[$Value['to_id']]['to_id'];
			$RedirectedPages[$Key]['to_title'] = $RedirectedPages[$Value['to_id']]['to_title'];
			$Counter++;
		    }
		}
	    }
	} else break;
	if ($Counter == 0) 
	    break;
    }	
    return $RedirectedPages;
}

// Get ModelType for specified category
function fchw_GetCategoryModelType($Category) {
	global $wgDBprefix;
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->query("select to_title from ".$wgDBprefix."fchw_relation where from_title like '".$dbr->strencode($Category)."' and relation='ModelType'");
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
	$row = $dbr->fetchObject( $res );
	return $row->to_title;
    }
    return "";
}

// Get graph definitions for specified model type
//  Customizing:Configure_<ChartType>
//  Customizing:Configure_Chart with <ChartType> inside this page. 
function fchw_GetGraphDefinitions($ModelType) {
	global $wgDBprefix;
    global $wgDBtype;
    $text = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $TablePage 		= $dbr->tableName( 'page' );
    if ($wgDBtype == "postgres") 
        $TablePageContent	= $dbr->tableName( 'pagecontent' );
    else
        $TablePageContent	= $dbr->tableName( 'text' );
    $TableRevision 	= $dbr->tableName( 'revision' );
    $ModelTypeText = "";
    $res = $dbr->query( "select page_title, old_text from ".$wgDBprefix."page inner join $TableRevision on $TableRevision.rev_id=$TablePage.page_latest inner join $TablePageContent on $TablePageContent.old_id = $TableRevision.rev_text_id where page_title = 'Customizing:Configure_$ModelType';");
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
	$row = $dbr->fetchObject( $res );
	$ModelTypeText = $row->old_text;
    } else {
        $res = $dbr->query( "select page_title, old_text from ".$wgDBprefix."page inner join $TableRevision on $TableRevision.rev_id=$TablePage.page_latest inner join $TablePageContent on $TablePageContent.old_id = $TableRevision.rev_text_id where page_title = 'Customizing:Configure_Chart';");
	$count = $dbr->numRows( $res );
	if( $count > 0 ) {
	    $row = $dbr->fetchObject( $res );
	    $ModelTypeText = $row->old_text;
	}
    }
    // parser
    $CatBroDef = NULL;
    $CatBroDef['nodes'] 	= NULL;
    $CatBroDef['arrows'] 	= NULL;
    $Lines = explode("\n", $ModelTypeText);
    $StartConfigBlock = false;
    $StartModelTypeBlock = false;
    $Type = "";
    foreach ($Lines as $value) {
	$LineUpper = strtoupper($value);
	$LineUpperNoSpace = str_replace(" ", "", strtoupper($value));
	// find == configuration block
	if (($StartConfigBlock == true) && (strpos($LineUpperNoSpace, "==") === 0)) {
	    $StartConfigBlock = false;
	}
	if (($StartConfigBlock == false) && (strpos($LineUpperNoSpace, "==CONFIGURATION") === 0)) {
	    $StartConfigBlock = true;
	}
	if (($StartModelTypeBlock == true) && (strpos($LineUpper, "*") === 0) && (strpos($LineUpper, "*", 1) != 1)) {
	    $StartModelTypeBlock = false;
	}
	if (($StartConfigBlock == true) && (strpos($LineUpperNoSpace, "*CONFIGURE_".strtoupper($ModelType)) === 0)) {
	    $StartModelTypeBlock = true;
	}
        // parse def.
	if (($StartModelTypeBlock == true) && ($LineUpper == "**NODES")) {
	    $Type = "nodes";
	}
	if (($StartModelTypeBlock == true) && ($LineUpper == "**ARROWS")) {
	    $Type = "arrows";
	}
	if (($StartModelTypeBlock == true) && (strpos($LineUpper, "***") === 0) && ($Type == "nodes")) {
	    $Ele = explode (" ", OneSpaceOnly($value));
	    if (isset($Ele[0]) && isset($Ele[1]) && isset($Ele[2])) {
    	        $Ele[0] = str_replace("*", "", $Ele[0]);
		$CatBroDef[$Type][$Ele[0]]['Visible'] 		= true;
		$CatBroDef[$Type][$Ele[0]]['Shape'] 			= $Ele[1];
		$CatBroDef[$Type][$Ele[0]]['BackColor'] 		= $Ele[2];
		if (isset( $Ele[3] ))
		    $CatBroDef[$Type][$Ele[0]]['FontColor'] 		= $Ele[3]; else
	    	    $CatBroDef[$Type][$Ele[0]]['FontColor'] 		= "black";
	    }
	}
	if (($StartModelTypeBlock == true) && (strpos($LineUpper, "***") === 0) && ($Type == "arrows")) {
	    $Ele = explode (" ", OneSpaceOnly($value));
	    if (isset($Ele[0]) && isset($Ele[1]) && isset($Ele[2])) {
    	        $Ele[0] = str_replace("*", "", $Ele[0]);
		$CatBroDef[$Type][$Ele[0]]['Visible'] 		= true;
		$CatBroDef[$Type][$Ele[0]]['Shape'] 			= $Ele[1];
		$CatBroDef[$Type][$Ele[0]]['Color'] 		= $Ele[2];
		if (isset( $Ele[3] ))
		    $CatBroDef[$Type][$Ele[0]]['Style'] 		= $Ele[3]; else
	    	    $CatBroDef[$Type][$Ele[0]]['Style'] 		= "solid";
		if (isset( $Ele[4] ))
		    $CatBroDef[$Type][$Ele[0]]['Label'] 		= str_replace("_", " ", $Ele[4]); else
	    	    $CatBroDef[$Type][$Ele[0]]['Label'] 		= "";
	    }
	}
//	text .= "$value <br />";
    }
//    print_r($CatBroDef);
//    die();
    $dbr->freeResult( $res );
    return $CatBroDef;
}

// get current level
function fchw_GetCurrentLevel() {
	global $wgDBprefix;
    global $fchw, $wgTitle, $wgParser; 
    $dbr =& wfGetDB( DB_SLAVE );      
    $page = $dbr->tableName( 'page' );
    $CategoryLinks = $dbr->tableName( 'categorylinks' );
    $title = $wgParser->getTitle()->mTextform;                     
    $res = $dbr->query( "SELECT rel1.to_title as level  FROM $page ".
       "LEFT OUTER JOIN  $CategoryLinks ON $CategoryLinks.cl_from = $page.page_id ".
       "LEFT OUTER JOIN ".$wgDBprefix."fchw_relation rel1 ON (rel1.from_id = $page.page_id) and (rel1.relation = 'Level') ".
       "WHERE $CategoryLinks.cl_to = '".$fchw['CurrentCategory']."' AND $page.page_title = '".$dbr->strencode($title)."'" );            
    $count = $dbr->numRows( $res );                                                                                   
    if( $count > 0 ) {                                                                                                
        $row = $dbr->fetchObject( $res );                                                                             
        return $row->level;                                                                           
    }                                                                                                                 
    $dbr->freeResult( $res );                                                                                         
    return 	0;	
}

// get array with all used levels
function fchw_GetLevels($Category) {
	global $wgDBprefix;
    $Levels = "";                                                                                              
    $dbr =& wfGetDB( DB_SLAVE );      
    $page = $dbr->tableName( 'page' );
    $CategoryLinks = $dbr->tableName( 'categorylinks' );
    $res = $dbr->query("SELECT rel1.to_title as level from $page LEFT OUTER JOIN $CategoryLinks ON $CategoryLinks.cl_from = 
$page.page_id ".
        "LEFT OUTER JOIN ".$wgDBprefix."fchw_relation rel1 ON (rel1.from_id = $page.page_id) and (rel1.relation = 'Level') ".                         
        "WHERE $CategoryLinks.cl_to = '".$Category."' group by rel1.to_title order by rel1.to_title");               
    $count = $dbr->numRows( $res );                                                                                                             
    if( $count > 0 ) {                                                                                                                          
        while ($row = $dbr->fetchObject( $res )) {                                                                                              
            if (Trim($row->level) != "") {                                                                                                      
                $Levels[$row->level] = 1;                                                                                                
            }                                                                                                                                   
        }                                                                                                                                       
    }           
    $dbr->freeResult( $res );                                                                                                                   
    return $Levels;
}      



// get array with near levels /+-2/
function fchw_GetNearLevels($Levels, $CurrentLevel, $Minus = 2, $Plus = 2) {
    $NearLevels = FALSE;
    if (is_array($Levels)) {
        krsort($Levels);                                                                                                                      
	$found = 0;                                                                                                                                 
	foreach ($Levels as $key => $value) {                                                                                                
    	if (($key == $CurrentLevel) || ($found > 0)) {                                                                                   
    		$found++;                                                                                                                           
        	if ($found <= $Minus+1)                                                                                                                    
            	    $NearLevels[$key] = $found;                                                                                              
    	    }                                                                                                                                       
	}                                                                                                                                           
	ksort($Levels);                                                                                                                      
	$found = 0;                                                                                                                                 
	foreach ($Levels as $key => $value) {                                                                                                
    	    if (($key == $CurrentLevel) || ($found > 0)) {                                                                                   
        	$found++;                                                                                                                           
        	if ($found <= $Plus+1)                                                                                                                    
            	    $NearLevels[$key] = $found;                                                                                              
    	    }                                                                                                                                       
	}                                                                                                                                           
    }
    return $NearLevels;
}                               

// get array with pages
function fchw_GetPages() {
	global $wgDBprefix;
    $Pages = FALSE;                                                                                              
    $dbr =& wfGetDB( DB_SLAVE );      
    $page = $dbr->tableName( 'page' );
    $res = $dbr->query("select page_id, page_title from $page order by page_title");
    $count = $dbr->numRows( $res );                                                                                                             
    if( $count > 0 ) {                                                                                                                          
        while ($row = $dbr->fetchObject( $res )) {                                                                                              
            if (Trim($row->page_title) != "") {                                                                                                      
                $Pages[$row->page_title] = $row->page_id;                                                                                                
            }                                                                                                                                   
        }                                                                                                                                       
    }           
    $dbr->freeResult( $res );                                                                                                                   
    return $Pages;
}      

// Preload pages
function fchw_PreloadPage($Title) {
    if ($Title === '')
	return '';
    else {
        $preloadTitle = Title::newFromText( $Title );
        if ( isset( $preloadTitle ) && $preloadTitle->userCanRead() ) {
            $rev=Revision::newFromTitle($preloadTitle);
            if ( is_object( $rev ) ) {
                $text = $rev->getText();
                return $text;
            } else
        	return '';
        }
    }
}

// return string with compressed spaces into one
function OneSpaceOnly($Input) {
    $text = "";                
    $lastch = "";              
    $Len = strlen($Input);     
    for($i=0; $i<$Len; $i++) { 
        $ch = $Input[$i];      
        if (($ch == " ") && ($lastch == " ")) 
            continue;                         
        $text .= $ch;                         
        $lastch = $ch;                        
    }                                         		
    return $text;                             
}    

// HASH replacement - if function doesn't exists
if (!function_exists('hash_algos')) {
    function hash_algos() {
        $algo[0] = "md5";
        $algo[1] = "sha1";
        $algo[2] = "crc32";
        return($algo);
    } 
}

if (!function_exists('hash')) {
    function hash($algo, $data, $raw_output = 0) {
        if($algo == 'md5') return(md5($data));
        if($algo == 'sha1') return(sha1($data));
        if($algo == 'crc32') return(crc32($data));
    }
}

// get array with pagenames
function fchw_GetPageNames($Category) {
	global $wgDBprefix;
    $PageNames = "";                                                                                              
    $dbr =& wfGetDB( DB_SLAVE );      
    $page = $dbr->tableName( 'page' );
    $CategoryLinks = $dbr->tableName( 'categorylinks' );
    $res = $dbr->query("SELECT from_title, rel1.to_title as level from $page LEFT OUTER JOIN $CategoryLinks ON $CategoryLinks.cl_from = 
$page.page_id ".
        "LEFT OUTER JOIN ".$wgDBprefix."fchw_relation rel1 ON (rel1.from_id = $page.page_id) and (rel1.relation = 'PageName') ".                         
        "WHERE $CategoryLinks.cl_to = '".$Category."' group by rel1.to_title order by rel1.to_title");               
    $count = $dbr->numRows( $res );                                                                                                             
    if( $count > 0 ) {                                                                                                                          
        while ($row = $dbr->fetchObject( $res )) {                                                                                              
            if (Trim($row->level) != "") {                                                                                                      
                $PageNames[$row->from_title] = $row->level;                                                                                                
            }                                                                                                                                   
        }                                                                                                                                       
    }           
    $dbr->freeResult( $res );                                                                                                                   
    return $PageNames;
}      

// translate pagename
function fchw_TranslatePageName($Page) {
    global $fchw;
    $Page2 = str_replace("_", " ", $Page);
    if (isset($fchw['PageNames'][$Page2]))
	return $fchw['PageNames'][$Page2];
      else
        return $Page;
}

