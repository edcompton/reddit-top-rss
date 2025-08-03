# Implementation Blueprint for Reddit RSS Enhancement

## Phase 1: Mercury Parser Deployment on Railway

### Step 1.1: Research Mercury Deployment
```
Research the original johnwarne/reddit-top-rss GitHub documentation for Mercury setup instructions. Find the recommended Mercury parser repository (HenryQW/mercury-parser-api) and review its deployment requirements.
```

### Step 1.2: Deploy Mercury to Railway
```
Deploy the Mercury parser service to Railway:
1. Fork or import HenryQW/mercury-parser-api repository
2. Create new Railway project for Mercury service
3. Configure environment variables (PORT=3000, API_KEY=generated_key)
4. Deploy and obtain the service URL
5. Test the Mercury endpoint with a sample article URL
```

### Step 1.3: Configure Reddit RSS with Mercury
```
Update the Reddit RSS application with Mercury configuration:
1. Add MERCURY_URL environment variable with Railway URL
2. Add MERCURY_API_KEY with the generated key
3. Verify Mercury integration is working by checking if article content is being fetched
4. Test with a Reddit post that links to an external article
```

## Phase 2: Fix Basic Image Post Rendering

### Step 2.1: Enhance i.redd.it Image Handling
```
Improve the i.redd.it image post handling in rss.php:
1. Locate the i.redd.it case in the media switch statement
2. Add post metadata (upvotes) to the description
3. Ensure image has proper alt text using post title
4. Add structured HTML that Readwise can parse
5. Test with "Dom is a dad!" post specifically
```

### Step 2.2: Add Fallback Content for Image Posts
```
Ensure all image posts have sufficient content:
1. Modify the default case in content switch statement
2. Always include post title in description for image posts
3. Add upvote count as metadata
4. Verify no posts have empty descriptions
```

## Phase 3: Enhance Video Post Support

### Step 3.1: Improve Reddit Video Embedding
```
Update v.redd.it video handling:
1. Locate the v.redd.it case in rss.php
2. Keep existing iframe embed
3. Add explicit fallback link to Reddit post for audio
4. Include video thumbnail as preview image
5. Add post metadata (title, upvotes)
```

### Step 3.2: Test Video Playback
```
Verify video posts work correctly:
1. Find a v.redd.it video post in feed
2. Check iframe renders in Readwise
3. Verify fallback link is accessible
4. Confirm thumbnail displays
```

## Phase 4: Implement Post Metadata

### Step 4.1: Add Upvote Display
```
Add upvote count to all posts:
1. Access $item["data"]["score"] for each post
2. Add upvote count after post permalink
3. Format as clean text (e.g., "500 upvotes")
4. Apply to all post types consistently
```

### Step 4.2: Limit and Enhance Comments
```
Optimize comment display:
1. Locate comment fetching logic (around line 279 in rss.php)
2. Ensure $_GET["comments"] is capped at 3 maximum
3. Verify AutoModerator comments are filtered
4. Test with posts that have many comments
```

## Phase 5: Content Processing Improvements

### Step 5.1: Implement Smart Truncation
```
Add intelligent content truncation for long articles:
1. After Mercury content is retrieved, check word count
2. Implement word counting function
3. Truncate at 7,000 words if exceeded
4. Add "Read more" link to original article
5. Preserve HTML structure when truncating
```

### Step 5.2: Optimize Gallery Display
```
Ensure all gallery images display:
1. Review Reddit gallery handling (lines 193-206)
2. Verify all images are included (no limiting)
3. Ensure proper image URLs are used (full resolution)
4. Test with a post containing many images
```

## Phase 6: Testing and Validation

### Step 6.1: Comprehensive Post Type Testing
```
Test each post type systematically:
1. Create test list of different post types
2. Test simple image post (i.redd.it)
3. Test video post (v.redd.it)
4. Test gallery post
5. Test external article with Mercury
6. Test text-only self post
7. Document any issues found
```

### Step 6.2: Readwise Integration Testing
```
Verify Readwise compatibility:
1. Add enhanced feed to Readwise
2. Check each post type renders correctly
3. Verify images display in full
4. Test video fallbacks work
5. Confirm article content is readable
6. Check comment formatting
```

## Phase 7: Error Handling and Edge Cases

### Step 7.1: Implement Graceful Fallbacks
```
Add robust error handling:
1. Wrap Mercury calls in proper error handling
2. Ensure failed image loads don't break posts
3. Add fallback for failed video embeds
4. Test with intentionally broken content
5. Verify posts never disappear due to errors
```

### Step 7.2: Validate Feed Structure
```
Ensure RSS feed remains valid:
1. Validate XML structure with online validator
2. Check all required RSS elements present
3. Verify CDATA sections properly formed
4. Test with multiple RSS readers
5. Fix any validation errors found
```

## Code Generation Prompts

### Prompt 1: Mercury Deployment
```
Deploy HenryQW/mercury-parser-api to Railway with these specifications:
1. Import the repository to Railway
2. Set PORT environment variable to 3000
3. Generate a secure API_KEY
4. Configure the service to be publicly accessible
5. Document the deployment URL and API key for use in the Reddit RSS app
```

### Prompt 2: Fix Image Post Rendering
```
In rss.php, enhance the i.redd.it image handling (around line 209-212):
1. Modify the image embed to include alt text from post title
2. Add the post score (upvotes) before the image
3. Ensure the description has sufficient content for Readwise
4. Test specifically with image posts that were previously failing
```

### Prompt 3: Add Post Metadata
```
Modify rss.php to include post metadata in all descriptions:
1. Add upvote count after the post permalink for all posts
2. Format as: "<p>Score: [number] upvotes</p>"
3. Ensure this appears consistently across all post types
4. Keep formatting minimal and clean
```

### Prompt 4: Enhance Video Support
```
Update the v.redd.it case in rss.php (around line 186-190):
1. Keep the existing iframe embed
2. Add a paragraph after the iframe with text: "If video doesn't play, <a href='[reddit_url]'>view on Reddit with audio</a>"
3. Ensure thumbnail image is included
4. Add post metadata (score)
```

### Prompt 5: Implement Content Truncation
```
Add intelligent truncation for Mercury-parsed content:
1. Create a word count function for HTML content
2. After Mercury content is added, check if it exceeds 7,000 words
3. If it does, truncate intelligently at word boundary
4. Add "Read more" link to original article URL
5. Preserve HTML structure and close any open tags
```

### Prompt 6: Limit Comment Count
```
Modify the comment fetching logic in rss.php:
1. Ensure $_GET["comments"] never exceeds 3
2. Change the default to 2 if not specified
3. Keep the AutoModerator filtering
4. Verify comment formatting is clean and readable
```

### Prompt 7: Test and Validate
```
Create a testing script that:
1. Fetches the RSS feed with various parameters
2. Validates each post has required elements
3. Checks that descriptions are not empty
4. Verifies images have proper HTML structure
5. Reports any posts that might fail in Readwise
```

### Prompt 8: Final Integration
```
Wire everything together:
1. Ensure Mercury URL and API key are properly configured
2. Verify all image posts render with content
3. Confirm video posts have working fallbacks
4. Test gallery posts show all images
5. Validate the feed with Readwise Reader
6. Document any remaining issues
```