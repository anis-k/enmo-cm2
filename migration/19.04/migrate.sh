#!/bin/sh
php ./migrateSendmail.php
php ./migrateExport.php
php ./migrateFeature.php
php ./migrateNewNature.php
php ./refactorPriorities.php
