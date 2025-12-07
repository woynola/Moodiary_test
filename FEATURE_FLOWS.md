# Moodiary Feature Flows

## 1. Journal Creation Flow

### User Journey
```
Dashboard → Click "New Journal" → Create Journal Page
    ↓
Fill in:
  - Title
  - Content (rich text editor)
  - Notebook (dropdown)
  - Entry Date (date picker)
  - Mood Score (1-10 slider)
  - Weather (optional)
  - Privacy (toggle)
    ↓
Upload Media (optional):
  - Click "Add Media"
  - Select image/video/audio
  - Upload to storage
    ↓
Click "Save" → Validation
    ↓
Success:
  - Create Journal record
  - Store media files
  - Award 50 points
  - Log activity
  - Redirect to journal view
```

### Database Operations
1. Create `journals` record
2. Create `journal_media` records (if media uploaded)
3. Create `activity_logs` record
4. Update `user_levels` points
5. Invalidate cache

### API Endpoint
```
POST /api/journals
{
  "title": "My Day",
  "content": "<p>Today was great...</p>",
  "notebook_id": 1,
  "entry_date": "2024-01-01",
  "mood_score": 8,
  "weather": "sunny"
}
```

---

## 2. Mood Tracking Flow

### User Journey
```
Dashboard → Click "Log Mood" → Mood Logger
    ↓
Select Mood:
  - Click emoji (happy, sad, angry, etc.)
  - Adjust intensity slider (1-10)
  - Add optional note
    ↓
Add Triggers (optional):
  - Click "Add Trigger"
  - Select category (work, relationship, weather, etc.)
  - Enter trigger description
  - Add multiple triggers
    ↓
Click "Save" → Validation
    ↓
Success:
  - Create Mood record
  - Create MoodTrigger records
  - Award 25 points
  - Generate daily insight (if first mood of day)
  - Show confirmation
```

### Insight Generation
```
After mood logged:
  ↓
Check if daily insight exists for today
  ↓
If not:
  - Collect all moods from today
  - Calculate mood distribution
  - Identify top triggers
  - Generate insight text
  - Create MoodInsight record
  ↓
Cache insight for quick retrieval
```

### Database Operations
1. Create `moods` record
2. Create `mood_triggers` records
3. Create `mood_insights` record (if needed)
4. Update `user_levels` points
5. Create `activity_logs` record

### API Endpoint
```
POST /api/moods
{
  "emoji": "happy",
  "intensity": 8,
  "note": "Great day at work",
  "triggers": [
    {
      "category": "work",
      "trigger": "Completed project"
    },
    {
      "category": "social",
      "trigger": "Lunch with friends"
    }
  ]
}
```

---

## 3. Challenge Participation Flow

### Browse & Join
```
Dashboard → Click "Challenges" → Challenge List
    ↓
View available challenges:
  - Challenge name, icon, description
  - Duration (30 days, etc.)
  - Difficulty
  - Participants count
    ↓
Click "Join Challenge" → Confirmation
    ↓
Success:
  - Create UserChallenge record
  - Generate daily checkpoints (30 records)
  - Show challenge details page
```

### Daily Completion
```
Challenge Page → View Today's Checkpoint
    ↓
Click "Complete Today" → Confirmation
    ↓
Success:
  - Mark checkpoint as completed
  - Update current_streak
  - Update longest_streak
  - Increment total_completed_days
  - Award 10 points
    ↓
Check for completion:
  - If total_completed_days == duration_days:
    - Mark challenge as completed
    - Award 500 + (streak × 10) points
    - Unlock badge (if applicable)
    - Update user level
```

### Streak System
```
Checkpoint completed:
  ↓
Check previous day checkpoint:
  - If completed: streak += 1
  - If not completed: streak = 1
  - If gap > 1 day: streak = 1
    ↓
Update longest_streak if current > longest
```

### Database Operations
1. Create `user_challenges` record
2. Create 30 `challenge_checkpoints` records
3. Update checkpoint `is_completed` and `completed_at`
4. Update `user_challenges` streak/progress
5. Create `activity_logs` record
6. Award points and check badges

