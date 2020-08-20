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
$sql = 'SELECT l.id label_id, l.name label_name, l.description label_description, t.id type_id, t.name type_name
FROM label l 
INNER JOIN type t ON t.id = l.type_id
ORDER BY l.name ASC';
$labels = $mysql->query($sql);

$selected_labels = [];

if (isset($_GET['label']) && $_GET['label'] != '') {
    $get_labels = $_GET['label'];
    foreach($get_labels as $k => $v) {
        $selected_labels[] = $k;
    }
}

$colors = [
    'rgb(55, 55, 150)',
    'rgb(50, 132, 184)',
    'rgb(41, 158, 72)',
    'rgb(158, 76, 41)',
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
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" placeholder="Start Date">
            </div>
            <div class="col">
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" placeholder="End Date">
            </div>
        </div>
        <div class="form-row">
            <div class="form-check">
                Badges:
                <?php
                    foreach($labels as $label) {
                        $c = '';
                        $class_badge = 'badge_light';
                        if (in_array($label['id'], $selected_labels)) {
                            $c = 'checked';
                            $class_badge = 'badge_dark';
                        }
                        echo '
                        <span class="badge badge-pill '.$class_badge.'">
                            <label for="label_'.$label['id'].'">
                                <input type="checkbox" id="label_'.$label['id'].'" name="label['.$label['id'].']" '.$c.' />
                                <span title="'.$label['description'].'">'.$label['name'].'</span>
                            </label>
                        </span>
                        ';
                    }
                ?>
            </div>
        </div>
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

</script>
</body>
</html>
