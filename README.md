# 🌸 Moodiary

A modern, beautiful journaling + mood tracker + community forum + self-improvement challenge app built with Laravel 11, TailwindCSS, and Alpine.js.

## ✨ Features

### 📝 Journaling
- Rich text editor with formatting (bold, italic, bullets)
- Photo, video, and audio uploads
- Organize journals into notebooks
- Calendar view
- PIN protection for private entries
- Automatic reading time calculation
- Full-text search

### 😊 Mood Tracking
- Emoji-based mood logging (happy, sad, angry, stressed, etc.)
- Intensity scale (1-10)
- Mood triggers tracking (work, relationships, weather, health, etc.)
- Weekly & monthly statistics
- AI-ready mood insights
- Mood trend visualization

### 💬 Community Forum
- Create and share posts
- Threaded comments
- Reactions: Like, Support, Hug
- Anonymous posting mode
- Report system for moderation
- Category-based organization

### 🎯 30-Day Challenges
- Pre-built challenges (gratitude, deep work, minimalism, focus)
- Create custom challenges
- Daily checklist tracking
- Streak system
- Progress visualization
- Milestone rewards

### 🏆 Gamification
- Points system
- Level progression
- Badge collection
- Activity leaderboard
- Achievement tracking

### 🔔 Reminders & Notifications
- Customizable reminders for journaling
- Mood check-in reminders
- Challenge reminders
- Real-time notifications

### 👨‍💼 Admin Panel
- User management
- Challenge template management
- Forum moderation
- Report management
- Usage analytics

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.2)
- **Database**: MySQL 8.x
- **Frontend**: Blade + TailwindCSS + Alpine.js
- **Queue**: Redis (with database fallback)
- **Cache**: Redis
- **Storage**: Local public + S3-ready
- **PDF Export**: Dompdf
- **Authentication**: Laravel Breeze + Google OAuth
- **Search**: MySQL Fulltext

## 📋 Requirements

- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Composer
- Redis (optional, for queue/cache)

## 🚀 Installation

### 1. Clone & Setup
```bash
cd d:/Moodiary
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env`:
```
DB_DATABASE=moodiary
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations
```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets
```bash
npm run build
# or for development
npm run dev
```

### 6. Start Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## 📁 Project Structure

```
moodiary/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── JournalController.php
│   │   │   ├── MoodController.php
│   │   │   ├── ChallengeController.php
│   │   │   ├── ForumController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── Admin/
│   │   │   └── Api/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Journal.php
│   │   ├── Mood.php
│   │   ├── Challenge.php
│   │   ├── ForumPost.php
│   │   └── ...
│   ├── Services/
│   │   ├── GamificationService.php
│   │   ├── MoodInsightService.php
│   │   ├── ChallengeService.php
│   │   └── ForumModerationService.php
│   ├── Policies/
│   └── Jobs/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── components/
│   │   ├── dashboard.blade.php
│   │   ├── journals/
│   │   ├── moods/
│   │   ├── challenges/
│   │   ├── forum/
│   │   ├── admin/
│   │   └── profile/
│   ├── css/
│   │   └── app.css
│   └── js/
│       ├── app.js
│       └── bootstrap.js
├── routes/
│   ├── web.php
│   ├── api.php
│   └── auth.php
├── config/
├── storage/
├── tests/
└── public/
```

## 🎨 Color Palette

- **Off White**: #F6F5F2
- **Lavender**: #EDE4FF
- **Cloud Blue**: #C8D9EB
- **Blush Pink**: #FFE8E8
- **Mint**: #D1F2EB
- **Cream**: #FDF7E4
- **Indigo Accent**: #A5B4FC
- **Soft Gray**: #8B8EAB

## 📚 API Documentation

See [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) for complete API reference.

### Quick API Examples

```bash
# Get journals
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/journals

# Create mood entry
curl -X POST http://localhost:8000/api/moods \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"emoji":"happy","intensity":8,"note":"Great day!"}'

# Get mood stats
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/moods/stats/weekly
```

## 🔐 Authentication

### Register
```
POST /register
```

### Login
```
POST /login
```

### Google OAuth
```
GET /auth/google
GET /auth/google/callback
```

## 📊 Database Schema

### Core Tables
- `users` - User accounts
- `journals` - Journal entries
- `notebooks` - Journal folders
- `journal_media` - Images, videos, audio
- `moods` - Mood logs
- `mood_triggers` - Mood trigger categories
- `mood_insights` - Generated insights

### Forum Tables
- `forum_categories` - Forum categories
- `forum_posts` - Forum posts
- `forum_comments` - Post comments
- `forum_reactions` - Like, support, hug reactions
- `forum_reports` - Content reports

### Challenge Tables
- `challenges` - Challenge templates
- `user_challenges` - User challenge progress
- `challenge_checkpoints` - Daily checkpoints
- `challenge_rewards` - Milestone rewards

### Gamification Tables
- `badges` - Badge definitions
- `user_badges` - User badge unlocks
- `user_levels` - User level & points
- `activity_logs` - Activity tracking

### Notification Tables
- `reminders` - User reminders
- `notifications` - User notifications

## 🚀 Deployment

### Shared Hosting / cPanel

1. Upload files via FTP
2. Create MySQL database
3. Update `.env` with database credentials
4. Run migrations: `php artisan migrate`
5. Set proper permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 777 storage bootstrap/cache
   ```
6. Configure cron job for queue:
   ```
   * * * * * cd /path/to/moodiary && php artisan schedule:run >> /dev/null 2>&1
   ```

### VPS / Dedicated Server

1. Clone repository
2. Install dependencies
3. Configure Nginx/Apache
4. Setup SSL certificate
5. Configure Redis for queue/cache
6. Setup supervisor for queue worker
7. Configure cron jobs

## 🧪 Testing

```bash
php artisan test
```

## 📝 Development

### Create Migration
```bash
php artisan make:migration create_table_name
```

### Create Model
```bash
php artisan make:model ModelName -m
```

### Create Controller
```bash
php artisan make:controller ControllerName
```

### Create Service
```bash
php artisan make:class Services/ServiceName
```

## 🐛 Troubleshooting

### Storage Link Missing
```bash
php artisan storage:link
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Queue Issues
```bash
php artisan queue:work
```

## 📄 License

MIT License

## 👥 Contributing

Contributions welcome! Please follow Laravel best practices.

## 📞 Support

For issues and questions, please create an issue in the repository.

---

**Made with ❤️ for wellness and productivity**
