<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $followup_id = "RF" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_received_followup
        (followup_id, letter_received_id, phone_call_client, email_to_client, email_date,
         client_reply, client_reply_date, followup_status, change_of_sic, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $followup_id,
        $_POST['letter_received_id'],
        $_POST['phone_call_client'],
        $_POST['email_to_client'],
        $_POST['email_date'],
        $_POST['client_reply'],
        $_POST['client_reply_date'],
        $_POST['followup_status'],
        $_POST['change_of_sic'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        echo "Follow-up recorded!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h3>Letter Received Follow-up Form</h3>
<form method="post">
  <input name="letter_received_id" placeholder="Letter Received ID" required><br>
  <select name="phone_call_client">
    <option value="Yes, called">Yes, called</option>
    <option value="Pending">Pending</option>
    <option value="N/A">N/A</option>
  </select><br>

  <select name="email_to_client">
    <option value="Yes, emailed">Yes, emailed</option>
    <option value="Pending">Pending</option>
    <option value="N/A">N/A</option>
  </select><br>

  <label>Email Date: <input type="date" name="email_date"></label><br>

  <select name="client_reply">
    <option value="Action needed">Action needed</option>
    <option value="No action">No action</option>
    <option value="Pending">Pending</option>
  </select><br>

  <label>Client Reply Date: <input type="date" name="client_reply_date"></label><br>

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
