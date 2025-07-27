<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $followup_sent_id = "SF" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_sent_followup
        (followup_sent_id, letter_sent_id, phone_call_irb, call_date, irb_reply,
         ack_required, ack_received, followup_status, change_of_sic, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $followup_sent_id,
        $_POST['letter_sent_id'],
        $_POST['phone_call_irb'],
        $_POST['call_date'],
        $_POST['irb_reply'],
        $_POST['ack_required'],
        $_POST['ack_received'],
        $_POST['followup_status'],
        $_POST['change_of_sic'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        echo "Follow-up recorded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h3>Letter Sent Follow-up Form</h3>
<form method="post">
  <input name="letter_sent_id" placeholder="Letter Sent ID" required><br>

  <select name="phone_call_irb">
    <option value="Yes">Yes</option>
    <option value="N/A">N/A</option>
  </select><br>

  <label>Call Date: <input type="date" name="call_date"></label><br>

  <select name="irb_reply">
    <option value="Success">Success</option>
    <option value="Fail">Fail</option>
    <option value="Pending">Pending</option>
    <option value="N/A">N/A</option>
  </select><br>

  <select name="ack_required">
    <option value="Yes">Yes</option>
    <option value="N/A">N/A</option>
  </select><br>

  <select name="ack_received">
    <option value="Yes">Yes</option>
    <option value="No">No</option>
    <option value="Pending">Pending</option>
    <option value="N/A">N/A</option>
  </select><br>

  <select name="followup_status">
    <option value="Pending">Pending</option>
    <option value="Completed">Completed</option>
  </select><br>

  <select name="change_of_sic">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
  </select><br>

  <textarea name="remark" placeholder="Remarks"></textarea><br>
  <button type="submit">Submit Follow-up</button>
</form>
