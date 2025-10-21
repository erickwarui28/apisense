<?php

namespace Database\Seeders;

use App\Models\ApiRepository;
use Illuminate\Database\Seeder;

class ApiRepositorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apis = [
            [
                'name' => 'OpenWeatherMap API',
                'category' => 'weather',
                'features' => ['current weather', 'forecast', 'historical data', 'weather alerts'],
                'pricing' => 'freemium',
                'documentation_quality' => 8.5,
                'community_rating' => 4.2,
                'description' => 'Comprehensive weather data API with current conditions, forecasts, and historical weather information.',
                'website_url' => 'https://openweathermap.org/api',
                'documentation_url' => 'https://openweathermap.org/api',
                'tags' => ['weather', 'forecast', 'climate', 'meteorology'],
            ],
            [
                'name' => 'Stripe API',
                'category' => 'payments',
                'features' => ['payment processing', 'subscriptions', 'invoicing', 'marketplace payments'],
                'pricing' => 'paid',
                'documentation_quality' => 9.5,
                'community_rating' => 4.7,
                'description' => 'Complete payment processing platform for online businesses with support for cards, digital wallets, and more.',
                'website_url' => 'https://stripe.com',
                'documentation_url' => 'https://stripe.com/docs',
                'tags' => ['payments', 'billing', 'subscriptions', 'ecommerce'],
            ],
            [
                'name' => 'Twilio API',
                'category' => 'communications',
                'features' => ['SMS', 'voice calls', 'video', 'email', 'whatsapp'],
                'pricing' => 'paid',
                'documentation_quality' => 9.0,
                'community_rating' => 4.5,
                'description' => 'Cloud communications platform for building SMS, voice, and video applications.',
                'website_url' => 'https://www.twilio.com',
                'documentation_url' => 'https://www.twilio.com/docs',
                'tags' => ['sms', 'voice', 'video', 'communications', 'notifications'],
            ],
            [
                'name' => 'Firebase Authentication',
                'category' => 'authentication',
                'features' => ['user authentication', 'social login', 'multi-factor auth', 'user management'],
                'pricing' => 'freemium',
                'documentation_quality' => 9.2,
                'community_rating' => 4.6,
                'description' => 'Google\'s authentication service with support for multiple providers and security features.',
                'website_url' => 'https://firebase.google.com/products/auth',
                'documentation_url' => 'https://firebase.google.com/docs/auth',
                'tags' => ['authentication', 'security', 'google', 'firebase'],
            ],
            [
                'name' => 'SendGrid API',
                'category' => 'email',
                'features' => ['email delivery', 'email templates', 'analytics', 'suppression management'],
                'pricing' => 'freemium',
                'documentation_quality' => 8.8,
                'community_rating' => 4.3,
                'description' => 'Email delivery service with advanced features for transactional and marketing emails.',
                'website_url' => 'https://sendgrid.com',
                'documentation_url' => 'https://docs.sendgrid.com',
                'tags' => ['email', 'marketing', 'delivery', 'templates'],
            ],
            [
                'name' => 'Google Maps API',
                'category' => 'maps',
                'features' => ['maps', 'geocoding', 'directions', 'places', 'street view'],
                'pricing' => 'freemium',
                'documentation_quality' => 9.0,
                'community_rating' => 4.4,
                'description' => 'Comprehensive mapping and location services with detailed maps and location data.',
                'website_url' => 'https://developers.google.com/maps',
                'documentation_url' => 'https://developers.google.com/maps/documentation',
                'tags' => ['maps', 'geocoding', 'directions', 'location', 'google'],
            ],
            [
                'name' => 'GitHub API',
                'category' => 'development',
                'features' => ['repository management', 'issues', 'pull requests', 'webhooks', 'actions'],
                'pricing' => 'freemium',
                'documentation_quality' => 9.3,
                'community_rating' => 4.8,
                'description' => 'Complete API for managing GitHub repositories, issues, and development workflows.',
                'website_url' => 'https://github.com',
                'documentation_url' => 'https://docs.github.com/en/rest',
                'tags' => ['git', 'repository', 'development', 'version control'],
            ],
            [
                'name' => 'AWS S3 API',
                'category' => 'storage',
                'features' => ['file storage', 'CDN', 'backup', 'data archiving', 'static hosting'],
                'pricing' => 'paid',
                'documentation_quality' => 8.7,
                'community_rating' => 4.5,
                'description' => 'Scalable object storage service for storing and retrieving any amount of data.',
                'website_url' => 'https://aws.amazon.com/s3',
                'documentation_url' => 'https://docs.aws.amazon.com/s3',
                'tags' => ['storage', 'aws', 'cloud', 'files', 'backup'],
            ],
            [
                'name' => 'Slack API',
                'category' => 'productivity',
                'features' => ['messaging', 'bots', 'integrations', 'file sharing', 'workflows'],
                'pricing' => 'freemium',
                'documentation_quality' => 8.9,
                'community_rating' => 4.4,
                'description' => 'Team communication platform with extensive API for building integrations and bots.',
                'website_url' => 'https://api.slack.com',
                'documentation_url' => 'https://api.slack.com/docs',
                'tags' => ['messaging', 'team', 'productivity', 'bots', 'integrations'],
            ],
            [
                'name' => 'Unsplash API',
                'category' => 'media',
                'features' => ['photo search', 'photo downloads', 'collections', 'user profiles'],
                'pricing' => 'freemium',
                'documentation_quality' => 8.5,
                'community_rating' => 4.3,
                'description' => 'High-quality stock photography API with millions of free images.',
                'website_url' => 'https://unsplash.com/developers',
                'documentation_url' => 'https://unsplash.com/documentation',
                'tags' => ['photos', 'images', 'stock', 'media', 'creative'],
            ],
            [
                'name' => 'NewsAPI',
                'category' => 'news',
                'features' => ['news articles', 'headlines', 'sources', 'search', 'categorization'],
                'pricing' => 'freemium',
                'documentation_quality' => 8.2,
                'community_rating' => 4.1,
                'description' => 'News aggregation API providing access to thousands of news sources worldwide.',
                'website_url' => 'https://newsapi.org',
                'documentation_url' => 'https://newsapi.org/docs',
                'tags' => ['news', 'articles', 'headlines', 'media', 'current events'],
            ],
            [
                'name' => 'Spotify Web API',
                'category' => 'music',
                'features' => ['music streaming', 'playlists', 'search', 'user data', 'recommendations'],
                'pricing' => 'freemium',
                'documentation_quality' => 8.8,
                'community_rating' => 4.5,
                'description' => 'Access to Spotify\'s music catalog and user data for building music applications.',
                'website_url' => 'https://developer.spotify.com',
                'documentation_url' => 'https://developer.spotify.com/documentation',
                'tags' => ['music', 'streaming', 'playlists', 'audio', 'entertainment'],
            ],
        ];

        foreach ($apis as $apiData) {
            ApiRepository::create($apiData);
        }
    }
}