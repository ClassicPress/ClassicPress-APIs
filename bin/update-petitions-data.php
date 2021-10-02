#!/usr/bin/env php
<?php

// Prevent usage in a webserver.
if (php_sapi_name() !== 'cli') {
	die('Command line only');
}

/**
 * Query petitions (feature requests) for ClassicPress via the forums and save
 * the data to a set of JSON files which are returned by the API endpoint.
 */

/**
 * Return a URL for petitions, in JSON (API) or HTML (webpage) format.
 */
function get_petitions_url($order, $json = true) {
	$base_url = 'https://forums.classicpress.net/c/governance/petitions/77';
	return $base_url . ($json ? '.json' : '') . '?order=' . $order;
}

/**
 * Query petitions data from the ClassicPress forums via the Discourse API.
 *
 * @see https://docs.discourse.org/#operation/listCategoryTopics
 * @see https://github.com/ClassicPress/ClassicPress-APIs/issues/28
 */
function list_petitions($order) {
	$fch = curl_init(get_petitions_url($order));
	curl_setopt($fch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($fch);
	$response = json_decode($response, true);

	$status_code = curl_getinfo($fch, CURLINFO_HTTP_CODE);
	if ($status_code !== 200) {
		return ['error' => $status_code];
	}
	if (!is_array($response)) {
		return ['error' => 'unknown'];
	}

	return $response;
}

/**
 * Override the `created_at` date for petitions that were first created on
 * Fider and then migrated to the forums, because the date of the forum thread
 * is not the same as the date of the original petition.
 */
$override_created_at = json_decode(
	file_get_contents(__DIR__ . '/../v1/features/1.0/fider-created-at.json'),
	true
);

// The main loop for this script.
foreach (['votes', 'latest', 'created'] as $order) {
	$petitions = list_petitions($order);
	if (isset($petitions['error'])) {
		die("Error listing petitions by order=$order: $petitions[error]");
	}
	$results = [
		'data' => [],
		'link' => get_petitions_url($order, false),
	];
	$users_by_id = [];
	foreach ($petitions['users'] as $user) {
		$users_by_id[$user['id']] = $user;
	}
	foreach ($petitions['topic_list']['topics'] as $topic) {
		$op_id = $topic['posters'][0]['user_id'];
		$op_username = $users_by_id[$op_id]['username'];
		/**
		 * ClassicPress expects the petition status field to be one of 'open',
		 * 'planned', or 'started' (completed petitions are currently not shown
		 * in the dashboard). The data is a bit messy, so decide based on a
		 * combination of the tags and whether the petition's thread is still
		 * open.
		 */
		$petition_status = $topic['closed'] ? 'closed' : 'open';
		foreach ($topic['tags'] as $tag) {
			switch ($tag) {
				case 'planned':
					if ($petition_status !== 'started') {
						$petition_status = 'planned';
					}
					break;
				case 'pull-request':
				case 'cp-research-plugin':
					$petition_status = 'started';
					break;
			}
		}
		if ($petition_status === 'closed') {
			continue;
		}
		/**
		 * Skip 'latest' petitions with zero votes. This happens because the
		 * petitions forum is set to automatically bump a random topic every so
		 * often, and many of these topics have no new activity since the
		 * petitions moved to the forums.
		 */
		if ($order === 'latest' && $topic['vote_count'] === 0) {
			continue;
		}
		$results['data'][] = [
			'title' => $topic['title'],
			/**
			 * The 'description' field was previously present in this API
			 * response as an excerpt of the petition text, but this field is
			 * not used by ClassicPress and the post text is not returned by
			 * the Discourse API endpoint we are now using.
			 */
			'description'   => '',
			'createdAt'     => $override_created_at[$topic['id']] ?? $topic['created_at'],
			'createdBy'     => $op_username,
			'votesCount'    => $topic['vote_count'],
			'commentsCount' => $topic['reply_count'],
			'status'        => $petition_status,
			'tags'          => $topic['tags'],
			'link'          => "https://forums.classicpress.net/t/$topic[slug]/$topic[id]",
		];
		if (count($results['data']) === 10) {
			break;
		}
	}
	// Write then move, to avoid the API code reading a partially written file.
	file_put_contents(
		__DIR__ . '/petitions-order-' . $order . '.json.tmp',
		json_encode($results)
	);
	rename(
		__DIR__ . '/petitions-order-' . $order . '.json.tmp',
		__DIR__ . '/../v1/features/1.0/petitions-order-' . $order . '.json'
	);
}
