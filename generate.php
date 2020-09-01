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

$after = 'Y3Vyc29yOnYyOpHOFQk5vw==';

$query = '
{
  repository(name: "PrestaShop", owner: "PrestaShop") {
    issues(labels: "Bug", first: 100, after: "%AFTER%") {
      edges {
        cursor
        node {
          number
          title
          createdAt
          state
          closed
          closedAt
          milestone {
            title
          }
          labels(last: 100) {
            nodes {
              name
              description
            }
          }
        }
      }
    }
  }
}
';

$issues_data = $client->api('graphql')->execute(str_replace('%AFTER%', $after, $query));

while(count($issues_data['data']['repository']['issues']['edges']) > 0) {
    $issues = $issues_data['data']['repository']['issues']['edges'];
    echo sprintf("Found %s issues starting at %s...%s", count($issues), $issues[0]['node']['createdAt'], PHP_EOL);

    foreach($issues as $issue) {
        //putting the cursor to iterate on the next results
        $after = $issue['cursor'];
        $labels = [];
        foreach($issue['node']['labels']['nodes'] as $label) {
            $labels[] = $label;
        }
        $sql = 'SELECT id, state FROM `issue` WHERE `issue_id` = :issue_id;';
        $data = [
            'issue_id' => $issue['node']['number'],
        ];
        $issue_exists = $mysql->query($sql, $data);
        if (isset($issue_exists['id'])) {
            if ($issue_exists['state'] == 'open' && strtolower($issue['node']['state']) == 'closed') {
                //this issue was closed so we can update it in the database
                //we remove all its labels and do it again just to be sure
                $sql = 'DELETE FROM issue_label WHERE issue_id = :issue_id;';
                $data = [
                    'issue_id' => $issue_exists['id'],
                ];
                $mysql->query($sql, $data);
                echo sprintf("Updating issue #%s (%s)%s", $issue['node']['number'], $issue['node']['title'], PHP_EOL);
                insert_labels($mysql, $issue_exists['id'], $labels);
            } else {
                continue;
            }
        }

        $sql = 'INSERT INTO `issue` (`issue_id`, `name`, `state`, `milestone`, `created`, `closed`) 
VALUES (:issue_id, :name, :state, :milestone, :created, :closed);';
        $data = [
            'issue_id' => $issue['node']['number'],
            'name' => $issue['node']['title'],
            'state' => strtolower($issue['node']['state']),
            'milestone' => $issue['node']['milestone'] != null ? $issue['node']['milestone']['title'] : '',
            'created' => date('Y-m-d H:i:s', strtotime($issue['node']['createdAt'])),
            'closed' => ($issue['node']['closedAt'] == null) ? null : date('Y-m-d H:i:s', strtotime($issue['node']['closedAt'])),
        ];
        $mysql->query($sql, $data);
        echo sprintf("Inserting issue #%s (%s)%s", $issue['node']['number'], $issue['node']['title'], PHP_EOL);
        //get issue id
        $issue_id = $mysql->lastInsertId();
        //insert labels
        insert_labels($mysql, $issue_id, $labels);
    }
    //relaunch the query with the next batch
    $issues_data = $client->api('graphql')->execute(str_replace('%AFTER%', $after, $query));
};

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
            echo sprintf("Creating the new label %s (%s)...%s", $label['name'], $label['description'], PHP_EOL);
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