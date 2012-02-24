#!/bin/sh

../../../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/flowchartwiki/maintenance/pagelist_Customizing.txt > ./extensions/flowchartwiki/maintenance/import_Customizing.xml
../../../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/flowchartwiki/maintenance/pagelist_ProcessList.txt > ./extensions/flowchartwiki/maintenance/import_ProcessList.xml
../../../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/flowchartwiki/maintenance/pagelist_Flightbooking.txt > ./extensions/flowchartwiki/maintenance/import_Flightbooking.xml
../../../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/flowchartwiki/maintenance/pagelist_GettingStarted.txt > ./extensions/flowchartwiki/maintenance/import_GettingStarted.xml
../../../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/flowchartwiki/maintenance/pagelist_ValueStream.txt > ./extensions/flowchartwiki/maintenance/import_ValueStream.xml
../../../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/flowchartwiki/maintenance/pagelist_ShapeTest.txt > ./extensions/flowchartwiki/maintenance/import_ShapeTest.xml

