<?php
/*
| CSV download of the report built on reports.php
| (same options: reports.php?type=teams&period=3m...  ->  download_report.php?type=teams&period=3m...)
|
| The CSV is one clean table: the column names on the first row, then one row
| per record. This opens correctly in Excel / Google Sheets and can be imported
| into other tools. (A CSV can't keep column widths or colours – for a formatted
| report use "Download Excel", download_excel.php.)
*/
include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/download_report.php";