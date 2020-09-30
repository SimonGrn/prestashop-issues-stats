<?php

require_once __DIR__ . '/mysql.php';

if (getenv('SECURITY_TOKEN') != SECURITY_TOKEN) {
    die("Wrong token".PHP_EOL);
}

$mysql = new PDOWrapper();

$start_date = date('Y-m-01');
$end_date = date('Y-m-01', strtotime("+1 month"));
if (getenv('START_DATE')) {
    $start_date = getenv('START_DATE');
}
if (getenv('END_DATE')) {
    $end_date = getenv('END_DATE');
}

$type = 'creation';
if (getenv('TYPE') && in_array(getenv('TYPE'), ['creation', 'close'])) {
    $type = getenv('TYPE');
}

$sql_parameters = " AND ". ($type == 'creation' ? " created BETWEEN " : " closed BETWEEN "). "";
$sql_parameters .= "'".$start_date."' AND '".$end_date."'";

$data = [];

// -------------------------------------
// total
$total = $mysql->query("
    SELECT COUNT(*) as total
    FROM issue i 
    WHERE 1=1
    $sql_parameters;
");
$data['total'] = $total['total'];

// -------------------------------------
// Regressions
$regressions = $mysql->query("
    SELECT COUNT(*) as regressions
    FROM issue i 
    INNER JOIN issue_label il ON i.id = il.issue_id
    INNER JOIN label l ON l.id = il.label_id
    WHERE 1=1
    $sql_parameters
    AND l.name = 'Regression';
");
$data['regressions']['value'] = $regressions['regressions'];
$data['regressions']['percent'] = round($regressions['regressions'] / $data['total'] * 100, 2);

// -------------------------------------
// Duplicates
$duplicates = $mysql->query("
    SELECT COUNT(*) as duplicates
    FROM issue i 
    INNER JOIN issue_label il ON i.id = il.issue_id
    INNER JOIN label l ON l.id = il.label_id
    WHERE 1=1
    $sql_parameters
    AND l.name = 'Duplicate';
");
$data['duplicates']['value'] = $duplicates['duplicates'];
$data['duplicates']['percent'] = round($duplicates['duplicates'] / $data['total'] * 100, 2);

// -------------------------------------
// Bug per versions
$bug_per_versions = $mysql->query("
    SELECT i.id, l.name
    FROM issue i 
    INNER JOIN issue_label il ON i.id = il.issue_id
    INNER JOIN label l ON l.id = il.label_id
    INNER JOIN type t ON t.id = l.type_id
    WHERE 1=1
    $sql_parameters
    AND t.id = 2;
");

$temp = [];
foreach ($bug_per_versions as $bpv) {
    $temp[$bpv['id']][] = $bpv['name'];
}

$data_versions = [];
foreach($temp as $id => $versions) {
    usort($versions, 'version_compare');
    if (isset($data_versions[end($versions)])) {
        $data_versions[end($versions)] += 1;
    } else {
        $data_versions[end($versions)] = 1;
    }
}
arsort($data_versions);
$data['bug_per_version'] = $data_versions;

// -------------------------------------
// % bug per office (BO, FO, other)
$per_office = $mysql->query("
    SELECT i.id, l.name
    FROM issue i 
    INNER JOIN issue_label il ON i.id = il.issue_id
    INNER JOIN label l ON l.id = il.label_id
    INNER JOIN type t ON t.id = l.type_id
    WHERE 1=1
    $sql_parameters
    AND t.id = 8;
");

$percent_per_office = [
    'total' => 0,
    'BO' => 0,
    'FO' => 0,
    'Other' => 0,
];
foreach($per_office as $line) {
    if (!in_array($line['name'], ['BO', 'FO'])) {
        $percent_per_office['Other'] ++;
    } else {
        $percent_per_office[$line['name']] ++;
    }
}
$percent_per_office['total'] = count($per_office);
$data['percent_per_office'] = $percent_per_office;

// -------------------------------------
// % issues detected by TE
$detected_by_TE = $mysql->query("
    SELECT COUNT(*) as detected_by_TE
    FROM issue i 
    INNER JOIN issue_label il ON i.id = il.issue_id
    INNER JOIN label l ON l.id = il.label_id
    WHERE 1=1
    $sql_parameters
    AND l.name = 'Detected by TE';
");
$data['detected_by_TE'] = [
    'percent' => round((($detected_by_TE['detected_by_TE'] / $data['total']) * 100), 2),
    'value' => $detected_by_TE['detected_by_TE']
];

// --------------------------------------
// Severity issues in BO and avg severity for issues in FO
$per_office = $mysql->query("
    SELECT i.issue_id, GROUP_CONCAT(l.name) attributes, COUNT(l.name) nbr
    FROM issue i 
    INNER JOIN issue_label il ON i.id = il.issue_id
    INNER JOIN label l ON l.id = il.label_id
    INNER JOIN type t ON t.id = l.type_id
    WHERE 1=1
    $sql_parameters
    AND t.id IN (8, 4)
    GROUP BY i.issue_id
    HAVING nbr > 1
");
$data_sev_office = [
    'BO' => [
        'Trivial' => 0,
        'Minor' => 0,
        'Major' => 0,
        'Critical' => 0,
    ],
    'FO' => [
        'Trivial' => 0,
        'Minor' => 0,
        'Major' => 0,
        'Critical' => 0,
    ],
    'other' => [
        'Trivial' => 0,
        'Minor' => 0,
        'Major' => 0,
        'Critical' => 0,
    ]
];
foreach($per_office as $line) {
    $attributes = explode(',', $line['attributes']);
    if (in_array('BO', $attributes)) {
        if (in_array('Trivial', $attributes)) {
            $data_sev_office['BO']['Trivial'] ++;
        } elseif (in_array('Minor', $attributes)) {
            $data_sev_office['BO']['Minor'] ++;
        } elseif (in_array('Major', $attributes)) {
            $data_sev_office['BO']['Major'] ++;
        } else {
            $data_sev_office['BO']['Critical'] ++;
        }
    } elseif (in_array('FO', $attributes)) {
        if (in_array('Trivial', $attributes)) {
            $data_sev_office['FO']['Trivial'] ++;
        } elseif (in_array('Minor', $attributes)) {
            $data_sev_office['FO']['Minor'] ++;
        } elseif (in_array('Major', $attributes)) {
            $data_sev_office['FO']['Major'] ++;
        } else {
            $data_sev_office['FO']['Critical'] ++;
        }
    } else {
        if (in_array('Trivial', $attributes)) {
            $data_sev_office['other']['Trivial'] ++;
        } elseif (in_array('Minor', $attributes)) {
            $data_sev_office['other']['Minor'] ++;
        } elseif (in_array('Major', $attributes)) {
            $data_sev_office['other']['Major'] ++;
        } else {
            $data_sev_office['other']['Critical'] ++;
        }
    }
}
$data['severity_per_office'] = $data_sev_office;

$template_file_content = file_get_contents('reports/template.md');
$template_file_content = replace($template_file_content, 'start_date', $start_date);
$template_file_content = replace($template_file_content, 'end_date', $end_date);
$template_file_content = replace($template_file_content, 'type', $type);
$template_file_content = replace($template_file_content, 'total', $data['total']);
$template_file_content = replace($template_file_content, 'regressions', $data['regressions']['value']);
$template_file_content = replace($template_file_content, 'regressions_percent', $data['regressions']['percent'].'%');
$template_file_content = replace($template_file_content, 'duplicates', $data['duplicates']['value']);
$template_file_content = replace($template_file_content, 'duplicates_percent', $data['duplicates']['percent'].'%');
$template_file_content = replace($template_file_content, 'issues_detected_by_TE', $data['detected_by_TE']['value']);
$template_file_content = replace($template_file_content, 'issues_detected_by_TE_percent',
    $data['detected_by_TE']['percent'].'%');
$template_file_content = replace($template_file_content, 'generated', date('Y-m-d H:i:s'));

// issues per version
$render = '| Version | Number of issues |'.PHP_EOL;
$render .= '| ------- | ----------------- |'.PHP_EOL;
foreach($data['bug_per_version'] as $version => $value) {
    $render .= '| '.$version.' | '.$value.' |'.PHP_EOL;
}
$template_file_content = replace($template_file_content, 'issues_per_version', $render);

// issues per office
$render = '| Office | Percentage of issues |'.PHP_EOL;
$render .= '| ------- | ----------------- |'.PHP_EOL;
$render .= '|   BO    | '.$data['percent_per_office']['BO'] .' ('.round($data['percent_per_office']['BO'] / $data['percent_per_office']['total'] * 100, 2).'%) |'.PHP_EOL;
$render .= '|   FO    | '.$data['percent_per_office']['FO'] .' ('.round($data['percent_per_office']['FO'] / $data['percent_per_office']['total'] * 100, 2).'%) |'.PHP_EOL;
$render .= '|  Other  | '.$data['percent_per_office']['Other'] .' ('.round($data['percent_per_office']['Other'] / $data['percent_per_office']['total'] * 100, 2).'%) |'.PHP_EOL;

$template_file_content = replace($template_file_content, 'issues_per_office', $render);

// issues ranked by severity
// $data['severity_per_office']
$render = '| Office | Severity | Number of issues |'.PHP_EOL;
$render .= '| ------ | -------- | ----------------- |'.PHP_EOL;
$current_office = 'BO';
foreach($data['severity_per_office'] as $office => $information) {
    foreach($information as $severity => $value) {
        if ($current_office != $office) {
            $render .= '| ------ | -------- | ----------------- |'.PHP_EOL;
            $current_office = $office;
        }
        $render .= '| '.$office.' | '.$severity.' | '.$value.' |'.PHP_EOL;
    }
}
$template_file_content = replace($template_file_content, 'issues_per_office_and_severity', $render);

$report_file_name = sprintf("report_%s-%s.md", $start_date, $end_date);
$fh = fopen('reports/'.$report_file_name, 'w');
fwrite($fh, $template_file_content);
fclose($fh);

function replace($string, $tag, $content) {
    return str_replace("%$tag%", $content, $string);
}
