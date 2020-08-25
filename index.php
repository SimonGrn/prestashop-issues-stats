<?php

require_once __DIR__ . '/mysql.php';
$mysql = new PDOWrapper();

$start_date = '2018-07-01';
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
$sql = 'SELECT COUNT(i.id) nbr, l.id, i.state, l.name
FROM issue i
INNER JOIN issue_label il ON i.id = il.issue_id
INNER JOIN label l ON il.label_id = l.id
WHERE i.created BETWEEN :start_date AND :end_date
AND l.id IN ('.implode(',', $selected_labels).')
GROUP BY l.id, i.state, l.name
ORDER BY nbr DESC;';
$data_results = $mysql->query($sql, $data);
//put the data in the correct fields

$js_data = [];
$total = 0;

foreach($data_results as $line) {
    $js_data[$line['name']][$line['state']] = [
            'value' => $line['nbr'],
    ];
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

        a.nav-link {
            font-size: 0.7em;
        }

        h4 {
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .type_selected {
            font-weight: bold !important;
            text-decoration: underline !important;
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
                <h4>Issues creation dates interval</h4>
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
                <button type="submit" class="btn btn-primary">Display data</button>
            </div>
        </div>
        <div class="container-labels">
            <div class="form-row">
                <div class="col">
                    <h4>Types of labels</h4>
                </div>
            </div>
            <div class="form-row">
                <div class="col">
                    <ul class="nav nav-tabs" id="navTab" role="tablist">
                        <?php
                        $active = 'active';
                        $aria_active = 'true';
                            foreach($labels as $type_id => $type_labels) {
                                echo '
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link '.$active.'" id="label-'.$type_id.'-tab" data-toggle="tab" href="#tab_label_'.$type_id.'" role="tab" aria-controls="label_'.$type_id.'" aria-selected="'.$aria_active.'">'.$type_labels[0]['type_name'].'</a>
                                </li>
                        ';
                            $active = '';
                            $aria_active = 'false';
                            }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="form-row">
                <div class="col">
                    <div class="tab-content">
            <?php
            $active = 'active';
                foreach($labels as $type_id => $type_labels) {
            ?>
                <div class="tab-pane <?php echo $active; ?>" id="<?php echo 'tab_label_'.$type_id; ?>" role="tabpanel" aria-labelledby="<?php echo $type_id; ?>-tab">
                    <small>Select/Unselect all the labels :
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
                    </small><br />
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
                                    <input type="checkbox" id="label_'.$label['id'].'" name="label['.$label['id'].']" '.$c.' class="type_checkbox type_'.$type_id.'" data-type="'.$type_id.'"/>
                                    <span title="'.$label['description'].'">'.$label['name'].'</span>
                                </label>
                            </span>
                            ';
                        }
                    ?>
                </div>
                    <?php
                    $active = '';
                }
            ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <hr>
    <canvas id="bar_data"></canvas>
    <canvas id="pie_data"></canvas>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
    //check uncheck all
    $(".type_icon_check_all").click(function() {
      const type_id = $(this).data('type');
      $(".type_"+type_id).prop("checked", true).trigger('change');
    });

    $(".type_icon_uncheck_all").click(function() {
      const type_id = $(this).data('type');
      $(".type_"+type_id).prop("checked", false).trigger('change');
    });

    //select unselect a label
    $('.type_checkbox').change(function() {
      if(this.checked) {
        $(this).parents('span.badge').removeClass('badge-light').addClass('badge-dark');
      } else {
        $(this).parents('span.badge').removeClass('badge-dark').addClass('badge-light');
      }
      const type_id = $(this).data('type');
      const type_checkboxes = $('.type_'+type_id);
      let type_checked = false;
      for(var i=0; i < type_checkboxes.length;  i++) {
          let cur = $(type_checkboxes[i]);
          if (cur.prop('checked')) {
              type_checked = true;
          }
      }
      if (type_checked) {
          $("#label-"+type_id+"-tab").addClass('type_selected');
      } else {
          $("#label-"+type_id+"-tab").removeClass('type_selected');
      }
    });

    let config_pie = {
        type: 'pie',
        data: {
            datasets: [{
                data: <?php
                $pie_data = [];
                foreach($js_data as $entry) {
                    $t = 0;
                    foreach($entry as $open_closed) {
                        $t += $open_closed['value'];
                    }
                    $pie_data[] = $t;
                }
                echo json_encode($pie_data);
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
            labels: <?php echo json_encode(array_keys($js_data)); ?>
        },
        options: {
            title: {
                display: true,
                text: 'Number of issues per label'
            },
            responsive: true
        }
    };

    let config_bars = {
        type: 'bar',
        data: {
            datasets: [
                {
                data: <?php
                    $bar_closed_data = [];
                    $t = 0;
                    foreach($js_data as $entry) {
                        $t += $entry['closed']['value'];
                        $bar_closed_data[] = $t;
                    }
                    echo json_encode($bar_closed_data);
                ?>,
                backgroundColor: 'rgb(55, 55, 150)'
                ,
                label: 'Closed Issues'
            },{
                    data: <?php
                    $bar_open_data = [];
                    $t = 0;
                    foreach($js_data as $entry) {
                        if (isset($entry['open'])) {
                            $t += $entry['open']['value'];
                        }
                        $bar_open_data[] = $t;
                    }
                    echo json_encode($bar_open_data);
                    ?>,
                    backgroundColor: 'rgb(129, 157, 199)',
                    label: 'Open Issues'
                }],
            labels: <?php echo json_encode(array_keys($js_data)); ?>
        },
        options: {
            title: {
                display: true,
                text: 'Issues data per state'
            },
            responsive: true,
            scales: {
                xAxes: [{
                    stacked: true,
                }],
                yAxes: [{
                    stacked: true
                }]
            }
        }
    };

    window.onload = function() {
      var ctx_pie = document.getElementById('pie_data').getContext('2d');
      window.myPie = new Chart(ctx_pie, config_pie);
      var ctx_bar = document.getElementById('bar_data').getContext('2d');
      window.myBar = new Chart(ctx_bar, config_bars);
    };
</script>
</body>
</html>
