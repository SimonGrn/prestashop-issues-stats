<?php
use Github\Client;
use Github\ResultPager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysql.php';

if (getenv('SECURITY_TOKEN') != SECURITY_TOKEN) {
    die("Wrong token".PHP_EOL);
}

$mysql = new PDOWrapper();

$client = new Client();
$client->authenticate(GITHUB_TOKEN, null, Github\Client::AUTH_ACCESS_TOKEN);
$paginator = new ResultPager($client);

$start_date = '2018-07-01';
$end_date = date('Y-m-d');

if (getenv('START_DATE') !== false) {
    $start_date = date('Y-m-d', strtotime(getenv('START_DATE')));
}
if (getenv('END_DATE') !== false) {
    $end_date = date('Y-m-d', strtotime(getenv('END_DATE')));
}

$start_date_ts = strtotime($start_date);
$end_date_ts = strtotime($end_date);
$interval = 60*60*24*91; //91 days
$next_interval = min($end_date_ts, $start_date_ts+$interval);

do {
    //populate or update the database
    $start_date_request = date('Y-m-d', $start_date_ts);
    $end_date_request = date('Y-m-d', $next_interval);
    echo "FROM $start_date_request to $end_date_request".PHP_EOL;

    $parameters = [
        "type:issue label:Bug repo:prestashop/prestashop created:$start_date_request..$end_date_request"
    ];
    $issues = $paginator->fetchAll($client->api('search'), 'issues', $parameters);

    echo "Found ".count($issues)." issues".PHP_EOL;

    foreach($issues as $issue) {
        $labels = $issue['labels'];
        $sql = 'SELECT id, state FROM `issue` WHERE `issue_id` = :issue_id;';
        $data = [
            'issue_id' => $issue['number'],
        ];
        $issue_exists = $mysql->query($sql, $data);
        if (isset($issue_exists['id'])) {
//            echo sprintf("Issue %s already in the database, updating/skipping...%s", $issue['number'], PHP_EOL);
            if ($issue_exists['state'] == 'open' && $issue['state'] == 'closed') {
                //this issue was closed so we can update it in the database
                //we remove all its labels and do it again just to be sure
                echo sprintf("Updating issue %s%s", $issue_exists['id'], PHP_EOL);
                $sql = 'DELETE FROM issue_label WHERE issue_id = :issue_id;';
                $data = [
                    'issue_id' => $issue_exists['id'],
                ];
                $mysql->query($sql, $data);
                insert_labels($mysql, $issue_exists['id'], $labels);
            } else {
                continue;
            }
        }

//        echo sprintf("Inserting issue %s (%s)...%s", $issue['number'], $issue['state'], PHP_EOL);
        $sql = 'INSERT INTO `issue` (`issue_id`, `name`, `state`, `milestone`, `created`, `closed`) 
VALUES (:issue_id, :name, :state, :milestone, :created, :closed);';
        $data = [
            'issue_id' => $issue['number'],
            'name' => $issue['title'],
            'state' => $issue['state'],
            'milestone' => isset($issue['milestone']['title']) ? $issue['milestone']['title'] : '',
            'created' => date('Y-m-d H:i:s', strtotime($issue['created_at'])),
            'closed' => ($issue['closed_at'] == null) ? null : date('Y-m-d H:i:s', strtotime($issue['closed_at'])),
        ];
        $mysql->query($sql, $data);
        //get issue id
        $issue_id = $mysql->lastInsertId();
        //insert labels
        insert_labels($mysql, $issue_id, $labels);
    }
    $start_date_ts = $next_interval;
    $next_interval = $next_interval+$interval;
} while (strtotime($end_date_request) < $end_date_ts);

function insert_labels($mysql, $issue_id, $labels) {
    foreach($labels as $label) {
        //does this label already exists ?
        $sql = 'SELECT id FROM `label` WHERE `name` = :name;';
        $data = [
            'name' => $label['name'],
        ];
        $label_exists = $mysql->query($sql, $data);
        if (isset($label_exists['id'])) {
            //we just add this existing label to this issue
            $sql = 'INSERT INTO `issue_label` (`issue_id`, `label_id`) VALUES (:issue_id, :label_id);';
            $data = [
                'issue_id' => $issue_id,
                'label_id' => $label_exists['id'],
            ];
            $mysql->query($sql, $data);
        } else {
            //we create the label and add it to the issue
            $sql = 'INSERT INTO `label` (`name`, `description`) VALUES (:name, :description);';
            $data = [
                'name' => $label['name'],
                'description' => $label['description'],
            ];
            $mysql->query($sql, $data);
            $label_id = $mysql->lastInsertId();
            $sql = 'INSERT INTO `issue_label` (`issue_id`, `label_id`) VALUES (:issue_id, :label_id);';
            $data = [
                'issue_id' => $issue_id,
                'label_id' => $label_id,
            ];
            $mysql->query($sql, $data);
        }
    }
}