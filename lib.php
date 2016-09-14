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
    global  $wgVersion;
    $dbr = wfGetDB( DB_SLAVE );
    if (version_compare( $wgVersion, '1.17.0')>=0) {
      $collation=Collation::singleton();
      $searchTitle=$collation->getSortKey($Title);
    } else {
      $searchTitle=$Title;
    }
    $cat = $dbr->selectField(
        'categorylinks',
        'cl_to',
        [ 'cl_sortkey' => $searchTitle ],
        __FUNCTION__
    );
    return ( $cat || "" );
}

// Get list of categories
function fchw_GetCategories() {
    $CatBroCategories = NULL;
    $dbr = wfGetDB( DB_SLAVE );
    $catList = $dbr->selectFieldValues(
        'categorylinks',
        'cl_to',
        array(),
        __FUNCTION__,
        'DISTINCT'
    );
    foreach ($catList as $cat) {
        $CatBroCategories[$cat] = 1;
    }
    return $CatBroCategories;
}

// Get list of redirected pages
function fchw_GetRedirectedPages() {
    $RedirectedPages = NULL;
    $dbr = wfGetDB( DB_SLAVE );

    $res = $dbr->select(
        [ 'redirect' ],
        [ 'rd_from', 'rd_title', 'page_id' ],
        [ ],
        __FUNCTION__,
        [ 'LEFT OUTER JOIN' => [ 'page' => 'page.page_title = redirect.rd_title' ] ]
    );

    foreach ( $res as $row ) {
        $RedirectedPages[$row->rd_from]['to_id']    = $row->page_id;
        $RedirectedPages[$row->rd_from]['to_title'] = $row->rd_title;
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
    $dbr = wfGetDB( DB_SLAVE );
    $title = $dbr->selectField(
        'fchw_relation',
        'to_title',
        [ $dbr->buildLike('from_title', $Category),
          'relation' => 'ModelType' ],
        __FUNCTION__
    );
    return ($title || "");
}

// Get graph definitions for specified model type
//  Customizing:Configure_<ChartType>
//  Customizing:Configure_Chart with <ChartType> inside this page.
function fchw_GetGraphDefinitions($ModelType) {
    $ModelTypeText = "";

    $dbr = wfGetDB( DB_SLAVE );
    $res = $dbr->select(
        'page',
        [ 'page_title', 'old_text' ],
        [ 'page_title' => "Customizing:Configure_".$ModelType ],
        __FUNCTION__,
        array(),
        [ 'revision' => [ 'INNER JOIN', 'rev_id=page_latest' ],
          'text'     => [ 'INNER JOIN', 'old_id=rev_text_id' ],
        ]
    );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        $row = $dbr->fetchObject( $res );
        $ModelTypeText = $row->old_text;
    } else {
    $res = $dbr->select(
        'page',
        [ 'page_title', 'old_text' ],
        [ 'page_title' => 'Customizing:Configure_Chart' ],
        __FUNCTION__,
        array(),
        [ 'revision' => [ 'INNER JOIN', 'rev_id=page_latest' ],
          'text'     => [ 'INNER JOIN', 'old_id=rev_text_id' ],
        ]
    );
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
    //print($ModelType . "<br>");
    //print_r($CatBroDef);
    //die();
    return $CatBroDef;
}

// get current level
function fchw_GetCurrentLevel() {
    global $fchw, $wgTitle, $wgParser;
    if (!isset($fchw['Pages'])) {
        $fchw['Pages'] = fchw_LoadPages();
    }
    $title = $wgParser->getTitle()->mTextform;
    $pages = $fchw['Pages'];
    if (isset($pages[$title])) {
       $p = $pages[$title];
    } else { $p = NULL; }
    if ($p != NULL) {
        return $p->level;
    } else {
        return 0;
    }
}

// get array with all used levels
// $all = false will only retrieve the Levels where Pages have been assigned
//        and will omit the 'zzzzzzzz' level with all the pages that have no level assigned.
function fchw_GetLevels($all=true) {
    global $fchw;
    if (!isset($fchw['Pages'])) {
        $fchw['Pages'] = fchw_LoadPages();
    }
    $pages = $fchw['Pages'];
    $Levels = array();
    foreach($pages as $page) {
        if (Trim($page->level) != "") {
            $l = (string) $page->level;
            if (! isset($Levels[$l])) {
                $Levels[$l] = array();
            }
            $Levels[$l][]=$page->pageName;
        } else {
            if ($all == true) {
                if (! isset($Levels['zzzzzzzz'])) {
                    $Levels['zzzzzzzz'] = array();
                }
                $Levels['zzzzzzzz'][]=$page->pageName;
            }
        }
    }
    ksort($Levels, SORT_STRING);
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
    $Pages = FALSE;
    $dbr = wfGetDB( DB_SLAVE );
    $page = $dbr->tableName( 'page' );
    $res = $dbr->query("SELECT page_id, page_title FROM $page ORDER BY page_title");
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        while ($row = $dbr->fetchObject( $res )) {
            if (Trim($row->page_title) != "") {
                $Pages[$row->page_title] = $row->page_id;
            }
        }
    }
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

// translate pagename
function fchw_TranslatePageName($Page) {
    global $fchw;
    if (!isset($fchw['Pages'])) {
        $fchw['Pages'] = fchw_LoadPages();
    }
    $Page2 = str_replace("_", " ", $Page);
    if (isset($fchw['Pages'][$Page2])) {
       $p = $fchw['Pages'][$Page2];
    } else { $p=NULL; }
    if ($p != NULL) {
        return $p->getTranslatedName();
    } else {
        return $Page;
    }
}

// Load all the Pages and Links for the current Graph into memory
// added Bugfix by Hiroyuki S.
function fchw_LoadPages() {
    global $fchw;
    $dbr = wfGetDB( DB_SLAVE );

    // Step 1: Load all Pages (Just the PageNames) and create Objects of them.
    $res = $dbr->select(
        [ 'categorylinks', 'page' ],
        [ 'cl_from', 'page_title' ],
        [ 'cl_to' => $fchw['CurrentCategory'],
          'page_id = cl_from'
        ],
        __FUNCTION__
    );
    foreach ( $res as $row ) {
        $p = new FchwPage($row->page_title, $row->cl_from);
        $pages[$row->page_title] = $p;
        $ids[$row->cl_from] = $row->page_title;
    }
    $dbr->freeResult( $res );


    // Step 2: Add Details to the Objects
    $id_list = array_keys($ids);
    $res = $dbr->select(
        'fchw_relation',
        '*',
        [ 'from_id' => $id_list ],
        __FUNCTION__
    );
    foreach ( $res as $row ) {
        $p =& $pages[ $ids->from_id ];
        if ($p) {
                switch ($row->relation) {
                    case "Level":
                        $p->level = $row->to_title;
                        break;
                    case "PageName":
                        $p->displayName = $row->to_title;
                        break;
                    case "Type":
                        $p->pageType = $row->to_title;
                        break;
                    default: // This is a link to another page
                    //$fromId = $row->from_id;
                    //$fromTitle = $row->from_title;
                    //$to_id  = $row->to_id;
                    //$toTitle = $row->to_title;
                    //$linkType = $row->relation;
                        $l = new FchwLink($row->from_id, $row->from_title, $row->to_id, $row->to_title, $row->relation);
                        $p->links[] = $l;
                }
        }
    }

    return $pages;
}