### API Endpoint
```
POST /api/challenges/{id}/join
Response: UserChallenge created

POST /api/user-challenges/{id}/checkpoint
{
  "date": "2024-01-01"
}
Response: Checkpoint marked complete
```

---

## 4. Forum Posting Flow

### Create Post
```
Forum → Click "New Post" → Post Editor
    ↓
Fill in:
  - Title
  - Content (rich text)
  - Category (dropdown)
  - Anonymous toggle
    ↓
Upload Media (optional):
  - Add images/videos
  - Store in forum directory
    ↓
Click "Post" → Validation
    ↓
Success:
  - Create ForumPost record
  - Create ForumPostMedia records
  - Award 75 points
  - Queue notification to category followers
  - Redirect to post view
```

### Comment & React
```
View Post → Scroll to Comments
    ↓
Add Comment:
  - Type comment text
  - Optional: Reply to specific comment
  - Optional: Post anonymously
  - Click "Post Comment"
    ↓
Success:
  - Create ForumComment record
  - Increment post replies_count
  - Update post last_reply_at
  - Award 25 points
  - Notify post author
    ↓
React to Post/Comment:
  - Click reaction button (Like, Support, Hug)
  - If already reacted: toggle off
  - If new reaction: create ForumReaction
    ↓
Update reaction counts in real-time
```

### Moderation
```
User Reports Content:
  ↓
Click "Report" → Report Modal
  ↓
Select reason:
  - Spam
  - Harassment
  - Inappropriate
  - Other
    ↓
Add description (optional)
  ↓
Click "Submit" → Create ForumReport
  ↓
Notify moderators
  ↓
Moderator Reviews:
  - View report details
  - View reported content
  - Choose action:
    - Dismiss
    - Reviewed (no action)
    - Resolved (delete content)
  ↓
If Resolved:
  - Delete ForumPost/ForumComment
  - Log moderation action
  - Notify reporter
```

### Database Operations
1. Create `forum_posts` record
2. Create `forum_post_media` records
3. Create `forum_comments` record
4. Create `forum_reactions` record
5. Create `forum_reports` record
6. Update `forum_posts` stats
7. Create `activity_logs` record

### API Endpoints
```
POST /api/forum/posts
{
  "title": "Need advice",
  "content": "...",
  "category_id": 1,
  "is_anonymous": false
}

POST /api/forum/posts/{id}/comment
{
  "content": "Great post!",
  "parent_id": null,
  "is_anonymous": false
}

POST /api/forum/posts/{id}/react
{
  "type": "like"
}

POST /api/forum/{post}/report
{
  "reason": "spam",
  "description": "..."
}
```

---

## 5. Gamification Flow

### Points System
```
User Action → Award Points:
  - Journal created: 50 points
  - Mood logged: 25 points
  - Forum post: 75 points
  - Forum comment: 25 points
  - Challenge checkpoint: 10 points
  - Challenge completed: 500 + (streak × 10) points
    ↓
Add to user.points
  ↓
Add to user_levels.total_points
  ↓
Check for level up:
  - If total_points >= points_to_next_level:
    - Increment current_level
    - Subtract points_to_next_level from total_points
    - Calculate new points_to_next_level
    - Award level-up badge
    - Notify user
```

### Badge System
```
User earns points/completes action:
  ↓
Check all active badges:
  - First Journal (1 journal created)
  - Journal Streak 7 (7 consecutive days)
  - Mood Tracker Master (30 moods logged)
  - Forum Contributor (5+ posts)
  - Challenge Completer (1+ completed)
  - Level 10 (reach level 10)
    ↓
For each badge:
  - Check unlock condition
  - If unlocked and user doesn't have it:
    - Create UserBadge record
    - Notify user
    - Show badge unlock animation
```

### Leaderboard
```
Leaderboard Page:
  ↓
Query top 50 users by points:
  - Cache for 1 hour
  - Include user rank
  - Show user avatar, name, level, points
    ↓
User's Rank:
  - Count users with more points
  - Add 1 to get rank
  - Show position on leaderboard
```

### Database Operations
1. Create `activity_logs` record
2. Update `user_levels` points
3. Check and create `user_badges` records
4. Update `users` level and points
5. Cache leaderboard data

---

