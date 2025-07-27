<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Dev, Manager, Senior

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_received_id = "LR" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO letters_received 
        (letter_received_id, client_id, branch_id, letter_type_id, received_date, scanned_copy_required,
         email_to_client_required, filed, sic_id, sic_signature, mic_id, mic_signature, follow_up_required, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssiisssssis",
        $letter_received_id,
        $_POST['client_id'],
        $_POST['branch_id'],
        $_POST['letter_type_id'],
        $_POST['received_date'],
        $_POST['scanned_copy_required'],
        $_POST['email_to_client_required'],
        $_POST['filed'],
        $_POST['sic_id'],
        $_POST['sic_signature'],
        $_POST['mic_id'],
        $_POST['mic_signature'],
        $_POST['follow_up_required'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        echo "Letter received successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h3>IRB Letter Received Form</h3>
<form method="post">
  <input name="client_id" placeholder="Client ID" required><br>
  <input name="branch_id" placeholder="IRB Branch ID" required><br>
  <input name="letter_type_id" placeholder="Letter Type ID" required><br>
  <label>Date Received: <input type="date" name="received_date" required></label><br>
  <label>Scanned Copy Required: <input type="checkbox" name="scanned_copy_required" value="1"></label><br>
  <label>Email to Client Required: <input type="checkbox" name="email_to_client_required" value="1"></label><br>
  <label>Filed: <input type="checkbox" name="filed" value="1"></label><br>
  <input name="sic_id" placeholder="SIC ID" required><br>
  <input name="sic_signature" placeholder="SIC Signature"><br>
  <input name="mic_id" placeholder="MIC ID"><br>
  <input name="mic_signature" placeholder="MIC Signature"><br>
  <label>Follow-up Required: <input type="checkbox" name="follow_up_required" value="1"></label><br>
  <textarea name="remark" placeholder="Remarks"></textarea><br>
  <button type="submit">Submit</button>
</form>
