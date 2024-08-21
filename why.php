// Fetch data for multi-line chart
$linecolumns = ['seal', 'pressure', 'hose', 'body'];
$chartDataLine = [];

foreach ($linecolumns as $linecolumn) {
    $sqlColumn = "SELECT 
                    SUM(CASE WHEN $linecolumn = 'yes' THEN 1 ELSE 0 END) AS yes_count,
                    SUM(CASE WHEN $linecolumn = 'no' THEN 1 ELSE 0 END) AS no_count
                  FROM evaluations
                  WHERE 1=1 $queryCondition";
    $resultColumnLine = $conn->query($sqlColumn);
    if (!$resultColumnLine) {
        die("Query failed: " . $conn->error);
    }
    $rowColumnLine = $resultColumnLine->fetch_assoc();
    $chartDataLine[$linecolumn] = [
        'yes' => $rowColumnLine['yes_count'],
        'no' => $rowColumnLine['no_count']
    ];
}

$chartDataLineJson = json_encode($chartDataLine);
-------------------------------
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your existing head content -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Your existing body content -->

    <!-- Multi-Line Chart -->
    <canvas id="myLineChart"></canvas>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDataLine = <?php echo $chartDataLineJson; ?>;
            var labels = Object.keys(chartDataLine);

            var yesData = labels.map(function(label) { return chartDataLine[label].yes; });
            var noData = labels.map(function(label) { return chartDataLine[label].no; });

            var ctx = document.getElementById('myLineChart').getContext('2d');
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Yes',
                        data: yesData,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        fill: false
                    }, {
                        label: 'No',
                        data: noData,
                        borderColor: '#FF5733',
                        backgroundColor: 'rgba(255, 87, 51, 0.2)',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Column'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Count'
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    var value = context.raw || 0;
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