## 6. Notification & Reminder Flow

### Reminder System
```
User Settings → Reminders
    ↓
Create Reminder:
  - Type: journal, mood, challenge
  - Title & message
  - Scheduled time (e.g., 9:00 AM)
  - Frequency: daily, weekly, custom
  - Days of week (if weekly)
    ↓
Save to database
    ↓
Scheduled Job (every minute):
  - Check reminders due
  - Create Notification record
  - Send to user (in-app, email, push)
  - Update last_sent_at
```

### Notification Types
```
1. Reminder Notifications
   - Journal reminder
   - Mood check-in
   - Challenge reminder

2. Activity Notifications
   - Forum post reply
   - Comment reaction
   - Challenge completion
   - Badge unlocked
   - Level up

3. Social Notifications
   - Forum post liked
   - Comment support/hug
   - New follower
```

### Database Operations
1. Create `reminders` record
2. Create `notifications` record
3. Update `notifications.is_read` when viewed
4. Queue job for scheduled reminders

---

## 7. Admin Panel Flow

### User Management
```
Admin Panel → Users
    ↓
View all users:
  - Search by name/email
  - Filter by status (active, inactive)
  - Sort by join date, points, level
    ↓
User Actions:
  - View profile
  - Deactivate/activate
  - Make moderator
  - View activity logs
```

### Challenge Management
```
Admin Panel → Challenges
    ↓
View templates:
  - List all challenge templates
  - Edit challenge details
  - Manage rewards
  - Activate/deactivate
    ↓
Create Challenge:
  - Fill challenge form
  - Set as template
  - Add milestone rewards
  - Save to database
```

### Moderation Dashboard
```
Admin Panel → Moderation
    ↓
View statistics:
  - Pending reports count
  - Reviewed reports count
  - Resolved reports count
  - Top reporters
  - Most reported content
    ↓
Review Reports:
  - View pending reports
  - Click to view content
  - Choose action:
    - Dismiss
    - Reviewed
    - Resolved (delete)
  - Add review note
  - Save action
```

### Analytics
```
Admin Panel → Analytics
    ↓
View charts:
  - User growth (30 days)
  - Journal creation trend
  - Forum activity
  - Challenge participation
  - Mood distribution
    ↓
Export data:
  - CSV export
  - Date range selection
```

---

## 8. Search & Discovery Flow

### Journal Search
```
Dashboard → Search
    ↓
Enter search term:
  - Searches title and content
  - Uses MySQL FULLTEXT index
  - Returns matching journals
    ↓
Filter results:
  - By date range
  - By mood score
  - By notebook
    ↓
Click journal → View full entry
```

### Forum Search
```
Forum → Search
    ↓
Enter search term:
  - Searches post titles and content
  - Uses MySQL FULLTEXT index
  - Returns matching posts
    ↓
Filter results:
  - By category
  - By date
  - By author
    ↓
Click post → View thread
```

---

## 9. Privacy & Security Flow

### Journal PIN Protection
```
Journal View → Click "Lock"
    ↓
Enter 4-digit PIN:
  - Hash with SHA256
  - Store in database
    ↓
Next access to journal:
  - Prompt for PIN
  - Verify against stored hash
  - Show journal if correct
  - Deny if incorrect
```

### Anonymous Forum Posting
```
Create Forum Post:
  - Toggle "Post Anonymously"
  - Post created with is_anonymous = true
  - Author name shown as "Anonymous"
  - User_id still stored (for moderation)
  - Comments can also be anonymous
```

### Data Privacy
```
User Settings → Privacy
    ↓
Options:
  - Make profile private
  - Hide from leaderboard
  - Disable notifications
  - Delete account (soft delete)
```

---

## 10. Export & Backup Flow

### Journal Export
```
Journal View → Click "Export"
    ↓
Choose format:
  - PDF
  - Markdown
  - Plain text
    ↓
Generate file:
  - Include title, date, content
  - Include media (in PDF)
  - Download to device
```

### Data Export
```
Settings → Export Data
    ↓
Select data to export:
  - All journals
  - All moods
  - All forum posts
  - Activity history
    ↓
Generate ZIP file:
  - JSON format
  - Include metadata
  - Download to device
```
