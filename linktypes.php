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

if (!defined('MEDIAWIKI'))
    die();

// Translate links    
$wgHooks['InternalParseBeforeLinks'][] = 'fchw_ParseLinks';
// Each page save - store relations for current page
$wgHooks['LinksUpdateConstructed'][] = 'fchw_UpdateLinks';	
// Delete page - delete relations
$wgHooks['ArticleDelete'][] = 'fchw_DeleteLinks'; 
// Move page - move relations
$wgHooks['TitleMoveComplete'][]='fchw_MoveLinks'; 
// Undelete page - recreate relations
$wgHooks['ArticleUndelete'][] = 'fchw_UndeleteLinks';

global $fchw;
$fchw['ParseLinks'] = 1;

// parse links when displaying page
function fchw_ParseLinks(&$parser, &$text) {
    global $fchw;
    if ($fchw['ParseLinks'] == 0) 
	return true;
    $output = "";
    $lastch = "";
    $LinkStarted = false;
    $Len = strlen($text);
    for($i=0; $i<$Len; $i++) {
	$ch = $text[$i];
        if (($ch == "[") && ($lastch == "[")) {
	    $LinkStarted = true;
	    $LinkText = "";
	}
        if (($ch == "]") && ($lastch == "]")) {
	    $LinkStarted = false;
	    if (strpos($LinkText, "::") > 0) {
		$Relation = substr($LinkText, 1, strpos($LinkText, "::")-1);
		$LinkTmp = substr($LinkText, strpos($LinkText, "::")+2, -1);
		$output .= "[$LinkTmp|$Relation::$LinkTmp]";
	    } else {
		$output .= $LinkText;
	    }
	}
	if ($LinkStarted) 
	    $LinkText .= $ch; else
	    $output .= $ch;
	$lastch = $ch;
    }
    $text = $output;
    return true;
}

// each save - fill relations table for current page
function fchw_UpdateLinks($linksupdate) {
    global $fchw, $wgParser;
    if ($fchw['ParseLinks'] == 0) 
	return true;
    $fchw['ParseLinks'] = 0;
    $options = new ParserOptions();
    $title = Title::newFromID($linksupdate->mId);
    if (($title === NULL)) continue;
    $revision = Revision::newFromTitle($title);
    if ($revision === NULL) continue;
    $parserOutput = $wgParser->parse($revision->getText(), $title, $options, true, true, $revision->getID());
    $linksupdate = new LinksUpdate($title, $parserOutput);
    $linksupdate->doUpdate();
    
    $fchw['Pages'] = fchw_GetPages();
    $fchw['dbr'] = &wfGetDB(DB_SLAVE);
    $fchw['table_relation'] = $fchw['dbr']->tableName('fchw_relation');
    $sql = "delete from ".$fchw['table_relation']." where from_title like '".$linksupdate->mTitle->mPrefixedText."'";
    $res = $fchw['dbr']->query($sql);                                                                                                                     
    $cnt = 1;
    foreach($linksupdate->mLinks as $Key2=>$Value2) {
	foreach($Value2 as $Key=>$Value) {
	    fchw_SaveLink($linksupdate, $Key);
	$cnt++;
	}
    }
    $fchw['ParseLinks'] = 1;
    return true;
}

// Delete links when deleting page
function fchw_DeleteLinks(&$article, &$user, &$reason) {
    global $fchw;
    $fchw['dbr'] = &wfGetDB(DB_SLAVE);
    $fchw['table_relation'] = $fchw['dbr']->tableName('fchw_relation');
    $sql = "delete from ".$fchw['table_relation']." where from_id = '".$article->getID()."'";
    $res = $fchw['dbr']->query($sql);                                                                                                                     
    return true;
}

// Move links when moving page
function fchw_MoveLinks(&$title, &$newtitle, &$user, $oldid, $newid) {
    global $fchw;
    $fchw['dbr'] = &wfGetDB(DB_SLAVE);
    $fchw['table_relation'] = $fchw['dbr']->tableName('fchw_relation');
    $fchw['Pages'] = fchw_GetPages();
    $fchw['dbr']->update($fchw['table_relation'], 
       array('from_title' => $newtitle), 
       array('from_title' => $title), 
       'fchw_MoveLinks');
    $fchw['dbr']->update($fchw['table_relation'], 
       array('to_title' => $newtitle), 
       array('to_title' => $title), 
       'fchw_MoveLinks');
//die ("$title, $newtitle, $oldid, $newid");
    return true;
}

// Undelete links when un-deleting page
function fchw_UndeleteLinks($title, $create) {
    global $fchw;
    $fchw['dbr'] = &wfGetDB(DB_SLAVE);
    $fchw['table_relation'] = $fchw['dbr']->tableName('fchw_relation');
    $fchw['Pages'] = fchw_GetPages();
    $fchw['dbr']->update($fchw['table_relation'], 
       array('to_id' => $fchw['Pages']["$title"]), 
       array('to_title' => $title), 
       'fchw_UndeleteLinks');
    return true;
}

// Save link - save relation to database
function fchw_SaveLink($linksupdate, $Link) {
    global $fchw;
    if (!isset($fchw['RedirectedPages'])) 
	$fchw['RedirectedPages'] = fchw_GetRedirectedPages();
    if (strpos($Link, "::") > 0) {
	$Relation = substr($Link, 0, strpos($Link, "::"));
	$To_title = substr($Link, strpos($Link, "::")+2);
	$To_id = 0;
	$From_title = $linksupdate->mTitle->mPrefixedText;
	if (strrpos($From_title, ":") > 0) 
	    $From_title = substr($From_title, strpos($From_title, ":")+1);
	if (strrpos($To_title, ":") > 0) 
	    $To_title = substr($To_title, strpos($To_title, ":")+1);
	if (isset($fchw['Pages'][$To_title]))
	    $To_id = $fchw['Pages'][$To_title];
	// REDIRECTED pages
	if (isset($fchw['RedirectedPages'][$To_id])) {
	    $To_title = $fchw['RedirectedPages'][$To_id]['to_title'];
	    $To_id = $fchw['RedirectedPages'][$To_id]['to_id'];
	}                                                                                                                 
	//
	$fchw['dbr']->insert($fchw['table_relation'], 
	   array(
		'from_id' => $linksupdate->mId,
		'from_title' => $From_title, 
		'to_id' => $To_id, 
		'to_title' => $To_title,
		'relation' => $Relation 		
	   ), 'fchw_SaveLink');
    }
    return true;
}


