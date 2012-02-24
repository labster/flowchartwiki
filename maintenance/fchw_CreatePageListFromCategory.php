<?php
require_once ( getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
    : dirname( __FILE__ ) . '/../../../maintenance/commandLine.inc' );
require_once("$IP/maintenance/counter.php");
require_once("./extensions/fchw/lib.php");
require_once("./extensions/fchw/linktypes.php");

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

