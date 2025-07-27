<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior only

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = "40000" . rand(100,999); // auto-ID style
    $stmt = $conn->prepare("INSERT INTO clients (client_id, company_name, pic_name, company_phone, pic_phone, pic_email, street, pcode, city, state, sic_id, mic_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss",
        $client_id, $_POST['company_name'], $_POST['pic_name'], $_POST['company_phone'],
        $_POST['pic_phone'], $_POST['pic_email'], $_POST['street'], $_POST['pcode'],
        $_POST['city'], $_POST['state'], $_POST['sic_id'], $_POST['mic_id']);
    
    if ($stmt->execute()) {
        echo "Client added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="post">
  <h3>Register Client</h3>
  <input name="company_name" placeholder="Company Name" required><br>
  <input name="pic_name" placeholder="PIC Name" required><br>
  <input name="company_phone" placeholder="Company Phone"><br>
  <input name="pic_phone" placeholder="PIC Phone"><br>
  <input name="pic_email" placeholder="PIC Email" required><br>
  <input name="street" placeholder="Street"><br>
  <input name="pcode" placeholder="Postal Code"><br>
  <input name="city" placeholder="City"><br>
  <input name="state" placeholder="State"><br>
  <input name="sic_id" placeholder="SIC ID (e.g., 20001)" required><br>
  <input name="mic_id" placeholder="MIC ID (e.g., 10002)" required><br>
  <button type="submit">Add Client</button>
</form>
