<?php
require '../vendor/autoload.php';
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2]); // Developer and Manager

use Mpdf\Mpdf;

$mpdf = new Mpdf();

$html = '<h2>IRB Letters - Received</h2>';
$html .= '<table border="1" cellpadding="5"><tr><th>ID</th><th>Client</th><th>Branch</th><th>Type</th><th>Date</th><th>Status</th></tr>';

$result = $conn->query("SELECT * FROM letters_received");

while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>{$row['letter_received_id']}</td>
        <td>{$row['client_id']}</td>
        <td>{$row['branch_id']}</td>
        <td>{$row['letter_type_id']}</td>
        <td>{$row['received_date']}</td>
        <td>{$row['status']}</td>
    </tr>";
}

$html .= '</table>';

$mpdf->WriteHTML($html);
$mpdf->Output('Letters_Received_Report.pdf', \Mpdf\Output\Destination::INLINE);
