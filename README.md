# Booking App

A booking backend built with Laravel, providing a RESTful API for managing properties, rooms, bookings, payments, reviews, offers, rewards, and user interactions.

The project also includes a Filament-based administration dashboard for managing the application's resources and operations.

## Features

### Authentication & Users

- Email OTP-based authentication
- User authentication and authorization
- Role and permission management
- User profile management
- Favorite properties
- Saved trip plans
- Payment method management
- Transaction history
- Reward points and points history

### Properties & Bookings

- Property and room management
- Room availability checking
- Hotel/property search
- Booking management
- Booking expiration handling
- Booking payment management
- Database transactions and locking to prevent double bookings

### Payments & Refunds

- Stripe payment integration
- Idempotent payment processing using idempotency keys
- Partial and remaining booking payments
- Automatic stripe refund amount calculation
- Reward points conversion and handling during refunds
- Secure payment webhook handling

### Offers & Rewards

- Offers and discount management
- Offer validation based on booking conditions
- Rewards points earning and redemption
- Reward points conversion during payments and refunds

### Reviews & Ratings

- Reviews and ratings
- Review categories and tags
- Review approval and rejection workflow
- Review editing restrictions
- AI-powered review summaries using Groq AI
- Automatic review summary regeneration based on review changes

### AI Features

- AI-powered property search using Groq AI
- AI travel assistant using Groq AI
- AI-powered travel planner that generates personalized trip plans based on users' interests , travel style , destination and travel dates using Groq AI
- Conversation history storage to support follow-up interactions and refinement of generated trip plans
- AI-powered review summaries using Groq AI

### Notifications & Background Processing

- Email notifications
- Booking reminders
- Review Reminders
- Queued jobs for background processing
- Scheduled tasks
- Event-driven application workflows

### Administration

- Filament admin dashboard
- Property management
- Room management
- Booking management
- Payment management
- Review management
- Offer management
- User management
- Country and city management
- Amenity management
- Room type management
- Travel category management
- Review tag and category management
- Property type management

### API

- RESTful API
- Request validation
- Authorization policies
- Pagination and filtering
- Search functionality

## Tech Stack

- PHP
- Laravel
- MySQL
- Filament
- Redis
- Laravel Sanctum
- Spatie Laravel Permission
- Stripe
- Groq AI
- Blade
- Vite

## Project Structure

```text
app/
├── Console/            # Artisan commands and scheduled tasks
├── Enums/              # Application enums
├── Events/             # Application events
├── Filament/           # Filament resources, pages, and widgets
├── Http/
│   ├── Controllers/    # API and web controllers
│   ├── Middleware/     # HTTP middleware
│   ├── Requests/       # Form request validation
│   └── Resources/      # API resources
├── Jobs/               # Queued background jobs
├── Listeners/          # Event listeners
├── Livewire/           # Livewire components
├── Models/             # Eloquent models
├── Notifications/      # Application notifications
├── Observers/          # Model observers
├── Policies/           # Authorization policies
└── Services/           # Business logic and integrations

bootstrap/              # Framework bootstrapping
config/                 # Application configuration

database/
├── factories/          # Model factories
├── migrations/         # Database migrations
└── seeders/            # Database seeders

lang/                   # Localization files
public/                 # Public assets and application entry point

resources/
├── css/                # CSS assets
├── js/                 # JavaScript assets
└── views/
    ├── filament/       # Filament-related views
    └── invoices/       # Invoice templates

routes/
├── api.php             # API routes
├── channels.php        # Broadcast channel authorization
├── console.php         # Console routes
└── web.php             # Web routes

storage/                # Logs, cache, and generated files
tests/                  # Automated tests

Installation
Requirements
PHP 8.2+
Composer
Node.js and npm
MySQL
Redis
Clone the repository
git clone https://github.com/sara55m/Booking-App.git

cd Booking-App
Install PHP dependencies
composer install
Install frontend dependencies
npm install
Environment configuration

Create your environment file:

cp .env.example .env

On Windows:

copy .env.example .env

Generate the application key:

php artisan key:generate

Configure your database and other services in .env.

Database

Run the migrations:

php artisan migrate

seed the database:

php artisan db:seed

Storage

Create the public storage link:

php artisan storage:link
Frontend assets

Build the frontend assets:

npm run build
Run the application
php artisan serve

The application will be available at:

http://127.0.0.1:8000
Queue Worker

Run the queue worker with:

php artisan queue:work
Scheduler

Run the Laravel scheduler with:

php artisan schedule:work
Integrations
Stripe

The application uses Stripe for booking payments.

Stripe credentials and webhook secrets must be configured through environment variables.

Groq AI

The application uses Groq AI to generate summaries of approved property reviews.

The API credentials must be configured through environment variables.

Admin Dashboard

The project includes a Filament administration dashboard for managing application resources such as:

Countries
Cities
Amenities
Travel Categories
Property Types
Properties
Room Types
Rooms
Bookings
Reviews
Offers
Users
Users Reward Points Hitory
Payments
API Documentation

## API Documentation

This project provides a RESTful API for authentication, properties, rooms,
bookings, payments, reviews, offers, rewards, favorites, and AI-powered features.

Interactive API documentation is generated using OpenAPI and Scramble.

### API Documentation

[View API Documentation](...)

## Testing

Run the test suite with:

```bash
php artisan test
Security

Never commit the .env file or expose sensitive credentials, including:

Database credentials
API keys
Stripe secrets
Webhook secrets
AI API keys
Mail credentials

Use .env.example to document the required environment variables without exposing sensitive values.

Project Status

🚧 This project is currently under development.

Author

Sara Mohamed
Junior Backend PHP/Laravel Developer

GitHub: https://github.com/sara55m

License

This project is currently intended for portfolio and educational purposes.
