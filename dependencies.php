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

$wgExtensionFunctions[] = "wfDependeciesExtension";

function wfDependeciesExtension() {
    global $wgParser;
    $wgParser->setHook( "Dependencies", "renderDependencies" );
}

function DeLink($input) {
    global $wgUser;
    $skin = $wgUser->getSkin();
    return "<a href=\"".$skin->makeUrl($input)."\">$input</a>";
}

function renderDepType() {
    global $wgTitle, $wgParser;
    global $wgDBprefix;
    $output = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $relations = $dbr->tableName( 'fchw_relation' );
    $sql = "SELECT from_title, relation, to_title FROM $relations WHERE from_title like '".$dbr->strencode($wgParser->getTitle()->mTextform)."' and relation = 'Type' LIMIT 500";
    $res = $dbr->query( $sql );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
        while( $row = $dbr->fetchObject( $res ) ) {
            $output .= DeLink($row->to_title)."<br />";
        }
    }
    $dbr->freeResult( $res );
    return $output;
}

function renderWhereDoIlink() {
    global $wgTitle, $wgParser;
    global $wgDBprefix;
    $output = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $relations = $dbr->tableName( 'fchw_relation' );
    $sql = "SELECT from_title, relation, to_title FROM $relations WHERE from_title like '".$dbr->strencode($wgParser->getTitle()->mTextform)."' LIMIT 500";
    $res = $dbr->query( $sql );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
        while( $row = $dbr->fetchObject( $res ) ) {
            if ($row->relation == "Type")
            continue;
            if ($row->relation == "Level")
            continue;
            if ($row->relation == "PageName")
            continue;
            $output .= " &nbsp; ".DeLink($row->to_title)." (".DeLink($row->relation).")<br />";
        }
    }
    $dbr->freeResult( $res );
    return $output;
}

function renderWhoLinksHere() {
    global $wgTitle, $wgParser;
    global $wgDBprefix;
    $output = "";
    $dbr =& wfGetDB( DB_SLAVE );
    $relations = $dbr->tableName( 'fchw_relation' );
    $sql = "SELECT from_title, relation, to_title FROM $relations WHERE to_title like '".$dbr->strencode($wgParser->getTitle()->mTextform)."' LIMIT 500";
    $res = $dbr->query( $sql );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
        while( $row = $dbr->fetchObject( $res ) ) {
            if ($row->relation == "Type")
            continue;
            if ($row->relation == "Level")
            continue;
            if ($row->relation == "PageName")
            continue;
            $output .= " &nbsp; ".DeLink($row->from_title)." (".DeLink($row->relation).")<br />";
        }
    }
    $dbr->freeResult( $res );
    // links for types
    $sql = "SELECT from_title, relation, to_title FROM $relations WHERE to_title like '".$dbr->strencode($wgParser->getTitle()->mTextform)."' and relation='Type' LIMIT 500";
    $res = $dbr->query( $sql );
    $count = $dbr->numRows( $res );
    if( $count > 0 ) {
        # Make list
        while( $row = $dbr->fetchObject( $res ) ) {
            $output .= " &nbsp; ".DeLink($row->from_title)."<br />";
        }
    }
    $dbr->freeResult( $res );
    return $output;
}

function renderTable() {
    global $wgArticle, $wgTitle, $wgRequest, $wgParser;
    global $DepTitle;
    $output = "";
    $output .= "<p><table width='80%' cellpadding='0' cellspacing='0' border='0' class='dependencies'>";
    $output .= "<tr><td colspan='2' style='padding: 2pt; border: 1px solid black;'><strong>".wfMsg('fchw_TypeOfPage')." '".$wgParser->getTitle()->mPrefixedText."':</strong> ".renderDepType()."</td></tr>";
    $output .= "<tr><td valign='top' style='padding: 2pt; border: 1px solid black;'><strong>".wfMsg('fchw_WhereDoILinkTo')."</strong><br />".renderWhereDoIlink()."</td>";
    $output .= "<td valign='top' style='padding: 2pt; border: 1px solid black;'><strong>".wfMsg('fchw_WhoLinksHere')."</strong><br />".renderWhoLinksHere()."</td></tr>";
    $output .= "</table>";
    return $output;
}

function renderDependencies( $input )
{
    $output = renderTable();
    return $output;
}
