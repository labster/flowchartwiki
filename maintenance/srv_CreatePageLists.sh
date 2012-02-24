#!/bin/sh

../../../../../php5/bin/php ./extensions/flowchartwiki/maintenance/fchw_CreatePageListFromCategory.php ProcessList > ./extensions/flowchartwiki/maintenance/pagelist_ProcessList.txt
../../../../../php5/bin/php ./extensions/flowchartwiki/maintenance/fchw_CreatePageListFromCategory.php Flightbooking > ./extensions/flowchartwiki/maintenance/pagelist_Flightbooking.txt
../../../../../php5/bin/php ./extensions/flowchartwiki/maintenance/fchw_CreatePageListFromCategory.php GettingStarted > ./extensions/flowchartwiki/maintenance/pagelist_GettingStarted.txt
../../../../../php5/bin/php ./extensions/flowchartwiki/maintenance/fchw_CreatePageListFromCategory.php ValueStream > ./extensions/flowchartwiki/maintenance/pagelist_ValueStream.txt
../../../../../php5/bin/php ./extensions/flowchartwiki/maintenance/fchw_CreatePageListFromCategory.php ShapeTest > ./extensions/flowchartwiki/maintenance/pagelist_ShapeTest.txt
