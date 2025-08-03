<?php
// With some help from https://www.sanwebe.com/2013/07/creating-valid-rss-feed-using-php

// Load configuration - CRITICAL FIX for standalone access
if (!defined('REDDIT_USER')) {
	// Load environment-based config if available
	if (file_exists('env-config.php')) {
		require_once 'env-config.php';
	} elseif (file_exists('config.php')) {
		require_once 'config.php';
	} else {
		// Fallback to default config
		require_once 'config-default.php';
	}
	// Load functions after config
	require_once 'functions.php';
	// Load auth to get access token
	require_once 'auth.php';
}

header("Content-Type: text/xml; charset=utf-8", true);


// Return cached RSS feed, if it exists and if it was cached in the past hour
if(CACHE_RSS_FEEDS) {
	if(file_exists("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml") && time() - filemtime("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml") < 60 * 60) {
		echo file_get_contents("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml", true);
		exit;
	}
}


// Sort and filter
include "sort-and-filter.php";


// Get Subreddit feed
$jsonFeedFile = getFile("https://oauth.reddit.com/r/" . $subreddit . ".json", "redditJSON", "cache/reddit/$subreddit.json", 60 * 5, $accessToken);

// Check if Reddit API call failed
if (!$jsonFeedFile || $jsonFeedFile === false) {
	// Output error as XML comment for debugging
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<rss version="2.0"><channel>';
	echo '<title>Error</title>';
	echo '<description>Failed to connect to Reddit API. Please check your credentials.</description>';
	echo '<link>https://www.reddit.com</link>';
	echo '<!-- Debug: No response from Reddit API -->';
	echo '</channel></rss>';
	exit;
}

$jsonFeedFileParsed = json_decode($jsonFeedFile, true);

// Check if JSON parsing failed or no data
if (!$jsonFeedFileParsed || !isset($jsonFeedFileParsed["data"]["children"])) {
	// Output error as XML comment for debugging
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<rss version="2.0"><channel>';
	echo '<title>Error</title>';
	echo '<description>Invalid response from Reddit API</description>';
	echo '<link>https://www.reddit.com</link>';
	echo '<!-- Debug: ' . htmlspecialchars(substr($jsonFeedFile, 0, 500)) . ' -->';
	echo '</channel></rss>';
	exit;
}

$jsonFeedFileItems = $jsonFeedFileParsed["data"]["children"];
usort($jsonFeedFileItems, "sortByCreatedDate");


// Feed description text
$feedDescriptionText = "Hot posts in /r/";
$feedDescriptionText .= $subreddit;
if($thresholdScore) {
	if(isset($_GET["score"]) && $_GET["score"]) {
		$feedDescriptionText .= " at or above a score of ";
		$feedDescriptionText .= $_GET["score"];
	}
	if(isset($_GET["threshold"]) && $_GET["threshold"]) {
		$feedDescriptionText .= " at or above ";
		$feedDescriptionText .= $thresholdPercentage;
		$feedDescriptionText .= "% of monthly top posts' average score";
	}
	if(isset($_GET["averagePostsPerDay"]) && $_GET["averagePostsPerDay"]) {
		$feedDescriptionText .= " (roughly ";
		$feedDescriptionText .= $_GET["averagePostsPerDay"];
		$feedDescriptionText .= " posts per day)";
	}
}


// Create new DOM document
$xml = new DOMDocument("1.0", "UTF-8");


// Create "RSS" element
$rss = $xml->createElement("rss");
$rssNode = $xml->appendChild($rss);
$rssNode->setAttribute("version", "2.0");


