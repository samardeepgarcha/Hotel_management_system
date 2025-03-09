<?php
include 'db_connect.php'; // Your DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    // Check if the room is available
    $stmt = $conn->prepare("SELECT status FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    if (!$room) {
        echo "Room not found!";
    } elseif ($room['status'] === 'booked') {
        echo "Room is already booked!";
    } else {
        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (id, room_id, check_in, check_out, status) VALUES (?, ?, ?, ?, 'confirmed')");
        $stmt->bind_param("iiss", $user_id, $room_id, $check_in, $check_out);

        if ($stmt->execute()) {
            // Update room status to 'booked'
            $update_stmt = $conn->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?");
            $update_stmt->bind_param("i", $room_id);
            $update_stmt->execute();

            echo "Room booked successfully!";
        } else {
            echo "Booking failed: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
