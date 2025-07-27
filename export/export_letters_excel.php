<?php
require '../vendor/autoload.php';
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2]); // Developer and Manager

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Letters Received');

$sheet->fromArray(['ID', 'Client ID', 'Branch ID', 'Letter Type', 'Date', 'Status'], NULL, 'A1');

$result = $conn->query("SELECT * FROM letters_received");

$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $row['letter_received_id'],
        $row['client_id'],
        $row['branch_id'],
        $row['letter_type_id'],
        $row['received_date'],
        $row['status']
    ], NULL, 'A' . $rowNum++);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Letters_Received_Report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
