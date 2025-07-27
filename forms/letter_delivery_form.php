<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 4]); // Developer or Admin Staff

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_id = "LD" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_delivered 
        (delivery_id, letter_sent_id, collection_date, delivered_date, delivery_method,
         tracking_number, ad_staff_id, ad_signature, status, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $delivery_id,
        $_POST['letter_sent_id'],
        $_POST['collection_date'],
        $_POST['delivered_date'],
        $_POST['delivery_method'],
        $_POST['tracking_number'],
        $_POST['ad_staff_id'],
        $_POST['ad_signature'],
        $_POST['status'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        echo "Letter delivery recorded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h3>Letter Delivery Form (Admin Staff)</h3>
<form method="post">
  <input name="letter_sent_id" placeholder="Letter Sent ID" required><br>
  <label>Collection Date: <input type="date" name="collection_date" required></label><br>
  <label>Delivered Date: <input type="date" name="delivered_date" required></label><br>
  
  <select name="delivery_method" required>
    <option value="Courier">Courier</option>
    <option value="Dispatch">Dispatch</option>
  </select><br>

  <input name="tracking_number" placeholder="Tracking Number (optional)"><br>
  <input name="ad_staff_id" placeholder="Admin Staff ID" required><br>
  <input name="ad_signature" placeholder="Admin Signature" required><br>

  <select name="status">
    <option value="Pending">Pending</option>
    <option value="Completed">Completed</option>
  </select><br>

  <textarea name="remark" placeholder="Remarks (optional)"></textarea><br>

  <button type="submit">Submit Delivery</button>
</form>
