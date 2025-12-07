# Moodiary API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All API endpoints require Bearer token authentication via `Authorization` header:
```
Authorization: Bearer {token}
```

---

## Journal Endpoints

### List Journals
```
GET /api/journals
```
**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "My First Journal",
      "content": "...",
      "entry_date": "2024-01-01",
      "is_private": false,
      "mood_score": 8,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ],
  "pagination": {...}
}
```

### Create Journal
```
POST /api/journals
Content-Type: application/json

{
  "title": "My Journal Entry",
  "content": "<p>Journal content here</p>",
  "notebook_id": 1,
  "entry_date": "2024-01-01",
  "is_private": false,
  "mood_score": 8,
  "weather": "sunny"
}
```

### Get Journal
```
GET /api/journals/{id}
```

### Update Journal
```
PUT /api/journals/{id}
Content-Type: application/json

{
  "title": "Updated Title",
  "content": "Updated content",
  "notebook_id": 1,
  "entry_date": "2024-01-01"
}
```

### Delete Journal
```
DELETE /api/journals/{id}
```

### Upload Media
```
POST /api/journals/{id}/upload-media
Content-Type: multipart/form-data

{
  "file": <binary>,
  "type": "image|video|audio"
}
```

### Search Journals
```
GET /api/journals/search?q=search+term
```

### Calendar
```
GET /api/journals/calendar/{year}/{month}
```

---

## Mood Endpoints

### List Moods
```
GET /api/moods
```

### Create Mood
```
POST /api/moods
Content-Type: application/json

{
  "emoji": "happy",
  "intensity": 8,
  "note": "Had a great day",
  "triggers": [
    {
      "category": "work",
      "trigger": "Completed project"
    }
  ]
}
```

### Get Mood
```
GET /api/moods/{id}
```

### Update Mood
```
PUT /api/moods/{id}
```

### Delete Mood
```
DELETE /api/moods/{id}
```

### Weekly Stats
```
GET /api/moods/stats/weekly
```
**Response:**
```json
{
  "distribution": {
    "happy": 4,
    "neutral": 2,
    "sad": 1
  },
  "avgIntensity": 7.5,
  "totalMoods": 7
}
```

### Monthly Stats
```
GET /api/moods/stats/monthly
```

### Insights
```
GET /api/moods/insights
```
**Response:**
```json
{
  "daily": {...},
  "weekly": {...},
  "monthly": {...}
}
```

### Mood Trend (30 days)
```
GET /api/moods/trend
```

---

## Challenge Endpoints

### List Challenges
```
GET /api/challenges
```

### Get Challenge
```
GET /api/challenges/{id}
```

### Join Challenge
```
POST /api/challenges/{id}/join
```

### My Challenges
```
GET /api/my-challenges
```

### Complete Checkpoint
```
POST /api/user-challenges/{id}/checkpoint
Content-Type: application/json

{
  "date": "2024-01-01"
}
```

### Abandon Challenge
```
POST /api/user-challenges/{id}/abandon
```

---

## Forum Endpoints

### List Posts
```
GET /api/forum/posts
```

### Create Post
```
POST /api/forum/posts
Content-Type: application/json

{
  "title": "Post Title",
  "content": "Post content",
  "category_id": 1,
  "is_anonymous": false
}
```

### Get Post
```
GET /api/forum/posts/{id}
```

### Update Post
```
PUT /api/forum/posts/{id}
```

### Delete Post
```
DELETE /api/forum/posts/{id}
```

### Post Comment
```
POST /api/forum/posts/{id}/comment
Content-Type: application/json

{
  "content": "Comment text",
  "parent_id": null,
  "is_anonymous": false
}
```

### React to Post
```
POST /api/forum/posts/{id}/react
Content-Type: application/json

{
  "type": "like|support|hug"
}
```

### React to Comment
```
POST /api/forum/comments/{id}/react
Content-Type: application/json

{
  "type": "like|support|hug"
}
```

### Report Content
```
POST /api/forum/{reportable}/report
Content-Type: application/json

{
  "reason": "spam|harassment|inappropriate",
  "description": "Detailed reason"
}
```

### Get Categories
```
GET /api/forum/categories
```

---

## User Endpoints

### Get Profile
```
GET /api/user/profile
```

### Update Profile
```
PATCH /api/user/profile
Content-Type: application/json

{
  "name": "New Name",
  "email": "new@email.com",
  "username": "newusername",
  "bio": "My bio",
  "theme": "light|dark"
}
```

### Get Badges
```
GET /api/user/badges
```

### Get Activity
```
GET /api/user/activity
```

### Get Leaderboard
```
GET /api/user/leaderboard
```

### Get User Rank
```
GET /api/user/rank
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized access"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "Validation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

### 500 Server Error
```json
{
  "message": "Internal server error"
}
```

---

## Rate Limiting
API requests are rate-limited to 60 requests per minute per user.

## Pagination
List endpoints support pagination with query parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Example:
```
GET /api/journals?page=2&per_page=20
```
