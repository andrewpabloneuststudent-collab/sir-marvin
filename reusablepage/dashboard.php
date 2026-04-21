<?php
require_once __DIR__ . "/../conn/connection_links.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Welcome to the dashboard!</p>

<div style="width: 100%; height: 400px;">
    <canvas id="myChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('myChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        datasets: [{
            label: 'Sales',
            data: [10, 20, 15, 30],
            borderColor: 'blue',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false // 👈 stretch enabled
    }
});
</script>
</body>
</html>