// Set attributes with media namespace for better image handling
$rssNode->setAttribute("xmlns:dc", "http://purl.org/dc/elements/1.1/");
$rssNode->setAttribute("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
$rssNode->setAttribute("xmlns:atom", "https://www.w3.org/2005/Atom");
$rssNode->setAttribute("xmlns:media", "http://search.yahoo.com/mrss/");


// Create RFC822 Date format to comply with RFC822
$dateF = date("D, d M Y H:i:s T", time());
$buildDate = gmdate(DATE_RFC2822, strtotime($dateF));


// Create "channel" element under "RSS" element
$channel = $xml->createElement("channel");
$channelNode = $rssNode->appendChild($channel);


// A feed should contain an atom:link element
// http://j.mp/1nuzqeC
$channelAtomLink = $xml->createElement("atom:link");
$channelAtomLink->setAttribute("href", "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
$channelAtomLink->setAttribute("rel", "self");
$channelAtomLink->setAttribute("type", "application/rss+xml");
$channelNode->appendChild($channelAtomLink);


// Add general elements under "channel" node
$channelNode->appendChild($xml->createElement("title", "/r/" . $subreddit));
$channelNode->appendChild($xml->createElement("description", $feedDescriptionText));
$channelNode->appendChild($xml->createElement("link", "https://www.reddit.com/r/" . $subreddit));
$channelNode->appendChild($xml->createElement("language", "en-us"));
$channelNode->appendChild($xml->createElement("lastBuildDate", $buildDate));
$channelNode->appendChild($xml->createElement("generator", "Reddit Top RSS"));
$channelImageNode = $channelNode->appendChild($xml->createElement("image"));
$channelImageNode->appendChild($xml->createElement("url", "https://www.redditstatic.com/desktop2x/img/favicon/android-icon-192x192.png"));
$channelImageNode->appendChild($xml->createElement("title", "/r/" . $subreddit));
$channelImageNode->appendChild($xml->createElement("link", "https://www.reddit.com/r/" . $subreddit));


// Loop through feed items
foreach($jsonFeedFileItems as $item) {


	// Only show posts at or above score threshold
	if ($item["data"]["score"] >= $thresholdScore) {


		// Remove any utm junk from main item link
		$itemDataUrl = $item["data"]["url"];
		$itemDataUrl = preg_replace("/&?utm_(.*?)\=[^&]+/","","$itemDataUrl");


		// Create a new node called "item"
		$itemNode = $channelNode->appendChild($xml->createElement("item"));


		// Item Title
		$itemTitle = $item["data"]["title"];
		if($item["data"]["domain"]) {
			$itemTitle .= " (" . $item["data"]["domain"] . ")";
		}
		if (
			(strpos($itemDataUrl,"imgur") !== false && strpos($itemDataUrl,"gallery") !== false) ||
			strpos($item["data"]["url"], "www.reddit.com/gallery/")
		) {
			$itemTitle .= " (Gallery)";
		}
		$titleNode = $itemNode->appendChild($xml->createElement("title", $itemTitle));


		// Item Link
		$linkNode = $itemNode->appendChild($xml->createElement("link", $itemDataUrl));


		// Unique identifier for the item (GUID)
		$guidLink = $xml->createElement("guid", "https://www.reddit.com" . $item["data"]["permalink"]);
		$guidLink->setAttribute("isPermaLink", "false");
		$guid_node = $itemNode->appendChild($guidLink);


		// Create "comments" node under "item"
		if(strpos($item["data"]["domain"], "self.") == false) {
			$commentsNode = $itemNode->appendChild($xml->createElement("comments", "https://www.reddit.com" . $item["data"]["permalink"]));
		}


		// Create description text for both "description" and "content:encoded" nodes
		$itemDescription = "";


		// For i.redd.it posts, create a cleaner structure like the Go version
		if ($item["data"]["domain"] == "i.redd.it") {
			// Add metadata section like Go version
			$author = isset($item["data"]["author"]) ? $item["data"]["author"] : "unknown";
			$flair = isset($item["data"]["link_flair_text"]) ? $item["data"]["link_flair_text"] : "";
			
			$itemDescription .= '<div class="post-metadata" style="margin-bottom:15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; border-left: 3px solid #ff4500;">';
			$itemDescription .= '<div style="display: flex; flex-direction: column;">';
			$itemDescription .= '<div style="display: flex; align-items: center; flex-wrap: wrap; margin-bottom: 8px;">';
			$itemDescription .= '<strong style="margin-right: 10px;"><a href="https://old.reddit.com/u/' . $author . '" style="text-decoration: none; color: #336699;">u/' . $author . '</a></strong>';
			$itemDescription .= '<span style="color: #666; font-size: 0.9em;">' . $item["data"]["score"] . ' points</span>';
			if ($flair) {
				$itemDescription .= ' • <span style="display:inline-block; background-color:#f0f0f0; padding:2px 8px; border-radius:3px; font-size:0.9em;">' . htmlspecialchars($flair) . '</span>';
			}
			$itemDescription .= '</div>';
			$itemDescription .= '<div style="margin-top: 8px;">';
			$itemDescription .= '<a href="https://old.reddit.com' . $item["data"]["permalink"] . '" style="text-decoration: none; color: #336699;">View Comments on Reddit</a>';
			$itemDescription .= '</div>';
			$itemDescription .= '</div>';
			$itemDescription .= '</div>';
		} else {
			// Keep original structure for non-i.redd.it posts
			// Description comments link and metadata
			$itemDescription .= "<p><a href='https://www.reddit.com" . $item["data"]["permalink"] . "'>Post permalink</a></p>";
			
			// Add post score (upvotes)
			$itemDescription .= "<p>Score: " . $item["data"]["score"] . " upvotes</p>";
		}


		// Add media if it exists
		switch (true) {

			// General embeds
			case isset($item["data"]["media_embed"]["content"]):
				$mediaEmbed = $item["data"]["media_embed"]["content"];
				$mediaEmbed = str_replace("&lt;", "<", $mediaEmbed);
				$mediaEmbed = str_replace("&gt;", ">", $mediaEmbed);
				$itemDescription .= $mediaEmbed;
			break;
			case isset($item["data"]["secure_media_embed"]["content"]):
				$mediaEmbed = $item["data"]["secure_media_embed"]["content"];
				$mediaEmbed = str_replace("&lt;", "<", $mediaEmbed);
				$mediaEmbed = str_replace("&gt;", ">", $mediaEmbed);
				$itemDescription .= $mediaEmbed;
			break;
			case isset($item["data"]["secure_media"]["oembed"]["html"]):
				$mediaEmbed = $item["data"]["secure_media"]["oembed"]["html"];
				$mediaEmbed = str_replace("&lt;", "<", $mediaEmbed);
				$mediaEmbed = str_replace("&gt;", ">", $mediaEmbed);
				$itemDescription .= $mediaEmbed;
			break;

			// Imgur gifv
			case strpos($item["data"]["url"], "imgur") && strpos($item["data"]["url"], "gifv"):
				$mediaEmbed = "<video poster='" . str_replace(".gifv", "h.jpg", $item["data"]["url"]) . "' controls='true' preload='auto' autoplay='false' muted='muted' loop='loop' webkit-playsinline='' style='max-width: 90vw;'>
					<source src='" . str_replace("gifv", "mp4", $item["data"]["url"]) . "' type='video/mp4'>
				</video>";
				$itemDescription .= $mediaEmbed;
			break;

			// Reddit videos
			case $item["data"]["domain"] == "v.redd.it":
				$mediaEmbed = "<iframe height='800' width='800' frameborder='0' allowfullscreen='yes' scrolling='yes' src='https://old.reddit.com/mediaembed/" . $item["data"]["id"] . "'></iframe>";
				$mediaEmbed .= "<p>If video doesn't play, <a href='https://www.reddit.com" . $item["data"]["permalink"] . "'>view on Reddit with audio</a></p>";
				$mediaEmbed .= "<p><img src='" . $item["data"]["thumbnail"] . "' /></p>";
				$itemDescription .= $mediaEmbed;
			break;

			// Reddit galleries - add webfeedsFeaturedVisual class to all images
			case strpos($item["data"]["url"], "www.reddit.com/gallery/"):
				$jsonGalleryURL = str_replace("www.reddit.com/gallery/", "oauth.reddit.com/comments/", $item["data"]["url"]) . '.json';
				$jsonGalleryFileName = str_replace("https://oauth.reddit.com/comments/", "", $jsonGalleryURL);
				$jsonGalleryFile = getFile($jsonGalleryURL, "redditJSON", "cache/reddit/$jsonGalleryFileName", 60 * 5, $accessToken);
				$jsonGalleryFileParsed = json_decode($jsonGalleryFile, true);
				$jsonGalleryFileItems = $jsonGalleryFileParsed[0]["data"]["children"][0]["data"]["media_metadata"];
				$mediaEmbed = "";
				$imageCount = 0;
				foreach ($jsonGalleryFileItems as $image) :
					$previewImageURL = str_replace("preview.redd.it", "i.redd.it", $image["p"]["3"]["u"]);
					$previewImageURL = str_replace("&amp;", "&", $previewImageURL);
					$fullImageURL = str_replace("preview.redd.it", "i.redd.it", $image["s"]["u"]);
					$fullImageURL = str_replace("&amp;", "&", $fullImageURL);
					$imageCount++;
					$mediaEmbed .= '<a href="' . $fullImageURL . '"><img src="' . $previewImageURL . '" class="webfeedsFeaturedVisual" alt="Gallery image ' . $imageCount . '" style="max-width:100%; width:auto; display:block; margin:0 auto; margin-bottom:10px;" /></a>';
				endforeach;
				$itemDescription .= $mediaEmbed;
				break;

			// Reddit images - use webfeedsFeaturedVisual class for RSS reader compatibility
			case $item["data"]["domain"] == "i.redd.it":
				$imageAltText = htmlspecialchars($item["data"]["title"]);
				// Match Go version structure exactly - simpler without extra styling
				$mediaEmbed = '<img src="' . $item["data"]["url"] . '" class="webfeedsFeaturedVisual" alt="' . $imageAltText . '" style="max-width:100%;" />';
				$itemDescription .= $mediaEmbed;
				break;

			// Preview images - add webfeedsFeaturedVisual class
			case !empty($item["data"]["preview"]["images"]) && strpos($item["data"]["preview"]["images"][0]["source"]["url"],"redditmedia") !== false:
				$previewUrl = str_replace("&amp;", "&", $item["data"]["preview"]["images"][0]["source"]["url"]);
				$mediaEmbed = '<img src="' . $previewUrl . '" class="webfeedsFeaturedVisual" alt="' . htmlspecialchars($item["data"]["title"]) . '" style="max-width:100%; width:auto; display:block; margin:0 auto; margin-bottom:10px;" />';
				$itemDescription .= $mediaEmbed;
			break;

			// Image in URL - add webfeedsFeaturedVisual class
			case strpos($itemDataUrl, "jpg") !== false || strpos($itemDataUrl, "jpeg") !== false || strpos($itemDataUrl, "png") !== false || strpos($itemDataUrl, "gif") !== false:
				$isGif = strpos($itemDataUrl, "gif") !== false;
				$gifClass = $isGif ? " webfeedsGif" : "";
				$itemDescription .= '<img src="' . $itemDataUrl . '" class="webfeedsFeaturedVisual' . $gifClass . '" alt="' . htmlspecialchars($item["data"]["title"]) . '" style="max-width:100%; width:auto; display:block; margin:0 auto; margin-bottom:10px;" />';
			break;

			// Imgur - add webfeedsFeaturedVisual class
			case strpos($itemDataUrl, "imgur.com") !== false:
				$imgurUrl = $itemDataUrl;
				if(!strpos($imgurUrl, ".jpg") && !strpos($imgurUrl, ".png") && !strpos($imgurUrl, ".gif")) {
					$imgurUrl .= ".jpg";
				}
				$itemDescription .= '<img src="' . $imgurUrl . '" class="webfeedsFeaturedVisual" alt="' . htmlspecialchars($item["data"]["title"]) . '" style="max-width:100%; width:auto; display:block; margin:0 auto; margin-bottom:10px;" />';
			break;

			// Livememe
			case strpos($itemDataUrl, "livememe") !== false:
				$imageUrl = str_replace(["http://www.livememe.com/", "https://www.livememe.com/"], "https://i.lvme.me/", $itemDataUrl);
				$itemDescription .= "<p><img src='" . $imageUrl . ".jpg' /></p>";
			break;

			// Mercury-parsed lead image
			case MERCURY_URL && strpos($item["data"]["domain"], "self.") == false:
				$mercuryJSON = getFile($itemDataUrl, "mercuryJSON", "cache/mercury/" . filter_var($itemDataUrl, FILTER_SANITIZE_ENCODED) . ".json", 60 * 60 * 24 * 7, $accessToken);
				if(!isset(json_decode($mercuryJSON)->message) || json_decode($mercuryJSON)->message != "Internal server error") {
					$mercuryJSON = json_decode($mercuryJSON);
					if ($mercuryJSON->lead_image_url) {
						$itemDescription .= "<p><img src='" . $mercuryJSON->lead_image_url . "' /></p>";
					}
				}
			break;
		}


		// Feed text content and string replacement
		switch (true) {

			// Selftext
			case isset($item["data"]["selftext_html"]) && !empty($item["data"]["selftext_html"]):
				$selftext = $item["data"]["selftext_html"];
				$selftext = str_replace("&lt;", "<", $selftext);
				$selftext = str_replace("&gt;", ">", $selftext);
				$selftext = str_replace("&amp;quot;", '"', $selftext);
				$selftext = str_replace("&amp;#39;", "'", $selftext);
				$itemDescription .= $selftext;
			break;

			// No selftext and domain contains self
			case !isset($item["data"]["selftext_html"]) && strpos($item["data"]["domain"],"self.") !== false:
			break;

			// Mercury-parsed article content
			case MERCURY_URL && strpos($item["data"]["domain"], "self.") == false && strpos($item["data"]["url"], "redd.it") == false:
				$mercuryJSON = getFile($itemDataUrl, "mercuryJSON", "cache/mercury/" . filter_var($itemDataUrl, FILTER_SANITIZE_ENCODED) . ".json", 60 * 60 * 24 * 7, $accessToken);
				if(!isset(json_decode($mercuryJSON)->message) || json_decode($mercuryJSON)->message != "Internal server error") {
					if ($mercuryJSON = json_decode($mercuryJSON)) {
						// Add content with intelligent truncation at 7000 words
						$content = $mercuryJSON->content;
						if(isset($mercuryJSON->word_count) && $mercuryJSON->word_count > 7000) {
							// Truncate content at approximately 7000 words
							$words = str_word_count(strip_tags($content), 2);
							if(count($words) > 7000) {
								$positions = array_keys($words);
								$truncatePos = $positions[6999];
								$content = substr($content, 0, $truncatePos);
								// Close any open tags
								$content = force_balance_tags($content);
								$content .= "<p><a href='" . $itemDataUrl . "'>Read more...</a></p>";
							}
						}
						$itemDescription .= $content;
					}
				}
			break;
			
			// Default fallback for text posts with no content
			default:
				// Add post title as fallback content for text-only posts
				if(empty($itemDescription) || $itemDescription == "<p><a href='https://www.reddit.com" . $item["data"]["permalink"] . "'>Post permalink</a> </p>") {
					$itemDescription .= "<p>" . htmlspecialchars($item["data"]["title"]) . "</p>";
				}
			break;
		}


		// Add article's best comments (max 3)
		if(isset($_GET["comments"]) && $_GET["comments"] > 0) {
			// Cap comments at 3 maximum
			$requestedComments = min(intval($_GET["comments"]), 3);
			$commentsURL = "https://oauth.reddit.com/r/" . $item["data"]["subreddit"] . "/comments/" . $item["data"]["id"] . ".json?depth=1&showmore=0&limit=" . ($requestedComments + 1);
			$commentsJSON = getFile($commentsURL, "redditJSON", "cache/reddit/" . $item["data"]["subreddit"] . "-comments-" . $item["data"]["id"] . $_GET["comments"] . ".json", 60 * 5, $accessToken);
			$commentsJSONParsed = json_decode($commentsJSON, true);
			$comments = $commentsJSONParsed[1]["data"]["children"];
			if($comments[0]["data"]["author"] == "AutoModerator") {
				unset($comments[0]);
				$comments = array_values($comments);
			}
			// Limit to requested comments or 3, whichever is smaller
			$requestedComments = min(intval($_GET["comments"]), 3);
			if(count($comments) > $requestedComments) {
				$commentCount = $requestedComments;
			} else {
				$commentCount = count($comments);
			}
			if($commentCount) {
				$itemDescription .= "<p>&nbsp;</p><hr><p>&nbsp;</p>";
				if($commentCount == 1) {
					$itemDescription .= "<p>Best comment</p>";
				} elseif($commentCount > 1) {
					$itemDescription .= "<p>Best comments</p>";
				}
				$itemDescription .= "<ol>";
				for ($i = 0; $i < $commentCount; $i++) {
					$itemDescription .= "<li>" . htmlspecialchars_decode($comments[$i]["data"]["body_html"]) . "<ul><li><a href='https://www.reddit.com" . $comments[$i]["data"]["permalink"] . "'><small>Permalink</small></a> | <a href='https://www.reddit.com/user/" . $comments[$i]["data"]["author"] . "'><small>Author</small></a></li></ul></li>";
				}
				$itemDescription .= "</ol>";
			}
		}


		// Add hidden thumbnail as fallback if we have one and it's not already in content
		// This ensures RSS readers always have an image to display
		if(isset($item["data"]["thumbnail"]) && 
		   !empty($item["data"]["thumbnail"]) && 
		   $item["data"]["thumbnail"] != "self" && 
		   $item["data"]["thumbnail"] != "default" &&
		   $item["data"]["thumbnail"] != "nsfw" &&
		   $item["data"]["thumbnail"] != "spoiler" &&
		   strpos($itemDescription, 'webfeedsFeaturedVisual') === false) {
			// Add hidden thumbnail with webfeedsFeaturedVisual class as fallback
			$itemDescription .= '<img src="' . $item["data"]["thumbnail"] . '" class="webfeedsFeaturedVisual" style="display:none;" alt="' . htmlspecialchars($item["data"]["title"]) . '" />';
		}
		
		// CRITICAL FIX: Create both description and content:encoded fields like the Go version
		// This is required for maximum RSS reader compatibility, especially Readwise
		
		// Create "description" node - Go version HTML-encodes this field
		$descriptionNode = $itemNode->appendChild($xml->createElement("description"));
		// Use the same HTML encoding approach as Go (HTML entities, not CDATA)
		$descriptionNode->appendChild($xml->createTextNode(str_replace(['<', '>', '"'], ['&lt;', '&gt;', '&quot;'], $itemDescription)));
		
		// Create "content:encoded" node with CDATA content
		// This mirrors the Go implementation which sets both fields
		$contentEncodedNode = $itemNode->appendChild($xml->createElement("content:encoded"));
		$contentEncodedContents = $xml->createCDATASection($itemDescription);
		$contentEncodedNode->appendChild($contentEncodedContents);


		//Published date
		$pubDate = $xml->createElement("pubDate", date("r", $item["data"]["created_utc"]));
		$pubDateNode = $itemNode->appendChild($pubDate);


	}


	// Prevent CPU from spiking
	usleep(0.1 * 1000000);
}


// Cache RSS feed
if(CACHE_RSS_FEEDS) {
	file_put_contents("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml", $xml->saveXML());
}


// Echo the feed
echo $xml->saveXML();
?>