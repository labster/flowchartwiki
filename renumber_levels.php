<?php
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
  wfLoadExtensionMessages('fchwrenumlevels');
  $text = wfMsg('fchwrenumlevels');
 
  # Convert from title in text form to DBKey and put it into the alias array:
  $title = Title::newFromText($text); 
  return true;
}


