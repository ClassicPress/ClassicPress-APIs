<?php
header('Content-Type: application/json');

// A list of importer plugins used in the core.
// WordPress API: https://api.wordpress.org/core/importers/1.1/
// Used in /wp-admin/includes/import.php

echo json_encode([
    "importers" => [
        "blogger" => [
            "name" => "Blogger",
            "description" => "Import posts, comments, and users from a Blogger blog.",
            "plugin-slug" => "blogger-importer",
            "importer-id" => "blogger"
        ],
        "wpcat2tag" => [
            "name" => "Categories and Tags Converter",
            "description" => "Convert existing categories to tags or tags to categories, selectively.",
            "plugin-slug" => "wpcat2tag-importer",
            "importer-id" => "wpcat2tag"
        ],
        "livejournal" => [
            "name" => "LiveJournal",
            "description" => "Import posts from LiveJournal using their API.",
            "plugin-slug" => "livejournal-importer",
            "importer-id" => "livejournal"
        ],
        "movabletype" => [
            "name" => "Movable Type and TypePad",
            "description" => "Import posts and comments from a Movable Type or TypePad blog.",
            "plugin-slug" => "movabletype-importer",
            "importer-id" => "mt"
        ],
        "opml" => [
            "name" => "Blogroll",
            "description" => "Import links in OPML format.",
            "plugin-slug" => "opml-importer",
            "importer-id" => "opml"
        ],
        "rss" => [
            "name" => "RSS",
            "description" => "Import posts from an RSS feed.",
            "plugin-slug" => "rss-importer",
            "importer-id" => "rss"
        ],
        "tumblr" => [
            "name" => "Tumblr",
            "description" => "Import posts &amp; media from Tumblr using their API.",
            "plugin-slug" => "tumblr-importer",
            "importer-id" => "tumblr"
        ],
        "wordpress" => [
            "name" => "WordPress",
            "description" => "Install the WordPress importer to import posts, pages, comments, custom fields, categories, and tags from a WordPress export file.",
            "plugin-slug" => "wordpress-importer",
            "importer-id" => "wordpress"
        ]
    ],
    "translated" => false
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);