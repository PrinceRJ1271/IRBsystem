<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_sent_id = "LS" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO letters_sent 
        (letter_sent_id, client_id, branch_id, letter_type_id, sent_date,
         sic_id, sic_signature, mic_id, mic_signature, follow_up_required, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssis",
        $letter_sent_id,
        $_POST['client_id'],
        $_POST['branch_id'],
        $_POST['letter_type_id'],
        $_POST['sent_date'],
        $_POST['sic_id'],
        $_POST['sic_signature'],
        $_POST['mic_id'],
        $_POST['mic_signature'],
        $_POST['follow_up_required'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        echo "Letter sent recorded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h3>IRB Letter Sent Form</h3>
<form method="post">
  <input name="client_id" placeholder="Client ID" required><br>
  <input name="branch_id" placeholder="IRB Branch ID" required><br>
  <input name="letter_type_id" placeholder="Letter Type ID" required><br>
  <label>Sent Date: <input type="date" name="sent_date" required></label><br>
  <input name="sic_id" placeholder="SIC ID" required><br>
  <input name="sic_signature" placeholder="SIC Signature"><br>
  <input name="mic_id" placeholder="MIC ID"><br>
  <input name="mic_signature" placeholder="MIC Signature"><br>
  <label>Follow-up Required: <input type="checkbox" name="follow_up_required" value="1"></label><br>
  <textarea name="remark" placeholder="Remarks"></textarea><br>
  <button type="submit">Submit</button>
</form>
