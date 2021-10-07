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
function get_petitions_url($order, $json = true, $page = 0) {
	$base_url = 'https://forums.classicpress.net/c/governance/petitions/77';
	$list_url = $base_url . ($json ? '.json' : '') . '?order=' . $order;
	if ($page) {
		$list_url .= "&page=$page";
	}
	return $list_url;
}

/**
 * Query petitions data from the ClassicPress forums via the Discourse API.
 *
 * @see https://docs.discourse.org/#operation/listCategoryTopics
 * @see https://github.com/ClassicPress/ClassicPress-APIs/issues/28
 */
function list_petitions($order, $page = 0) {
	$url = get_petitions_url($order, true, $page);
	if (getenv('PETITIONS_DEBUG')) {
		echo "GET $url\n";
	}

	$fch = curl_init($url);
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
	$petitions_page = 0;
	$results = [
		'data' => [],
		'link' => get_petitions_url($order, false),
	];

	do {
		$petitions = list_petitions($order, $petitions_page);
		if (isset($petitions['error'])) {
			echo "Error listing petitions by order=$order: $petitions[error]\n";
			exit(1);
		}
		$users_by_id = [];
		foreach ($petitions['users'] as $user) {
			$users_by_id[$user['id']] = $user;
		}
		foreach ($petitions['topic_list']['topics'] as $topic) {
			$op_id = $topic['posters'][0]['user_id'];
			$op_username = $users_by_id[$op_id]['username'];
			/**
			 * ClassicPress expects the petition status field to be one of
			 * 'open', 'planned', or 'started' (completed petitions are
			 * currently not shown in the dashboard). The data is a bit messy,
			 * so decide based on a combination of the tags and whether the
			 * petition's thread is still open.
			 */
			$petition_status = $topic['closed'] ? 'closed' : 'open';
			/**
			 * A petition gets the 'declined' tag if it has been set to close,
			 * but there is a period of 7 days before the forum thread actually
			 * closes.
			 */
			$petition_declined = false;
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
					case 'declined':
						$petition_declined = true;
						break;
				}
			}
			if ($petition_status === 'closed' || $petition_declined) {
				continue;
			}
			/**
			 * Skip 'order=latest' petitions with zero votes. This happens
			 * because the petitions forum is set to automatically bump a
			 * random topic every so often, and many of these topics have no
			 * new activity since the petitions moved to the forums.
			 */
			if ($order === 'latest' && $topic['vote_count'] === 0) {
				continue;
			}
			$results['data'][] = [
				'title' => $topic['title'],
				/**
				 * The 'description' field was previously present in this API
				 * response as an excerpt of the petition text, but this field
				 * is not used by ClassicPress and the post text is not
				 * returned by the Discourse API endpoint we are now using.
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

		if (getenv('PETITIONS_DEBUG')) {
			echo "order=$order: " . count($results['data']) . " results\n";
		}

		// 'For order=latest' we may need to fetch more pages of petitions.
		$petitions_page++;
	} while (count($results['data']) < 10);

	// Write then move, to avoid the API code reading a partially written file.
	if (getenv('PETITIONS_DEBUG')) {
		echo "write petitions-order-$order.json\n";
	}
	file_put_contents(
		__DIR__ . '/petitions-order-' . $order . '.json.tmp',
		json_encode($results)
	);
	rename(
		__DIR__ . '/petitions-order-' . $order . '.json.tmp',
		__DIR__ . '/../v1/features/1.0/petitions-order-' . $order . '.json'
	);
}
