# Moodiary Database Schema

## Entity Relationship Diagram

```
┌─────────────┐
│   users     │
├─────────────┤
│ id (PK)     │
│ name        │
│ email       │
│ username    │
│ password    │
│ avatar_url  │
│ bio         │
│ level       │
│ points      │
│ is_admin    │
│ is_moderator│
│ is_active   │
└─────────────┘
      │
      ├──→ journals (1:M)
      ├──→ notebooks (1:M)
      ├──→ moods (1:M)
      ├──→ forum_posts (1:M)
      ├──→ forum_comments (1:M)
      ├──→ challenges (1:M)
      ├──→ user_badges (1:M)
      ├──→ user_levels (1:1)
      ├──→ reminders (1:M)
      ├──→ notifications (1:M)
      └──→ activity_logs (1:M)

┌──────────────┐
│  notebooks   │
├──────────────┤
│ id (PK)      │
│ user_id (FK) │
│ name         │
│ description  │
│ color        │
│ icon         │
│ order        │
└──────────────┘
      │
      └──→ journals (1:M)

┌──────────────┐
│   journals   │
├──────────────┤
│ id (PK)      │
│ user_id (FK) │
│ notebook_id  │
│ title        │
│ content      │
│ excerpt      │
│ entry_date   │
│ is_private   │
│ pin_code     │
│ mood_score   │
│ weather      │
│ reading_time │
│ views        │
│ is_published │
│ published_at │
│ created_at   │
│ updated_at   │
│ deleted_at   │
└──────────────┘
      │
      ├──→ journal_media (1:M)
      └──→ journal_tags (1:M)

┌──────────────────┐
│  journal_media   │
├──────────────────┤
│ id (PK)          │
│ journal_id (FK)  │
│ type             │
│ url              │
│ original_name    │
│ mime_type        │
│ size             │
│ order            │
└──────────────────┘

┌──────────────────┐
│  journal_tags    │
├──────────────────┤
│ id (PK)          │
│ journal_id (FK)  │
│ tag              │
└──────────────────┘

┌──────────────┐
│    moods     │
├──────────────┤
│ id (PK)      │
│ user_id (FK) │
│ emoji        │
│ intensity    │
│ note         │
│ recorded_at  │
│ created_at   │
│ updated_at   │
└──────────────┘
      │
      └──→ mood_triggers (1:M)

┌──────────────────┐
│  mood_triggers   │
├──────────────────┤
│ id (PK)          │
│ mood_id (FK)     │
│ category         │
│ trigger          │
└──────────────────┘

┌──────────────────┐
│  mood_insights   │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ period           │
│ period_date      │
│ dominant_mood    │
│ mood_distribution│
│ top_triggers     │
│ insight_text     │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────────┐
│  forum_categories    │
├──────────────────────┤
│ id (PK)              │
│ name                 │
│ slug                 │
│ description          │
│ icon                 │
│ order                │
│ is_active            │
│ created_at           │
│ updated_at           │
└──────────────────────┘
      │
      └──→ forum_posts (1:M)

┌──────────────────┐
│  forum_posts     │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ category_id (FK) │
│ title            │
│ content          │
│ excerpt          │
│ is_anonymous     │
│ is_pinned        │
│ is_locked        │
│ views            │
│ replies_count    │
│ last_reply_at    │
│ created_at       │
│ updated_at       │
│ deleted_at       │
└──────────────────┘
      │
      ├──→ forum_comments (1:M)
      ├──→ forum_post_media (1:M)
      └──→ forum_reactions (1:M)

┌──────────────────┐
│ forum_post_media │
├──────────────────┤
│ id (PK)          │
│ post_id (FK)     │
│ url              │
│ type             │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│ forum_comments   │
├──────────────────┤
│ id (PK)          │
│ post_id (FK)     │
│ user_id (FK)     │
│ parent_id (FK)   │
│ content          │
│ is_anonymous     │
│ likes_count      │
│ supports_count   │
│ hugs_count       │
│ created_at       │
│ updated_at       │
│ deleted_at       │
└──────────────────┘
      │
      ├──→ forum_reactions (1:M)
      └──→ forum_comments (1:M) [replies]

┌──────────────────┐
│ forum_reactions  │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ reactable_type   │
│ reactable_id     │
│ type             │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│ forum_reports    │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ reportable_type  │
│ reportable_id    │
│ reason           │
│ description      │
│ status           │
│ reviewed_by (FK) │
│ review_note      │
│ reviewed_at      │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│  challenges      │
├──────────────────┤
│ id (PK)          │
│ name             │
│ slug             │
│ description      │
│ icon             │
│ color            │
│ duration_days    │
│ rules            │
│ is_template      │
│ is_active        │
│ created_by (FK)  │
│ created_at       │
│ updated_at       │
└──────────────────┘
      │
      ├──→ user_challenges (1:M)
      └──→ challenge_rewards (1:M)

┌──────────────────────┐
│  user_challenges     │
├──────────────────────┤
│ id (PK)              │
│ user_id (FK)         │
│ challenge_id (FK)    │
│ started_at           │
│ completed_at         │
│ current_streak       │
│ longest_streak       │
│ total_completed_days │
│ status               │
│ created_at           │
│ updated_at           │
└──────────────────────┘
      │
      └──→ challenge_checkpoints (1:M)

┌──────────────────────┐
│challenge_checkpoints │
├──────────────────────┤
│ id (PK)              │
│ user_challenge_id(FK)│
│ checkpoint_date      │
│ is_completed         │
│ note                 │
│ completed_at         │
│ created_at           │
│ updated_at           │
└──────────────────────┘

┌──────────────────┐
│challenge_rewards │
├──────────────────┤
│ id (PK)          │
│ challenge_id (FK)│
│ day_milestone    │
│ reward_name      │
│ points_reward    │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────┐
│    badges    │
├──────────────┤
│ id (PK)      │
│ name         │
│ slug         │
│ description  │
│ icon         │
│ color        │
│ rarity       │
│ required_pts │
│ unlock_cond  │
│ is_active    │
│ created_at   │
│ updated_at   │
└──────────────┘
      │
      └──→ user_badges (1:M)

┌──────────────────┐
│  user_badges     │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ badge_id (FK)    │
│ unlocked_at      │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│  user_levels     │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ current_level    │
│ total_points     │
│ points_to_next   │
│ lifetime_points  │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│ activity_logs    │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ activity_type    │
│ points_earned    │
│ description      │
│ related_type     │
│ related_id       │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│   reminders      │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ type             │
│ title            │
│ message          │
│ scheduled_time   │
│ frequency        │
│ days_of_week     │
│ is_active        │
│ last_sent_at     │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌──────────────────┐
│ notifications    │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ type             │
│ title            │
│ message          │
│ related_type     │
│ related_id       │
│ is_read          │
│ read_at          │
│ created_at       │
│ updated_at       │
└──────────────────┘
```

