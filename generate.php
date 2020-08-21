<?php
use Github\Client;
use Github\ResultPager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysql.php';

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

$parameters = [
    "type:issue label:Bug repo:prestashop/prestashop created:$start_date..$end_date"
];
$issues = $paginator->fetchAll($client->api('search'), 'issues', $parameters);

echo "Found ".count($issues)." issues".PHP_EOL;

foreach($issues as $issue) {
    $sql = 'SELECT id FROM `issue` WHERE `issue_id` = :issue_id;';
    $data = [
        'issue_id' => $issue['number'],
    ];
    $issue_exists = $mysql->query($sql, $data);
    if (isset($issue_exists['id'])) {
        echo sprintf("Issue %s already in the database, skipping...%s", $issue['number'], PHP_EOL);
        continue;
    }

    echo sprintf("Inserting issue %s (%s)...%s", $issue['number'], $issue['state'], PHP_EOL);
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
    $labels = $issue['labels'];
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
