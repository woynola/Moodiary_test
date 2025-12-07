# Moodiary Quick Start Guide

## 5-Minute Setup (Local Development)

### 1. Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Composer

### 2. Clone & Install
```bash
cd d:/Moodiary
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database
```bash
# Create database in MySQL
CREATE DATABASE moodiary;

# Run migrations
php artisan migrate
```

### 5. Start Server
```bash
php artisan serve
npm run dev  # in another terminal
```

Visit: `http://localhost:8000`

---

## Project Structure Quick Reference

```
moodiary/
├── app/
│   ├── Http/Controllers/          # Request handlers
│   ├── Models/                    # Database models
│   ├── Services/                  # Business logic
│   ├── Policies/                  # Authorization
│   └── Http/Middleware/           # Request middleware
├── database/
│   ├── migrations/                # Database schemas
│   ├── seeders/                   # Sample data
│   └── factories/                 # Test data
├── resources/
│   ├── views/                     # Blade templates
│   ├── css/app.css               # Tailwind CSS
│   └── js/app.js                 # Alpine.js
├── routes/
│   ├── web.php                   # Web routes
│   ├── api.php                   # API routes
│   └── auth.php                  # Auth routes
├── config/                        # Configuration files
├── storage/                       # File storage
├── tests/                         # Test files
└── public/                        # Web root
```

---

## Common Commands

### Development
```bash
# Start development server
php artisan serve

# Build frontend assets
npm run dev
npm run build

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Create migration
php artisan make:migration create_table_name

# Seed database
php artisan db:seed
```

### Code Generation
```bash
# Create model with migration
php artisan make:model ModelName -m

# Create controller
php artisan make:controller ControllerName

# Create service
php artisan make:class Services/ServiceName

# Create policy
php artisan make:policy PolicyName
```

### Queue & Jobs
```bash
# Process queue
php artisan queue:work

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

---

## Key Files to Know

### Configuration
- `.env` - Environment variables
- `config/app.php` - Application config
- `config/database.php` - Database config
- `config/cache.php` - Cache config
- `config/queue.php` - Queue config

### Routes
- `routes/web.php` - Web routes (Blade)
- `routes/api.php` - API routes (JSON)
- `routes/auth.php` - Auth routes

### Main Controllers
- `JournalController` - Journal CRUD
- `MoodController` - Mood tracking
- `ChallengeController` - Challenges
- `ForumController` - Forum posts/comments
- `DashboardController` - Dashboard

### Services
- `GamificationService` - Points, badges, levels
- `MoodInsightService` - Mood analytics
- `ChallengeService` - Challenge logic
- `ForumModerationService` - Forum moderation

### Models
- `User` - User accounts
- `Journal` - Journal entries
- `Mood` - Mood logs
- `Challenge` - Challenges
- `ForumPost` - Forum posts
- `Badge` - Badges

---

## API Quick Reference

### Authentication
```bash
# Login
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get token (Sanctum)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### Journals
```bash
# List journals
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/journals

# Create journal
curl -X POST http://localhost:8000/api/journals \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"My Journal","content":"...","notebook_id":1,"entry_date":"2024-01-01"}'

# Get journal
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/journals/1

# Update journal
curl -X PUT http://localhost:8000/api/journals/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated","content":"..."}'

# Delete journal
curl -X DELETE http://localhost:8000/api/journals/1 \
  -H "Authorization: Bearer TOKEN"
```

### Moods
```bash
# List moods
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/moods

# Create mood
curl -X POST http://localhost:8000/api/moods \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"emoji":"happy","intensity":8,"note":"Great day"}'

# Get mood stats
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/moods/stats/weekly

# Get insights
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/moods/insights
```

### Challenges
```bash
# List challenges
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/challenges

# Join challenge
curl -X POST http://localhost:8000/api/challenges/1/join \
  -H "Authorization: Bearer TOKEN"

# Complete checkpoint
curl -X POST http://localhost:8000/api/user-challenges/1/checkpoint \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"date":"2024-01-01"}'

# Get my challenges
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/my-challenges
```

### Forum
```bash
# List posts
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/forum/posts

# Create post
curl -X POST http://localhost:8000/api/forum/posts \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Help","content":"...","category_id":1}'

# Add comment
curl -X POST http://localhost:8000/api/forum/posts/1/comment \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Great post!"}'

# React to post
curl -X POST http://localhost:8000/api/forum/posts/1/react \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"like"}'
```

### User
```bash
# Get profile
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/user/profile

# Update profile
curl -X PATCH http://localhost:8000/api/user/profile \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"New Name","bio":"My bio"}'

# Get badges
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/user/badges

# Get leaderboard
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/user/leaderboard

# Get rank
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/user/rank
```

---

## Useful Tips

### Enable Query Logging
In `config/database.php`:
```php
'connections' => [
    'mysql' => [
        // ... other config
        'logging' => true,
    ]
]
```

Then view in `storage/logs/laravel.log`

### Tinker (Interactive Shell)
```bash
php artisan tinker

# Create user
User::create(['name' => 'John', 'email' => 'john@example.com', 'password' => Hash::make('password')])

# Query data
User::all()
Journal::where('user_id', 1)->get()

# Test service
app(GamificationService::class)->awardPoints($user, 100, 'test')
```

### Artisan Commands
```bash
# List all commands
php artisan list

# Get help for command
php artisan help migrate

# Run specific migration
php artisan migrate --path=database/migrations/2024_01_01_000001_create_users_table.php
```

### Debug Mode
Set `APP_DEBUG=true` in `.env` for detailed error pages

### Storage Link
```bash
php artisan storage:link
```

Creates symlink from `storage/app/public` to `public/storage`

---

## Troubleshooting

### "Class not found" Error
```bash
composer dump-autoload
```

### "No application encryption key" Error
```bash
php artisan key:generate
```

### Database Connection Error
- Check `.env` database credentials
- Ensure MySQL is running
- Verify database exists

### Permission Denied on Storage
```bash
chmod -R 777 storage bootstrap/cache
```

### Port 8000 Already in Use
```bash
php artisan serve --port=8001
```

### Node Modules Issues
```bash
rm -rf node_modules package-lock.json
npm install
```

---

## Next Steps

1. Read [README.md](./README.md) for full overview
2. Check [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) for API details
3. Review [ARCHITECTURE.md](./ARCHITECTURE.md) for system design
4. Study [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md) for data structure
5. Follow [FEATURE_FLOWS.md](./FEATURE_FLOWS.md) for feature implementation
6. Use [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md) for production setup

---

## Support

For issues:
1. Check Laravel documentation: https://laravel.com/docs
2. Check Tailwind CSS: https://tailwindcss.com/docs
3. Check Alpine.js: https://alpinejs.dev
4. Create GitHub issue with details
