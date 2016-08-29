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

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
    echo <<<EOT
Extension is not installed
EOT;
    exit( 1 );
}

$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['FchwRenumLevels'] = $dir . 'renumber_levels_body.php'; # Tell MediaWiki to load the extension body.
$wgExtensionMessagesFiles['fchwrenumlevels'] = $dir . 'renumber_levels.i18n.php';
$wgSpecialPages['FchwRenumLevels'] = 'FchwRenumLevels'; # Let MediaWiki know about your new special page.
$wgHooks['LanguageGetSpecialPageAliases'][] = 'fchwrenumLocalizedPageName'; # Add any aliases for the special page.

function fchwrenumLocalizedPageName(&$specialPageArray, $code) {
    # The localized title of the special page is among the messages of the extension:
    // wfLoadExtensionMessages('fchwrenumlevels'); // removed in MW-1.21.1
    $text = wfMessage('fchwrenumlevels')->text();

    # Convert from title in text form to DBKey and put it into the alias array:
    $title = Title::newFromText($text);
    return true;
}
