<?php

/*
 * A starter script for interacting with the Drupal 8 REST API
 * To use, place in the webroot of a Drupal 8 site
 * Tutorial to install a fresh Drupal 8 site: https://www.caxy.com/blog/setting-drupal-8-2-minutes-drush
 *
 */

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
$autoloader = require_once __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;

// Update to a user on your Drupal site that has permissions to create content
$user = 'admin';
$pass = 'password1234';
$url = 'http://127.0.0.1:9200';

// A great curl wrapper
$client = new GuzzleHttp\Client([
    'base_uri' => $url,
    'timeout'  => 2.0,
]);

// Drupal needs a CSRF Token for POST
// Gotcha: do not send auth when getting the token
$res = $client->request('GET', '/rest/session/token');
$csrf_token = $res->getBody();

// Check to see if it worked
if ($res->getStatusCode() == 200) {
    echo "CSRF Success: " . $csrf_token ."\n";
} else {
    echo "Trouble getting the token.\n";
}


// Set up a serialized JSON package to send
$serialized_entity = json_encode([
    'title' => [['value' => 'Example node title ' . time()]],
    'type' => [['target_id' => 'article']],
    '_links' => ['type' => [
        'href' => $url . '/rest/type/node/article'
    ]],
]);

$content_client = new GuzzleHttp\Client([
    'base_uri' => $url,
    'timeout'  => 2.0,
]);

// Send the JSON to the Drupal 8 API
// Note: In future versions of the API, the URL will change to /node from /entity/node
$res = $content_client->request('POST', '/entity/node?_format=hal_json', [
    'auth' => [$user, $pass],
    'body' => $serialized_entity,
    'headers' => [
        'Content-Type' => 'application/hal+json',
        'X-CSRF-Token' => $csrf_token
    ]
]);

// Check to see if it worked
if ($res->getStatusCode() == 201) {
    echo "POST Success: " . $res->getBody(). "\n";
} else {
    echo "HTTP Error with POST: " . $res->getStatusCode() .  "\n";
}
