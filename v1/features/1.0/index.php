<?php

/**
 * Query petition / voting / features requsts for ClassicPress
 * This API serves as a CORS compliant proxy service to fetch this data on behalf of ClassicPress installations
 *
 */

function get_petitions() {

    /**
     * The format below dictates what is shown in the Features Dashboard block.  Put this together in this way
     * to drive potentially different data to ClassicPress Dashboard Features panel on-demand without pushing new CP code.
     *
     * The array is shown in order, using the text string key as the visible section title
     * The 'query' is passed to fider as the value for the view parameter.
     * The 'limit' is passed to fider as the limit value
     * The 'tags' are passed to fider as the tags filter, and are shown on the Dashboard block below the excerpt
     * 'list_link' shows a link within the Dashboard block for the admin to click through and see the full list for that query-section
     * 'full_list_link' takes the admin to the base page having all the feature / change / vote requests
     */

    $query_blocks = [
         'Trending' => [
            'method' => 'GET',
            'action' => 'posts',
            'query' => 'trending',
            'limit' => 2,
            'tags' => '',
            'list_link' => 'https://petitions.classicpress.net/?view=trending',
        ],
        'Most Recent' => [
            'method' => 'GET',
            'action' => 'posts',
            'query' => 'recent',
            'limit' => 2,
            'tags' => '',
            'list_link' => 'https://petitions.classicpress.net/?view=recent',
            ],
        'Most Wanted' => [
            'http_method' => 'GET',
            'action' => 'posts',
            'query' => 'most-wanted',
            'limit' => 2,
            'tags' =>'',
            'list_link' => 'https://petitions.classicpress.net/?view=most-wanted',
        ],
        
    ];


    foreach ($query_blocks as $cbk => $cbv) {

        $query_string = '';

        if ($cbv['query'] != '') {
            $query_string .= 'view=' . $cbv['query'];

            if ($cbv['tags'] != '') {
                $query_string .= '&tags=' . $cbv['tags'];
            }

            if (isset($cbv['limit'])) {
                $query_string .= '&limit=' . $cbv['limit'];
            }
        }

        // Go query the remote API, make sure reasonable parameters are passed if they are not set
        $query_result = proxy_query($query_string, ($cbv['method'] == '' ? 'GET' : $cbv['method']), ($cbv['action'] == '' ? 'posts' : $cbv['action']));

        // At a minimum, add the link to where the user can go to see the full list
        if ($cbv['list_link'] != '') {
            $query_result['list_link'] = $cbv['list_link'];
        }

        $response[] = [$cbk => $query_result];
    }


    $json = [
        '1.0.0' => 'latest',
        'full_list_link' => 'https://petitions.classicpress.net',
        'petitions' => $response,
    ];

    // Set the headers which should allow CORS restrictions
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Max-Age: 3600');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Content-Type: application/json');
    echo json_encode($json);
}

/**
 * Execute the remote service query via cURL
 */
function proxy_query( $query_string, $method, $action ) {

    // Currently this is hosted at getfider.io
    $api_endpoint = "https://classicpress.fider.io/api/v1";

    $fch = curl_init($api_endpoint . '/' . $action . '?' . $query_string);

    curl_setopt($fch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($fch, CURLOPT_CUSTOMREQUEST, $method);
    $response = curl_exec($fch);

    $returned_data = json_decode(trim($response));

    if (curl_getinfo($fch, CURLINFO_HTTP_CODE) != 200) {
        return [];
    }

    return $returned_data;
}


// Get the latest ClassicPress Petitions
get_petitions();

