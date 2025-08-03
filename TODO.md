# Reddit RSS Enhancement TODO List

## Phase 1: Mercury Parser Setup
- [ ] Research Mercury setup in johnwarne/reddit-top-rss documentation
- [ ] Visit HenryQW/mercury-parser-api GitHub repository
- [ ] Fork/import mercury-parser-api to your GitHub account
- [ ] Create new Railway project for Mercury service
- [ ] Deploy Mercury to Railway
- [ ] Set PORT environment variable to 3000
- [ ] Generate secure API key
- [ ] Note down Mercury service URL
- [ ] Test Mercury endpoint with curl/browser
- [ ] Add MERCURY_URL to Reddit RSS environment variables
- [ ] Add MERCURY_API_KEY to Reddit RSS environment variables
- [ ] Test Mercury integration with an article link
- [ ] Verify Mercury responses are being cached

## Phase 2: Fix Image Post Rendering
- [ ] Open rss.php in editor
- [ ] Locate i.redd.it handling (line ~209-212)
- [ ] Add alt text to img tags using post title
- [ ] Add post score/upvotes to description
- [ ] Ensure description has meaningful content
- [ ] Test with "Dom is a dad!" post specifically
- [ ] Verify image posts work in Readwise
- [ ] Check other i.redd.it posts for consistency

## Phase 3: Enhance Video Support
- [ ] Locate v.redd.it handling in rss.php (line ~186-190)
- [ ] Keep existing iframe embed code
- [ ] Add fallback link for audio playback
- [ ] Format fallback text clearly
- [ ] Ensure thumbnail is included
- [ ] Add post metadata (upvotes)
- [ ] Test with multiple video posts
- [ ] Verify fallback links work

## Phase 4: Add Post Metadata
- [ ] Add upvote display to all post types
- [ ] Access $item["data"]["score"] variable
- [ ] Format as "Score: X upvotes"
- [ ] Place after post permalink
- [ ] Test across different post types
- [ ] Ensure consistent formatting
- [ ] Verify no duplicate score displays

## Phase 5: Optimize Comments
- [ ] Locate comment fetching section (line ~279)
- [ ] Cap $_GET["comments"] at maximum 3
- [ ] Set default to 2 comments if not specified
- [ ] Verify AutoModerator filtering works
- [ ] Test with posts having many comments
- [ ] Check comment formatting is clean
- [ ] Ensure comment permalinks work

## Phase 6: Content Processing
- [ ] Implement word counting function
- [ ] Add truncation for Mercury content
- [ ] Set limit at 7,000 words
- [ ] Add "Read more" link for truncated content
- [ ] Preserve HTML structure when truncating
- [ ] Test with very long articles
- [ ] Verify truncation doesn't break formatting

## Phase 7: Gallery Enhancement
- [ ] Review gallery handling code (line ~193-206)
- [ ] Ensure ALL images are included
- [ ] Verify no artificial limits on image count
- [ ] Check image URLs are full resolution
- [ ] Test with galleries containing 10+ images
- [ ] Confirm sequential display in Readwise

## Phase 8: Error Handling
- [ ] Add try-catch around Mercury calls
- [ ] Implement fallback for failed Mercury parsing
- [ ] Handle missing image URLs gracefully
- [ ] Add fallback for broken video embeds
- [ ] Ensure posts never disappear due to errors
- [ ] Test with intentionally broken URLs
- [ ] Verify error messages are user-friendly

## Phase 9: Testing
- [ ] Create list of test posts:
  - [ ] Simple i.redd.it image post
  - [ ] v.redd.it video post
  - [ ] Reddit gallery with multiple images
  - [ ] External article link
  - [ ] Text-only self post
  - [ ] Post with many comments
  - [ ] Post with embedded media
- [ ] Test each post type in RSS feed
- [ ] Add feed to Readwise Reader
- [ ] Verify all posts render correctly
- [ ] Check images display in full
- [ ] Test video playback/fallbacks
- [ ] Confirm article content readable
- [ ] Validate comment formatting

## Phase 10: Validation
- [ ] Validate RSS XML structure
- [ ] Check for well-formed XML
- [ ] Verify all required RSS elements
- [ ] Test CDATA sections
- [ ] Validate with W3C Feed Validator
- [ ] Test with alternative RSS readers
- [ ] Fix any validation errors

## Phase 11: Performance Check
- [ ] Verify caching is working:
  - [ ] Reddit cache (5 minutes)
  - [ ] Mercury cache (7 days)
  - [ ] RSS feed cache (1 hour)
- [ ] Test feed generation speed
- [ ] Check for any timeout issues
- [ ] Monitor Railway usage for Mercury

## Phase 12: Documentation
- [ ] Update README with Mercury setup instructions
- [ ] Document new environment variables
- [ ] Add Readwise configuration tips
- [ ] Create troubleshooting section
- [ ] Note any limitations or known issues
- [ ] Add example feed URLs

## Phase 13: Final Verification
- [ ] All image posts render in Readwise
- [ ] Video posts have working fallbacks
- [ ] Articles extract via Mercury
- [ ] Comments display correctly (max 3)
- [ ] Upvote counts show for all posts
- [ ] No empty descriptions
- [ ] Feed validates successfully
- [ ] Performance is acceptable

## Deployment
- [ ] Commit all changes to git
- [ ] Push to GitHub repository
- [ ] Deploy to Railway/Render
- [ ] Update environment variables in production
- [ ] Test production feed
- [ ] Monitor for any errors
- [ ] Verify Readwise can read production feed

## Post-Deployment
- [ ] Monitor feed for 24 hours
- [ ] Check Railway Mercury service stability
- [ ] Verify cache is working properly
- [ ] Address any user-reported issues
- [ ] Document any additional fixes needed