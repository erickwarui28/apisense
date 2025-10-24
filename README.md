# 🧠 APISense — AI-Powered API Discovery Platform

**Tagline:**  
> *Find the perfect APIs for your project before you start coding.*

APISense is an AI-powered API recommendation system that leverages **Elasticsearch** and **Google Gemini AI** to intelligently match developers and startups with the right APIs for their specific use cases.

## 🎥 Demo Video

Watch APISense in action:

[![APISense Demo](https://img.youtube.com/vi/nHi9osvOCG8/maxresdefault.jpg)](https://youtu.be/nHi9osvOCG8)

*Click the thumbnail above to watch the full demo video*

## 📑 Table of Contents

- [Demo Video](#-demo-video)
- [Features](#-features)
- [Tech Stack](#️-tech-stack)
- [Quick Start](#-quick-start)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
  - [System Requirements](#system-requirements-check)
  - [Database Setup](#6-database-setup)
  - [File Permissions](#5-set-file-permissions)
  - [Troubleshooting](#troubleshooting-common-issues)
- [Elasticsearch Setup](#-elasticsearch-cloud-setup)
- [Gemini API Setup](#-google-gemini-api-setup)
- [Database Schema](#-database-schema)
- [Usage](#-usage)
- [API Endpoints](#-api-endpoints)
- [Testing the Application](#-testing-the-application)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)
- [Support](#-support)

## 🚀 Features

- 🔍 **Natural-Language API Discovery** — Describe your project needs like *"I need APIs for a travel booking app with real-time pricing"*
- 🧾 **Intelligent API Matching** — Gemini AI analyzes your requirements and matches them with the most suitable APIs
- 🧠 **Context-Aware Recommendations** — AI understands project context from README files, descriptions, and requirements
- 💬 **Conversational Interface** — Chat-like UI for intuitive API discovery and comparison
- 📊 **API Analytics Dashboard** — Visual comparisons of API features, pricing, documentation quality, and community ratings
- 📡 **Multi-Source API Repository** — Comprehensive database of public APIs, SDKs, and developer tools
- ⚙️ **Smart Recommendations Engine** — AI explains why each API fits your use case and suggests alternatives
- 📁 **File Upload Analysis** — Upload README.md or project description files for automatic requirement extraction

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| **Framework** | Laravel 12 (PHP 8.3) |
| **Search Engine** | Elasticsearch Cloud |
| **AI Platform** | Google Gemini API |
| **Database** | MySQL |
| **Frontend** | Laravel Blade + TailwindCSS + Alpine.js |
| **Authentication** | Laravel Breeze |

## ⚡ Quick Start

Get APISense running locally in under 5 minutes:

```bash
# 1. Clone and enter directory
git clone https://github.com/erickwarui28/apisense.git
cd apisense

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Create MySQL database
mysql -u root -p -e "CREATE DATABASE apisense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Update .env with database credentials
# DB_CONNECTION=mysql
# DB_DATABASE=apisense
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 6. Run migrations and seed data
php artisan migrate
php artisan db:seed

# 7. Build assets and start server
npm run build
php artisan serve
```

**Important:** You'll need to configure Elasticsearch and Gemini API keys in `.env` for full functionality. See detailed setup instructions below.

## 📋 Prerequisites

- PHP 8.3+
- Composer
- Node.js & NPM
- MySQL 8.0+
- Elasticsearch Cloud account
- Google AI Studio account (for Gemini API)

## 🚀 Installation

### System Requirements Check

Before starting, verify that your system meets all prerequisites:

```bash
# Check PHP version (must be 8.3+)
php -v

# Check Composer installation
composer -V

# Check Node.js and NPM
node -v
npm -v

# Check MySQL installation
mysql --version
```

### 1. Clone the Repository

```bash
git clone <repository-url>
cd apisense
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

**Note:** If you encounter any Composer dependency issues, try:
```bash
composer install --ignore-platform-reqs
```

### 3. Environment Configuration

Copy the `.env.example` file and configure your environment:

```bash
# On Linux/macOS
cp .env.example .env

# On Windows (PowerShell)
Copy-Item .env.example .env

# On Windows (Command Prompt)
copy .env.example .env
```

Update the following variables in your `.env` file:

```env
# Application
APP_NAME=APISense
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apisense
DB_USERNAME=root
DB_PASSWORD=your_password

# Elasticsearch Configuration
ELASTICSEARCH_HOST=https://your-elastic-instance
ELASTICSEARCH_API_KEY=your-elasticsearch-api-key
ELASTICSEARCH_INDEX_PREFIX=apisense

# Gemini API Configuration
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-1.5-pro
GEMINI_API_ENDPOINT=https://generativelanguage.googleapis.com/v1beta

# Session & Cache
SESSION_DRIVER=file
CACHE_STORE=file

# Queue Configuration (use 'database' for production)
QUEUE_CONNECTION=sync

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Set File Permissions

**On Linux/macOS:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

**On Windows:**
```powershell
# Ensure the storage and bootstrap/cache directories are writable
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T
```

### 6. Database Setup

#### Create MySQL Database

1. Create the database using MySQL command:
```sql
CREATE DATABASE apisense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or use the provided SQL file:
```bash
mysql -u root -p < create-database.sql
```

Or create it directly from command line:
```bash
mysql -u root -p -e "CREATE DATABASE apisense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

2. Verify your `.env` database configuration:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apisense
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. Run migrations:
```bash
php artisan migrate
```

4. Seed the database with sample data:
```bash
# Seed all tables
php artisan db:seed

# Or seed specific table
php artisan db:seed --class=ApiRepositorySeeder
```

### 7. Build Frontend Assets

Build the production assets:
```bash
npm run build
```

### 8. Initialize Elasticsearch Index

Create the Elasticsearch index for API search:

```bash
php artisan tinker
```

Then run in the tinker console:
```php
use App\Services\ElasticsearchService;

$elasticsearch = app(ElasticsearchService::class);

// Create the APIs index
if (!$elasticsearch->indexExists('apis')) {
    $elasticsearch->createIndex('apis');
    echo "✓ Index created successfully!\n";
} else {
    echo "✓ Index already exists!\n";
}

// Index sample API data
$apiCount = DB::table('api_repositories')->count();
echo "Found {$apiCount} APIs in database to index.\n";

exit;
```

### 9. Start the Development Server

```bash
# Start Laravel development server
php artisan serve
```

Visit `http://localhost:8000` to access the application.

### 10. Verify Installation

Run this command to verify all services are properly configured:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:list
```

### Troubleshooting Common Issues

#### "Class not found" errors
```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

#### "Permission denied" errors
```bash
# Linux/macOS
sudo chmod -R 775 storage bootstrap/cache

# Windows: Run as Administrator
icacls storage /grant Everyone:F /T
```

#### Database connection errors
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check if database exists: `php artisan db:show`

#### Elasticsearch connection errors
- Verify `ELASTICSEARCH_HOST` and `ELASTICSEARCH_API_KEY`
- Test connection: `php artisan tinker` then `app(ElasticsearchService::class)->ping()`

#### Node.js/NPM errors
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

## 🔧 Elasticsearch Cloud Setup

### 1. Create Elasticsearch Cloud Account

1. Go to [Elasticsearch Cloud](https://cloud.elastic.co/)
2. Sign up for a free account
3. Create a new deployment

### 2. Configure Your Deployment

1. Choose a cloud provider (AWS, GCP, or Azure)
2. Select a region close to your users
3. Choose the "Standard" plan for development (free tier available)
4. Set a deployment name (e.g., "apisense-dev")

### 3. Get Connection Details

1. Once your deployment is ready, go to the "Overview" tab
2. Copy the "Elasticsearch endpoint" URL
3. Go to "Security" tab and create an API key:
   - Click "Create API key"
   - Give it a name (e.g., "apisense-api-key")
   - Copy the generated API key

### 4. Update Environment Variables

```env
ELASTICSEARCH_HOST=https://your-deployment-id.region.cloud.es.io:9243
ELASTICSEARCH_API_KEY=your-generated-api-key
```

### 5. Initialize Elasticsearch Index

```bash
php artisan tinker
```

```php
use App\Services\ElasticsearchService;

$elasticsearch = app(ElasticsearchService::class);

// Create the APIs index
if (!$elasticsearch->indexExists('apis')) {
    $elasticsearch->createIndex('apis');
    echo "Index created successfully!";
} else {
    echo "Index already exists!";
}
```

## 🤖 Google Gemini API Setup

### 1. Get Gemini API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Click "Create API Key"
4. Copy the generated API key

### 2. Update Environment Variables

```env
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-1.5-pro
```

## 📊 Database Schema

### Tables

- **api_repositories** - Stores API metadata and information
- **conversations** - Stores user chat history and AI responses
- **project_uploads** - Tracks uploaded README/project files
- **user_preferences** - Saves user searches and favorite APIs

### Sample API Data

The seeder includes popular APIs like:
- OpenWeatherMap API (Weather)
- Stripe API (Payments)
- Twilio API (Communications)
- Firebase Authentication
- SendGrid API (Email)
- Google Maps API
- GitHub API
- AWS S3 API
- And more...

## 🎯 Usage

### Natural Language Queries

1. Go to the "Natural Language Query" tab
2. Describe your project needs, e.g.:
   - "I'm building a travel app with weather forecasting and currency conversion"
   - "What APIs do I need for a social media analytics dashboard?"
   - "Recommend payment and authentication APIs for my e-commerce platform"
3. Click "Get API Recommendations"
4. Review AI-generated recommendations with explanations

### File Upload Analysis

1. Go to the "Upload Project File" tab
2. Upload your README.md or project description file
3. The AI will automatically extract requirements and suggest relevant APIs

### Browse APIs

1. Go to the "Browse APIs" tab
2. Use search and filters to find APIs by category, pricing, or features
3. View detailed information about each API

## 🔌 API Endpoints

### Authentication Required

- `POST /api/query` - Process natural language queries
- `GET /api/conversation-history` - Get conversation history
- `POST /api/upload` - Upload and analyze project files
- `GET /api/uploads` - Get user's uploaded files
- `GET /api/uploads/{id}` - Get specific upload details
- `DELETE /api/uploads/{id}` - Delete uploaded file
- `GET /api/repository` - Get all APIs with pagination
- `GET /api/repository/{id}` - Get specific API details
- `POST /api/repository/ingest` - Add new API to repository
- `PUT /api/repository/{id}` - Update API
- `DELETE /api/repository/{id}` - Delete API
- `GET /api/repository/categories` - Get API categories
- `POST /api/repository/search` - Search APIs using Elasticsearch

## 🧪 Testing the Application

Once you have APISense up and running, you can test its AI-powered API recommendation features.

### Method 1: Natural Language Query

1. **Access the Dashboard**
   - Navigate to `http://localhost:8000` in your browser
   - Log in or register for an account

2. **Describe Your Project**
   - Locate the **"What are you building?"** text area on the dashboard
   - Describe your project in natural language. For example:
     - *"I'm building a travel booking app with real-time pricing and weather forecasts"*
     - *"I need APIs for an e-commerce platform with payment processing and inventory management"*
     - *"What APIs should I use for a social media analytics dashboard?"*

3. **Generate Recommendations**
   - Click the **"Generate API Recommendations"** button
   - Wait for the AI to analyze your requirements (this may take a few seconds)

4. **Review Results**
   - APISense will display personalized API recommendations
   - Each recommendation includes:
     - API name and description
     - Why it fits your use case
     - Key features and pricing information
     - Documentation links
     - Alternative suggestions

### Method 2: Upload README File

1. **Prepare Your README File**
   - Have a README.md or project description file ready
   - The file should contain details about your project requirements, features, and goals

2. **Upload the File**
   - On the dashboard, locate the **"Upload Project File"** section
   - Click **"Choose File"** or drag and drop your README.md file
   - Click **"Upload and Analyze"**

3. **Automatic Analysis**
   - APISense will automatically extract:
     - Project requirements
     - Technical stack preferences
     - Feature descriptions
     - Integration needs

4. **Review Recommendations**
   - The AI will generate tailored API recommendations based on your file
   - Recommendations are organized by category and relevance
   - You can save favorite recommendations for future reference

### Tips for Best Results

- **Be Specific**: Include details about your target audience, scale, and specific features
- **Mention Technologies**: If you're using specific frameworks or languages, include them
- **List Requirements**: Mention must-have features like authentication, payments, notifications, etc.
- **Consider Budget**: Mention if you need free/freemium APIs or if budget is not a constraint

### Example Queries

**Travel & Booking:**
```
I'm building a travel booking platform that needs flight search, hotel booking, 
car rentals, and real-time currency conversion. The app will serve international 
users and needs multi-language support.
```

**E-commerce:**
```
Building an online store with payment processing, inventory management, 
shipping tracking, and customer notifications via email and SMS.
```

**Social Media:**
```
Creating a social media analytics tool that tracks mentions across Twitter, 
Instagram, and Facebook, with sentiment analysis and reporting features.
```

**Healthcare:**
```
Developing a telemedicine app with video conferencing, appointment scheduling, 
prescription management, and HIPAA-compliant data storage.
```

## 🚀 Deployment

### Production Environment

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database
3. Set up Elasticsearch Cloud production deployment
4. Configure Gemini API with production limits
5. Run `npm run build` for production assets
6. Set up proper file permissions
7. Configure web server (Apache/Nginx)

### Docker Deployment

```dockerfile
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/your-repo/issues) page
2. Create a new issue with detailed information
3. Join our community discussions

## 🔮 Future Enhancements

- 🤖 Integrate Gemini Function Calling for automated API integration code generation
- 🗣️ Add voice input (speech-to-text) for hands-free API discovery
- 🧠 Train model with community feedback and API usage patterns
- 📱 Build mobile version with push notifications for new API releases
- 🛠️ Role-based access for enterprise teams and API vendor partnerships
- 🔗 Direct integration with popular development tools (VS Code, GitHub, etc.)

## 🏁 Conclusion

APISense helps developers move from manual API research to intelligent discovery. By merging Elasticsearch's search capabilities with Gemini's reasoning power, it revolutionizes how developers and startups find and evaluate APIs — making API selection fast, informed, and AI-powered.

---

**Built with ❤️ using Laravel, Elasticsearch, and Google Gemini AI**