<?php
// Custom config that properly reads environment variables
// This file will override config-default.php if it exists

// Reddit username
if (getenv("REDDIT_USER")) {
  define('REDDIT_USER', getenv("REDDIT_USER"));
} elseif (!empty($_SERVER["REDDIT_USER"])) {
  define('REDDIT_USER', $_SERVER["REDDIT_USER"]);
} else {
  define('REDDIT_USER', 'your_reddit_user');
}

// Reddit Client ID
if (getenv("REDDIT_CLIENT_ID")) {
  define('REDDIT_CLIENT_ID', getenv("REDDIT_CLIENT_ID"));
} elseif (!empty($_SERVER["REDDIT_CLIENT_ID"])) {
  define('REDDIT_CLIENT_ID', $_SERVER["REDDIT_CLIENT_ID"]);
} else {
  define('REDDIT_CLIENT_ID', 'your_app_id');
}

// Reddit Client Secret
if (getenv("REDDIT_CLIENT_SECRET")) {
  define('REDDIT_CLIENT_SECRET', getenv("REDDIT_CLIENT_SECRET"));
} elseif (!empty($_SERVER["REDDIT_CLIENT_SECRET"])) {
  define('REDDIT_CLIENT_SECRET', $_SERVER["REDDIT_CLIENT_SECRET"]);
} else {
  define('REDDIT_CLIENT_SECRET', 'your_app_secret');
}

// Default subreddit
if (getenv("DEFAULT_SUBREDDIT")) {
  define('DEFAULT_SUBREDDIT', getenv("DEFAULT_SUBREDDIT"));
} elseif (!empty($_SERVER["DEFAULT_SUBREDDIT"])) {
  define('DEFAULT_SUBREDDIT', $_SERVER["DEFAULT_SUBREDDIT"]);
} else {
  define('DEFAULT_SUBREDDIT', 'pics');
}

// Mercury Parser URL
if (getenv("MERCURY_URL")) {
  define('MERCURY_URL', getenv("MERCURY_URL"));
} elseif (!empty($_SERVER["MERCURY_URL"])) {
  define('MERCURY_URL', $_SERVER["MERCURY_URL"]);
} else {
  define('MERCURY_URL', '');
}

// Mercury API key
if (getenv("MERCURY_API_KEY")) {
  define('MERCURY_API_KEY', getenv("MERCURY_API_KEY"));
} elseif (!empty($_SERVER["MERCURY_API_KEY"])) {
  define('MERCURY_API_KEY', $_SERVER["MERCURY_API_KEY"]);
} else {
  define('MERCURY_API_KEY', '');
}

// Cache Reddit JSON files
if (getenv("CACHE_REDDIT_JSON")) {
  define('CACHE_REDDIT_JSON', getenv("CACHE_REDDIT_JSON"));
} elseif (!empty($_SERVER["CACHE_REDDIT_JSON"])) {
  define('CACHE_REDDIT_JSON', $_SERVER["CACHE_REDDIT_JSON"]);
} else {
  define('CACHE_REDDIT_JSON', 'true');
}

// Cache Mercury Content
if (getenv("CACHE_MERCURY_CONTENT")) {
  define('CACHE_MERCURY_CONTENT', getenv("CACHE_MERCURY_CONTENT"));
} elseif (!empty($_SERVER["CACHE_MERCURY_CONTENT"])) {
  define('CACHE_MERCURY_CONTENT', $_SERVER["CACHE_MERCURY_CONTENT"]);
} else {
  define('CACHE_MERCURY_CONTENT', 'true');
}

// Cache RSS feeds
if (getenv("CACHE_RSS_FEEDS")) {
  define('CACHE_RSS_FEEDS', getenv("CACHE_RSS_FEEDS"));
} elseif (!empty($_SERVER["CACHE_RSS_FEEDS"])) {
  define('CACHE_RSS_FEEDS', $_SERVER["CACHE_RSS_FEEDS"]);
} else {
  define('CACHE_RSS_FEEDS', 'true');
}

// Config version
if (getenv("CONFIG_VERSION")) {
  define('CONFIG_VERSION', getenv("CONFIG_VERSION"));
} elseif (!empty($_SERVER["CONFIG_VERSION"])) {
  define('CONFIG_VERSION', $_SERVER["CONFIG_VERSION"]);
} else {
  define('CONFIG_VERSION', 1);
}
?>