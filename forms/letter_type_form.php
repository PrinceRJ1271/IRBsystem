<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2]); // Developer & Manager

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_id = ($_POST['letter_type'] == 'Received') ? "2000" . rand(10,99) : "2100" . rand(10,99);
    $stmt = $conn->prepare("INSERT INTO letter_types (letter_id, description, letter_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $letter_id, $_POST['description'], $_POST['letter_type']);

    if ($stmt->execute()) {
        echo "Letter type added!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="post">
  <h3>Add Letter Type</h3>
  <input name="description" placeholder="Description" required><br>
  <select name="letter_type" required>
    <option value="Received">Received</option>
    <option value="Sent">Sent</option>
  </select><br>
  <button type="submit">Add Letter Type</button>
</form>
