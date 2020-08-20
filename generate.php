<?php
use Github\Client;
use Github\ResultPager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysql.php';

$mysql = new PDOWrapper();

$client = new Client();
$client->authenticate(GITHUB_TOKEN, null, Github\Client::AUTH_ACCESS_TOKEN);
$paginator = new ResultPager($client);

$start_date = '2019-01-01';
$end_date = date('Y-m-d');


if (getenv('START_DATE') !== false) {
    $start_date = date('Y-m-d', strtotime(getenv('START_DATE')));
}
if (getenv('END_DATE') !== false) {
    $end_date = date('Y-m-d', strtotime(getenv('START_DATE')));
}

$parameters = [
    "type:issue is:closed label:Bug label:Fixed repo:prestashop/prestashop closed:$start_date..$end_date"
];
$issues = $paginator->fetchAll($client->api('search'), 'issues', $parameters);

echo "Found ".count($issues)." issues".PHP_EOL;

foreach($issues as $issue) {
    echo "Inserting issue ".$issue['number']."...".PHP_EOL;
    $sql = 'INSERT INTO `issue` (`issue_id`, `name`, `milestone`, `created`, `closed`) 
VALUES (:issue_id, :name, :milestone, :created, :closed);';
    $data = [
        'issue_id' => $issue['number'],
        'name' => $issue['title'],
        'milestone' => isset($issue['milestone']['title']) ? $issue['milestone']['title'] : '',
        'created' => date('Y-m-d H:i:s', strtotime($issue['created_at'])),
        'closed' => date('Y-m-d H:i:s', strtotime($issue['closed_at'])),
    ];
    $mysql->query($sql, $data);
    //get issue id
    $issue_id = $mysql->lastInsertId();
    //insert labels
    $labels = $issue['labels'];
    echo "    Inserting labels:  ";
    foreach($labels as $label) {
        echo $label['name']." ";
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
        echo PHP_EOL;
    }
    echo PHP_EOL;
}