## Key Relationships

### One-to-Many (1:M)
- User → Journals
- User → Notebooks
- User → Moods
- User → ForumPosts
- User → ForumComments
- User → Challenges
- User → UserBadges
- User → Reminders
- User → Notifications
- Notebook → Journals
- Journal → JournalMedia
- Journal → JournalTags
- Mood → MoodTriggers
- Challenge → UserChallenges
- Challenge → ChallengeRewards
- UserChallenge → ChallengeCheckpoints
- ForumCategory → ForumPosts
- ForumPost → ForumComments
- ForumPost → ForumPostMedia
- ForumPost → ForumReactions
- ForumComment → ForumReactions
- ForumComment → ForumComments (self-referencing)

### One-to-One (1:1)
- User → UserLevel

### Polymorphic (Many-to-Many)
- ForumReactions (posts & comments)
- ForumReports (posts & comments)

## Indexes

### Performance Indexes
```sql
-- Users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_is_active ON users(is_active);

-- Journals
CREATE INDEX idx_journals_user_id ON journals(user_id);
CREATE INDEX idx_journals_entry_date ON journals(entry_date);
CREATE INDEX idx_journals_notebook_id ON journals(notebook_id);
FULLTEXT INDEX idx_journals_fulltext ON journals(title, content);

-- Moods
CREATE INDEX idx_moods_user_id ON moods(user_id);
CREATE INDEX idx_moods_recorded_at ON moods(recorded_at);

-- Forum
CREATE INDEX idx_forum_posts_user_id ON forum_posts(user_id);
CREATE INDEX idx_forum_posts_category_id ON forum_posts(category_id);
CREATE INDEX idx_forum_comments_post_id ON forum_comments(post_id);
CREATE INDEX idx_forum_comments_user_id ON forum_comments(user_id);
FULLTEXT INDEX idx_forum_fulltext ON forum_posts(title, content);

-- Challenges
CREATE INDEX idx_user_challenges_user_id ON user_challenges(user_id);
CREATE INDEX idx_user_challenges_status ON user_challenges(status);

-- Gamification
CREATE INDEX idx_user_badges_user_id ON user_badges(user_id);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
```

## Data Types

### String Fields
- `name`, `title`, `email`: VARCHAR(255)
- `slug`: VARCHAR(255) UNIQUE
- `bio`, `description`: TEXT
- `content`: LONGTEXT
- `emoji`, `type`, `reason`: VARCHAR(50)

### Numeric Fields
- `id`: BIGINT UNSIGNED AUTO_INCREMENT
- `points`, `level`, `intensity`: INT
- `views`, `replies_count`: INT DEFAULT 0
- `size`: BIGINT

### Date/Time Fields
- `created_at`, `updated_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `deleted_at`: TIMESTAMP NULL
- `entry_date`, `checkpoint_date`, `period_date`: DATE
- `recorded_at`, `scheduled_time`: DATETIME
- `published_at`, `completed_at`, `last_sent_at`: TIMESTAMP NULL

### Boolean Fields
- `is_private`, `is_active`, `is_admin`: TINYINT(1) DEFAULT 0
- `is_anonymous`, `is_pinned`, `is_locked`: TINYINT(1) DEFAULT 0
- `is_template`, `is_read`: TINYINT(1) DEFAULT 0

### JSON Fields
- `days_of_week`: JSON (array of day numbers)
- `mood_distribution`: JSON (emoji → count mapping)
- `top_triggers`: JSON (trigger → count mapping)

## Constraints

### Foreign Keys
- All `*_id` fields reference parent tables
- Cascade delete for dependent records
- Restrict delete for referenced records

### Unique Constraints
- `users.email`
- `users.username`
- `challenges.slug`
- `forum_categories.slug`
- `user_badges` (user_id, badge_id)
- `user_challenges` (user_id, challenge_id)
- `challenge_checkpoints` (user_challenge_id, checkpoint_date)

## Soft Deletes

Tables with soft deletes:
- `users`
- `journals`
- `notebooks`
- `forum_posts`
- `forum_comments`

Queries automatically exclude soft-deleted records unless explicitly included.
