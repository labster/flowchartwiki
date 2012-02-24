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

/**
 * Description of FchwPage
 *
 * @author thomas
 */
class FchwPage {
    function __construct($pageName = "", $id="", $displayName = "", $level ="", $pageType="") {
        $this->id = $id;  // id in cl_from field
        $this->pageName = $pageName; // name of page in Wiki
        $this->displayName = $displayName; // Override the displayName of the Page in the graph
        $this->level = $level;
        $this->pageType = $pageType;
        $this->links = array();
    }
    function getTranslatedName() {
        if ($this->displayName != "") {
            return $this->displayName;
        } else {
            return $this->pageName;
        }
    }
}
class FchwLink {
    function __construct($fromId="", $linkFrom="", $toId="", $linkTo="", $linkType="") {
        $this->fromId = $fromId;
        $this->linkFrom = $linkFrom;
        $this->toId = $toId;
        $this->linkTo = $linkTo;
        $this->linkType = $linkType;
    }
}
?>
