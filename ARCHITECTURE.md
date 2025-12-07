# Moodiary System Architecture

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────┐  │
│  │  Web Browser     │  │  Mobile Browser  │  │  API Client  │  │
│  │  (Blade + JS)    │  │  (Responsive)    │  │  (REST)      │  │
│  └──────────────────┘  └──────────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Laravel Blade Templates + TailwindCSS + Alpine.js       │  │
│  │  - Dashboard                                              │  │
│  │  - Journal Pages                                          │  │
│  │  - Mood Tracker                                           │  │
│  │  - Challenge Pages                                        │  │
│  │  - Forum Pages                                            │  │
│  │  - Admin Panel                                            │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │ Controllers  │  │  Middleware  │  │  Request Validation  │  │
│  └──────────────┘  └──────────────┘  └──────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Services Layer                                           │  │
│  │  - GamificationService                                    │  │
│  │  - MoodInsightService                                     │  │
│  │  - ChallengeService                                       │  │
│  │  - ForumModerationService                                 │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                      DATA LAYER                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Eloquent Models & Relationships                          │  │
│  │  - User, Journal, Notebook, Mood                          │  │
│  │  - Challenge, UserChallenge, Badge                        │  │
│  │  - ForumPost, ForumComment, ForumReaction                 │  │
│  │  - Reminder, Notification, ActivityLog                   │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                   PERSISTENCE LAYER                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │   MySQL      │  │    Redis     │  │   File Storage       │  │
│  │  (Primary)   │  │  (Cache)     │  │   (S3 Ready)         │  │
│  └──────────────┘  └──────────────┘  └──────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow

### Journal Creation Flow
```
User → Create Journal Form → JournalController@store
  ↓
Validate Input → Create Journal Record → Store Media Files
  ↓
Award Gamification Points → Generate Activity Log
  ↓
Cache Invalidation → Redirect with Success
```

### Mood Tracking Flow
```
User → Log Mood → MoodController@store
  ↓
Validate Input → Create Mood Record → Store Triggers
  ↓
Award Points → Generate Daily Insight (if needed)
  ↓
Update Mood Statistics → Cache Update
```

### Challenge Flow
```
User → Browse Challenges → ChallengeController@join
  ↓
Create UserChallenge → Generate Daily Checkpoints
  ↓
User Completes Daily → ChallengeController@completeCheckpoint
  ↓
Update Streak → Check for Completion
  ↓
If Complete: Award Points + Badge → Update Level
```

### Forum Posting Flow
```
User → Create Post → ForumController@store
  ↓
Validate Input → Create ForumPost
  ↓
Award Points → Queue Notification to Followers
  ↓
Update Category Stats → Cache Invalidation
```

### Moderation Flow
```
User → Report Content → ForumController@report
  ↓
Create ForumReport (pending) → Queue Notification to Moderators
  ↓
Moderator Reviews → ModerationController@reviewReport
  ↓
If Resolved: Delete Content → Log Action
```

## Queue System

### Jobs
- `SendReminderNotification` - Send scheduled reminders
- `GenerateMoodInsight` - Generate daily/weekly/monthly insights
- `ProcessChallengeCompletion` - Award badges and points
- `SendForumNotifications` - Notify users of forum activity

### Configuration
```
QUEUE_CONNECTION=redis  # or database for fallback
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Running Queue Worker
```bash
php artisan queue:work
```

## Cache Strategy

### Cached Items
- User leaderboard (1 hour)
- Mood statistics (30 minutes)
- Challenge progress (15 minutes)
- Forum category stats (1 hour)
- User badges (1 day)

### Cache Keys
```
user.{id}.leaderboard
user.{id}.mood.stats.{period}
challenge.{id}.progress
forum.category.{id}.stats
user.{id}.badges
```

### Invalidation
- Cache cleared on data updates
- Manual cache clear via admin panel
- Scheduled cache refresh for statistics

## Authentication Flow

### Session-Based (Web)
```
Login Form → AuthenticatedSessionController
  ↓
Verify Credentials → Create Session
  ↓
Set Auth Guard → Redirect to Dashboard
```

### Google OAuth
```
User Clicks "Login with Google" → Redirect to Google
  ↓
User Authorizes → Google Redirects with Code
  ↓
Exchange Code for Token → Create/Update User
  ↓
Create Session → Redirect to Dashboard
```

### API (Token-Based)
```
POST /api/login → Return Sanctum Token
  ↓
Client Stores Token → Include in Authorization Header
  ↓
Middleware Verifies Token → Allow Request
```

## Storage Architecture

### Local Storage
```
storage/
├── app/
│   ├── journals/
│   │   └── {journal_id}/
│   │       ├── image_1.jpg
│   │       ├── video_1.mp4
│   │       └── audio_1.mp3
│   └── forum/
│       └── {post_id}/
│           └── media_files/
└── logs/
```

### S3 Configuration
```
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=moodiary-uploads
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## Security Measures

### Authentication
- Password hashing with bcrypt
- CSRF protection on all forms
- Session timeout (120 minutes)
- Rate limiting on login attempts

### Authorization
- Policy-based authorization
- Role-based access control (Admin, Moderator)
- User-scoped data access

### Data Protection
- PIN protection for private journals
- Anonymous forum posting option
- Soft deletes for data recovery
- Audit logging for admin actions

### Input Validation
- Server-side validation on all inputs
- XSS protection via Blade escaping
- SQL injection prevention via Eloquent
- File upload validation

## Performance Optimization

### Database
- Indexed columns for fast queries
- Eager loading of relationships
- Query optimization with pagination
- Full-text search on journals and posts

### Caching
- Redis for session storage
- Query result caching
- View fragment caching
- API response caching

### Frontend
- Asset minification via Vite
- Lazy loading of images
- Alpine.js for lightweight interactivity
- TailwindCSS for optimized CSS

### API
- Pagination for large datasets
- Sparse fieldsets support
- Rate limiting (60 req/min)
- Response compression

## Scalability Considerations

### Horizontal Scaling
- Stateless application design
- Redis for shared sessions/cache
- Database replication ready
- Load balancer compatible

### Vertical Scaling
- Efficient database queries
- Memory-efficient caching
- Optimized file storage
- Queue worker scaling

### Future Enhancements
- Elasticsearch for advanced search
- CDN integration for media
- Microservices for heavy processing
- Real-time features with WebSockets

## Monitoring & Logging

### Application Logs
```
storage/logs/laravel.log
```

### Error Tracking
- Stack trace logging
- User context in logs
- Request/response logging

### Performance Monitoring
- Query execution time
- Memory usage tracking
- Cache hit rates
- API response times

## Backup & Recovery

### Database Backups
```bash
php artisan backup:run
```

### File Backups
- S3 versioning enabled
- Local backup scripts
- Automated daily backups

### Recovery Procedures
- Database restore from backup
- File recovery from S3
- Transaction rollback capability
