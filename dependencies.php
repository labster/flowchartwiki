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


$wgHooks['ParserFirstCallInit'][] = 'FlowChartWikiDependencies::onParserInit';

class FlowChartWikiDependencies {

    public static function onParserInit (Parser $parser) {
        $parser->setHook( "Dependencies", "FlowChartWikiDependencies::renderDependencies" );
    }

    function DeLink($input) {
        global $wgUser;
        $skin = $wgUser->getSkin();
        return "<a href=\"".$skin->makeUrl($input)."\">$input</a>";
    }

    function renderDepType($titleText) {
        $dbr = wfGetDB( DB_SLAVE );
        $res = $dbr->select(
            'fchw_relation',
            [ 'from_title', 'relation', 'to_title' ],
            [ $dbr->buildLike('from_title', $titleText ),
              'relation' => 'Type' ],
            __FUNCTION__,
            "LIMIT 500"
        );

        $output = "";
        foreach ( $res as $row ) {
            $output .= $this->DeLink($row->to_title)."<br />";
        }
        return $output;
    }

    function renderWhereDoIlink($titleText) {
        $dbr = wfGetDB( DB_SLAVE );
        $res = $dbr->select(
            'fchw_relation',
            [ 'from_title', 'relation', 'to_title' ],
            [ $dbr->buildLike('from_title', $titleText ),
              "relation NOT IN ('Type', 'Level', 'PageName')" ],
            __FUNCTION__,
            "LIMIT 500"
        );

        $output = "";
        foreach ( $res as $row ) {
            $output .= " &nbsp; ".$this->DeLink($row->to_title)." (".$this->DeLink($row->relation).")<br />";
        }
        return $output;
    }

    function renderWhoLinksHere($titleText) {
        $dbr = wfGetDB( DB_SLAVE );
        $output = "";

        $res = $dbr->select(
            'fchw_relation',
            [ 'from_title', 'relation', 'to_title' ],
            [ $dbr->buildLike('to_title', $titleText ),
              "relation NOT IN ('Type', 'Level', 'PageName')" ],
            __FUNCTION__,
            "LIMIT 500"
        );
        foreach ( $res as $row ) {
            $output .= " &nbsp; ".$this->DeLink($row->from_title)." (".$this->DeLink($row->relation).")<br />";
        }

        // links for types
        $res = $dbr->select(
            'fchw_relation',
            [ 'from_title', 'relation', 'to_title' ],
            [ $dbr->buildLike('to_title', $titleText ),
              'relation' => 'Type' ],
            __FUNCTION__,
            "LIMIT 500"
        );
        foreach ( $res as $row ) {
            $output .= " &nbsp; ".$this->DeLink($row->from_title)."<br />";
        }
        return $output;
    }


    public static function renderDependencies ($input, array $args, Parser $parser, PPFrame $frame) {
        global $wgArticle, $wgRequest;
        $titleText = $parser->getTitle()->mTextform;
        $output = "";
        $output .= "<p><table width='80%' cellpadding='0' cellspacing='0' border='0' class='dependencies'>";
        //$output .= "<tr><td colspan='2' style='padding: 2pt; border: 1px solid black;'><strong>".wfMessage('fchw_TypeOfPage')->text()." '".$wgParser->getTitle()->mPrefixedText."':</strong> ".renderDepType()."</td></tr>";
        $output .= "<tr><td colspan='2' style='padding: 2pt; border: 1px solid black;'><strong>".wfMessage('fchw_TypeOfPage')->text()." '".$wgParser->getTitle()->getPrefixedText()."':</strong> ".$this->renderDepType($titleText)."</td></tr>";
        $output .= "<tr><td valign='top' style='padding: 2pt; border: 1px solid black;'><strong>".wfMessage('fchw_WhereDoILinkTo')->text()."</strong><br />".$this->renderWhereDoIlink($titleText)."</td>";
        $output .= "<td valign='top' style='padding: 2pt; border: 1px solid black;'><strong>".wfMessage('fchw_WhoLinksHere')->text()."</strong><br />".$this->renderWhoLinksHere($titleText)."</td></tr>";
        $output .= "</table>";
        return $output;
    }

}