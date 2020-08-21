<?php

require_once __DIR__ . '/mysql.php';
$mysql = new PDOWrapper();

$start_date = '2018-07-01';
$end_date = date('Y-m-d');

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = date('Y-m-d', strtotime($_GET['start_date']));
    $end_date = date('Y-m-d', strtotime($_GET['end_date']));
}

//state
$state = 'all';
$available_states = ['all', 'open', 'closed'];
if (isset($_GET['state']) && in_array($_GET['state'], $available_states)) {
    $state = $_GET['state'];
}

//get types of labels
$sql = 'SELECT id, name FROM type ORDER BY name ASC';
$types = $mysql->query($sql);

//get labels
$sql = 'SELECT l.id label_id, l.name label_name, l.description label_description, t.id type_id, t.name type_name
FROM label l 
INNER JOIN type t ON t.id = l.type_id
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
$data = [
    'start_date' => $start_date,
    'end_date' => $end_date,
];
$sql = 'SELECT COUNT(i.id) nbr, l.id, l.name
FROM issue i
INNER JOIN issue_label il ON i.id = il.issue_id
INNER JOIN label l ON il.label_id = l.id
WHERE i.created BETWEEN :start_date AND :end_date';
if($state != 'all') {
    $sql .= '
    AND i.state = :state
    ';
    $data['state'] = $state;
}
$sql .= '
AND l.id IN ('.implode(',', $selected_labels).')
GROUP BY l.id, l.name
ORDER BY nbr DESC;';
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

        #label-toggle {
            cursor: pointer;
        }

        .type_icon {
            cursor: pointer;
            margin: 0 3px;
        }

        span.badge, span.badge>* {
            cursor: pointer;
        }

        input.type_checkbox {
            display: none;
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
            <div class="col">
                <select class="form-control" name="state" id="state">
                    <?php
                    foreach($available_states as $available_state) {
                        $s = '';
                        if ($available_state == $state) {
                            $s = 'selected';
                        }
                        echo '<option value="'.$available_state.'" '.$s.'>'.$available_state.'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <small id="label-toggle">Toggle labels</small>
        </div>
        <div class="container-labels" style="display:none;">
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
                    <h5><?php echo $type_labels[0]['type_name']; ?>
                        <span class="type_icon type_icon_check_all" title="Check all labels for this type" data-type="<?php echo $type_id; ?>">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </span>
                        <span class="type_icon type_icon_uncheck_all" title="Uncheck all labels for this type" data-type="<?php echo $type_id; ?>">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                              <path fill-rule="evenodd" d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z"/>
                            </svg>
                        </span>
                    </h5>
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
                                    <input type="checkbox" id="label_'.$label['id'].'" name="label['.$label['id'].']" '.$c.' class="type_checkbox type_'.$type_id.'"/>
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
<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
    $("#label-toggle").click(function() {
    $(".container-labels").slideToggle('fast');
    });
    $(".type_icon_check_all").click(function() {
      const type_id = $(this).data('type');
      $(".type_"+type_id).prop("checked", true);
    });
    $(".type_icon_uncheck_all").click(function() {
      const type_id = $(this).data('type');
      $(".type_"+type_id).prop("checked", false);
    });
    $('.type_checkbox').change(function() {
      if(this.checked) {
        $(this).parents('span.badge').removeClass('badge-light').addClass('badge-dark');
      } else {
        $(this).parents('span.badge').removeClass('badge-dark').addClass('badge-light');
      }
    });


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
          'rgb(129, 157, 199)',
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
