<?php

//////////////////////////////////////////////////////////////
//
//    Copyright (C) Thomas Kock, Delmenhorst, 2009
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

require_once ( getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
    : dirname( __FILE__ ) . '/../../../maintenance/commandLine.inc' );
require_once("$IP/maintenance/counter.php");
require_once("./extensions/flowchartwiki/lib.php");
require_once("./extensions/flowchartwiki/linktypes.php");

global $wgParser, $wgServer;

if ($argc != 2) {
    print("Bad syntax: Use Category.php CategoryName\n");
    die();
} else $Category = $argv[0];
global $wgContLang;
$name = "$Category";//$title->getDBkey();
$dbr = wfGetDB( DB_SLAVE );
list( $page, $categorylinks ) = $dbr->tableNamesN( 'page', 'categorylinks' );
$sql = "SELECT page_namespace, page_title FROM $page " .
    "JOIN $categorylinks ON cl_from = page_id " .
    "WHERE cl_to = " . $dbr->addQuotes( $name );
$pages = array();
print("Category:$Category\n");
$res = $dbr->query( $sql, 'wfExportGetPagesFromCategory' );
while ( $row = $dbr->fetchObject( $res ) ) {
    $n = $row->page_title;
    if ($row->page_namespace) {
	$ns = $wgContLang->getNsText( $row->page_namespace );
	$n = $ns . ':' . $n;
    }
print("$n\n");
}
$dbr->freeResult($res);

