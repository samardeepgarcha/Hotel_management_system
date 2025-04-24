<?php
include 'db_connect.php';

$conn->begin_transaction();  // 🔒 Start transaction

try {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    // Step 1: Lock room and get its price
    $check = $conn->prepare("SELECT status, price FROM rooms WHERE id = ? FOR UPDATE");
    $check->bind_param("i", $room_id);
    $check->execute();
    $result = $check->get_result();
    $room = $result->fetch_assoc();

    if (!$room) {
        throw new Exception("Room not found.");
    }

    if ($room['status'] !== 'available') {
        throw new Exception("Room is not available.");
    }

    $price_per_night = $room['price'];

    // Step 2: Calculate number of nights
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date2->diff($date1)->days;

    if ($nights <= 0) {
        throw new Exception("Check-out must be after check-in.");
    }

    $total_price = $nights * $price_per_night;

    // Step 3: Insert booking
    $booking = $conn->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, status, total_price, created_at) VALUES (?, ?, ?, ?, 'confirmed', ?, NOW())");
    $booking->bind_param("iissd", $user_id, $room_id, $check_in, $check_out, $total_price);
    $booking->execute();
    $booking_id = $conn->insert_id;

    // Step 4: Update room status
    $update = $conn->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?");
    $update->bind_param("i", $room_id);
    $update->execute();

    // Step 5: Insert invoice
    $invoice = $conn->prepare("INSERT INTO invoices (booking_id, amount, payment_date) VALUES (?, ?, NOW())");
    $invoice->bind_param("id", $booking_id, $total_price);
    $invoice->execute();

    $conn->commit();
    echo "✅ Booking successful! Total price: ₹" . number_format($total_price, 2);

} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Transaction failed: " . $e->getMessage();
}

$conn->close();
?>