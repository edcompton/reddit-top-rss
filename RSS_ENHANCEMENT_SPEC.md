# Reddit RSS Feed Enhancement Specification

## Overview
Enhance the Reddit RSS feed generator to provide rich, readable content in Readwise Reader, with full support for images, videos, and article content extraction via Mercury parser.

## Core Requirements

### 1. Media Content Display
- **Images**: Display all images in full, including i.redd.it hosted images
- **Videos**: Embed videos using iframes with fallback links to Reddit posts
- **Galleries**: Show all images in sequence (no truncation)
- **Articles**: Extract full article content using Mercury parser

### 2. Post Metadata
- Display post title prominently
- Include upvote count
- Add 2-3 top comments (not more)
- Exclude post author information
- Keep formatting clean and minimal (no emoji indicators or decorative elements)

### 3. Content Processing

#### For i.redd.it Image Posts
- Include the full image with proper HTML structure
- Add post title as text content
- Include upvote count
- Add top 2-3 comments if available
- Ensure proper alt text for accessibility

#### For Video Posts (v.redd.it)
- Embed video using iframe
- Include fallback link to Reddit post (for audio playback)
- Add thumbnail image as preview
- Include post metadata

#### For External Article Links
- Use Mercury parser to extract clean article content
- Implement intelligent truncation at 7,000 words
- Add "Read more" link for truncated content
- Fallback to Reddit content if Mercury fails

#### For Reddit Galleries
- Display all images in sequence
- No pagination or limiting
- Include proper image tags with alt text

### 4. Mercury Parser Integration

#### Deployment Requirements
- Deploy Mercury parser service on Railway
- Configure with API key for security
- Set up environment variables in main app
- Follow configuration from original johnwarne/reddit-top-rss documentation

#### Configuration
- `MERCURY_URL`: Railway deployment URL
- `MERCURY_API_KEY`: Generated secure API key
- `CACHE_MERCURY_CONTENT`: Keep at default (true)
- Cache duration: 7 days (default)

### 5. Error Handling

#### Graceful Degradation
- If Mercury fails: Show original Reddit content
- If image fails to load: Show title and text content
- If video embed fails: Show thumbnail and link
- Never skip posts due to parsing errors
- Always show maximum available content

#### Content Validation
- Ensure all posts have meaningful description content
- Add title as fallback for empty descriptions
- Validate HTML structure for Readwise compatibility

### 6. RSS Feed Structure

#### Required Elements
- Valid XML with UTF-8 encoding
- CDATA sections for HTML content
- Proper pubDate formatting (RFC822)
- Unique GUID for each item
- Clean, valid HTML in description field

#### Enhanced Description Field Structure
```
1. Post permalink
2. Upvote count
3. Main content (image/video/article)
4. Top 2-3 comments (if requested)
5. Fallback links where appropriate
```

### 7. Performance Optimization

#### Caching Strategy
- Reddit API: 5 minutes (default)
- Mercury content: 7 days (default)
- RSS feeds: 1 hour (default)
- No changes to current cache durations

#### Content Limits
- Article truncation: 7,000 words
- Comments: Maximum 3
- No limit on image count in galleries

## Technical Implementation Details

### File Modifications Required

#### rss.php
1. Fix i.redd.it image handling (already completed)
2. Add upvote count to description
3. Enhance video embedding with fallback
4. Improve gallery handling for all images
5. Add intelligent truncation for Mercury content
6. Limit comment count to 2-3

#### functions.php
- No modifications needed (Mercury integration working)

#### env-config.php
- Add Mercury configuration variables (if not present)

### Railway Deployment for Mercury

#### Service Setup
1. Deploy HenryQW/mercury-parser-api to Railway
2. Configure environment variables
3. Generate and secure API key
4. Test endpoint accessibility

#### Environment Variables
```
PORT=3000
API_KEY=[generated secure key]
```

### Testing Requirements

#### Functional Testing
1. Test i.redd.it image posts render in Readwise
2. Verify video posts show iframe and fallback
3. Confirm galleries display all images
4. Test Mercury article extraction
5. Verify comment limiting works
6. Test truncation at 7,000 words

#### Edge Case Testing
1. Posts with no images or media
2. Failed Mercury parsing
3. Deleted Reddit posts
4. Very long comment threads
5. Mixed media posts

#### Readwise Compatibility Testing
1. Import feed into Readwise
2. Verify all post types display correctly
3. Test media playback where supported
4. Confirm readability of long articles

## Success Criteria

1. All Reddit post types render readable content in Readwise
2. "Dom is a dad!" and similar image posts display properly
3. Mercury parser successfully extracts article content
4. No posts are skipped due to parsing errors
5. Performance remains acceptable with caching
6. Video posts provide working fallback options

## Rollback Plan

If issues arise:
1. Revert RSS.php changes
2. Disable Mercury integration (set MERCURY_URL to empty)
3. Clear all caches
4. Return to previous working version

## Documentation Updates

Update README.md with:
1. Mercury Railway deployment instructions
2. New environment variable documentation
3. Readwise-specific configuration tips
4. Troubleshooting guide for common issues