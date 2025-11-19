<?php
require(__DIR__ . "/../connection/connection.php");

$users = [
    ['guru', 'Teto', null, password_hash('TeGoat123', PASSWORD_BCRYPT)],
    ['guru', 'Yoga', null, password_hash('Banzaiii', PASSWORD_BCRYPT)],
    ['guru', 'Azizir', null, password_hash('BestTeacher', PASSWORD_BCRYPT)],

    ['murid', null, 'raga@gmail.com', password_hash('SangBintang', PASSWORD_BCRYPT)],
    ['murid', null, 'fatur@gmail.com', password_hash('Annis', PASSWORD_BCRYPT)],
    ['murid', null, 'tante@gmail.com', password_hash('TetoHama', PASSWORD_BCRYPT)],


    ['admin', 'Iqbal', 'iqbal@gmail.com', password_hash('Iqbolll', PASSWORD_BCRYPT)],
    ['admin', 'Kenzi', 'kenzi@gmail.com', password_hash('LoveRuby', PASSWORD_BCRYPT)],
];

$stmt = $conn->prepare("INSERT INTO users (role, username, email, password) VALUES (?, ?, ?, ?)");
if(!$stmt) {
    die("Prepare failed: " . $conn->error);
}
foreach ($users as $user) {
    $stmt->bind_param("ssss", $user[0], $user[1], $user[2], $user[3]);
    if ($stmt->execute()) {
        echo "Inserted: {$user[0]} → " . ($user[1] ?? $user[2]) . "<br>";
    } else {
        echo "Error inserting {$user[0]}: " . $stmt->error . "<br>";
    }
}
$stmt->close();
$conn->close();

echo "Seeding completed successfully!";
?>