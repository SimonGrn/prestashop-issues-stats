<?php

require_once __DIR__ . '/mysql.php';
$mysql = new PDOWrapper();

$start_date = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 week" ) );
$end_date = date('Y-m-d');

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = date('Y-m-d', strtotime($_GET['start_date']));
    $end_date = date('Y-m-d', strtotime($_GET['end_date']));
}
//get types of labels
$sql = 'SELECT id, name FROM type ORDER BY name ASC';
$types = $mysql->query($sql);

//get labels
$sql = 'SELECT l.id label_id, l.name label_name, l.description label_description, t.id type_id, COALESCE(t.name, "None") type_name
FROM label l 
LEFT JOIN type t ON t.id = l.type_id
ORDER BY t.id, l.name ASC';
$db_labels = $mysql->query($sql);

$labels = [];
foreach ($db_labels as $db_label) {
    $labels[$db_label['type_id']][] = [
           'id' =>  $db_label['label_id'],
           'name' =>  $db_label['label_name'],
           'description' =>  $db_label['label_description'],
           'type_name' =>  $db_label['type_name'],
    ];
}

$selected_labels = [];

if (isset($_GET['label']) && $_GET['label'] != '') {
    $get_labels = $_GET['label'];
    foreach($get_labels as $k => $v) {
        $selected_labels[] = $k;
    }
}

//get the data
$sql = 'SELECT COUNT(i.id) nbr, l.id, l.name
FROM issue i
INNER JOIN issue_label il ON i.id = il.issue_id
INNER JOIN label l ON il.label_id = l.id
WHERE i.closed BETWEEN :start_date AND :end_date
AND l.id IN ('.implode(',', $selected_labels).')
GROUP BY l.id, l.name;';
$data = [
    'start_date' => $start_date,
    'end_date' => $end_date,
];
$data_results = $mysql->query($sql, $data);
$js_labels = [];
$js_data = [];
$total = 0;
foreach($data_results as $line) {
    $js_labels[] = $line['name'];
    $js_data[] = $line['nbr'];
    $total += $line['nbr'];
}

$colors = [
    'rgb(55, 55, 150)',
    'rgb(50, 132, 184)',
    'rgb(41, 158, 72)',
    'rgb(158, 76, 41)',
    'rgb(158, 152, 41)',
    'rgb(41, 158, 49)',
    'rgb(129, 157, 199)',
];
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <style>
        .container {
            background-color: #EEE;
            margin-top: 20px;
            padding-top: 10px;
            padding-bottom: 10px;
        }
    </style>

    <title>PrestaShop Issues Stats</title>
</head>
<body>
<div class="container">
    <h1>PrestaShop Issues Stats</h1>
    <form>
        <div class="form-row">
            <div class="col">
                <h3>Dates</h3>
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" placeholder="Start Date">
            </div>
            <div class="col">
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" placeholder="End Date">
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <h3>Types of labels</h3>
            </div>
        </div>
        <?php
            foreach($labels as $type_id => $type_labels) {
        ?>
        <div class="form-row">
            <div class="form-check">
                <h5><?php echo $type_labels[0]['type_name']; ?></h5>
                <?php
                    foreach($type_labels as $label) {
                        $c = '';
                        $class_badge = 'badge-light';
                        if (in_array($label['id'], $selected_labels)) {
                            $c = 'checked';
                            $class_badge = 'badge-dark';
                        }
                        echo '
                        <span class="badge '.$class_badge.'">
                            <label for="label_'.$label['id'].'" style="margin-bottom:0;">
                                <input type="checkbox" id="label_'.$label['id'].'" name="label['.$label['id'].']" '.$c.' class="type_checkbox"/>
                                <span title="'.$label['description'].'">'.$label['name'].'</span>
                            </label>
                        </span>
                        ';
                    }
                ?>
            </div>
        </div>
        <?php
            }
        ?>
        <div class="form-row">
            <div class="col">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
    <hr>
    <canvas id="data"></canvas>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
  var config = {
    type: 'pie',
    data: {
      datasets: [{
        data: <?php
          echo json_encode($js_data);
          ?>,
        backgroundColor: [
          'rgb(55, 55, 150)',
          'rgb(50, 132, 184)',
          'rgb(41, 158, 72)',
          'rgb(158, 76, 41)',
          'rgb(158, 152, 41)',
          'rgb(41, 158, 49)',
          'rgb(129, 157, 199)'
        ],
        label: 'Issue Data'
      }],
      labels: <?php
        echo json_encode($js_labels);
        ?>
    },
    options: {
      responsive: true
    }
  };

  window.onload = function() {
    var ctx = document.getElementById('data').getContext('2d');
    window.myPie = new Chart(ctx, config);
  };
</script>
</body>
</html>
