<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch_id = "1000" . rand(10,99);
    $stmt = $conn->prepare("INSERT INTO irb_branches (branch_id, name, phone, email, street, pcode, city, state)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss",
        $branch_id, $_POST['name'], $_POST['phone'], $_POST['email'],
        $_POST['street'], $_POST['pcode'], $_POST['city'], $_POST['state']);
    
    if ($stmt->execute()) {
        echo "IRB Branch added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="post">
  <h3>Add IRB Branch</h3>
  <input name="name" placeholder="Branch Name" required><br>
  <input name="phone" placeholder="Phone"><br>
  <input name="email" placeholder="Email" required><br>
  <input name="street" placeholder="Street"><br>
  <input name="pcode" placeholder="Postal Code"><br>
  <input name="city" placeholder="City"><br>
  <input name="state" placeholder="State"><br>
  <button type="submit">Add Branch</button>
</form>
