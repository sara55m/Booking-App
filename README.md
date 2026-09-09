# Booking App

A booking backend built with Laravel, providing a RESTful API for managing properties, rooms, bookings, payments, reviews, offers, rewards, and user interactions.

The project also includes a Filament-based administration dashboard for managing the application's resources and operations.

## Features

### Authentication & Users

- Email OTP-based authentication
- API authentication using Laravel Sanctum
- User authentication and authorization
- Role and permission management
- User profile management
- Favorite properties
- Saved and managed AI-generated trip plans
- Payment method management
- Transaction history
- Reward points and points history

### Properties & Bookings

- Property and room management
- Room availability checking
- Hotel/property search
- Booking management
- Policy-based booking cancellation according to property cancellation rules
- Booking expiration handling
- Booking payment management
- Database transactions and locking to prevent double bookings

### Payments & Refunds

- Stripe payment integration
- Idempotent payment processing using idempotency keys
- Partial and remaining booking payments
- Automatic stripe refund amount calculation
- Reward points conversion and handling during refunds
- Invoice generation for payments and cancellations
- Invoices attached to email notifications
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

- User Email notifications for booking, payment, review, offer, and arrival events
- Booking confirmation and cancellation notifications
- Booking expiration notifications
- Booking payment and balance due reminders
- Payment success and failure notifications
- Booking arrival reminders
- Real-time event broadcasting using Pusher and Laravel Echo
- Offer notifications
- Review approval, rejection, reminder, and update notifications
- Admin notifications for booking, payment, and review events
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
- Pusher
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

```

## Installation

### Requirements

Make sure you have the following installed:

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL
- Redis

### Clone the Repository

```bash
git clone https://github.com/sara55m/Booking-App.git
cd Booking-App
```

### Install PHP Dependencies

```bash
composer install
```

### Install Frontend Dependencies

```bash
npm install
```

### Environment Configuration

Create your environment file:

```bash
cp .env.example .env
```

On Windows:

```cmd
copy .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your database and other required services in the `.env` file.

### Database

Run the database migrations:

```bash
php artisan migrate
```

Seed the database:

```bash
php artisan db:seed
```

### Storage

Create the public storage link:

```bash
php artisan storage:link
```

### Frontend Assets

Build the frontend assets:

```bash
npm run build
```

### Run the Application

Start the Laravel development server:

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

### Queue Worker

Start the queue worker:

```bash
php artisan queue:work
```

### Scheduler

Start the Laravel scheduler:

```bash
php artisan schedule:work
```

---

## Integrations

### Stripe

The application uses Stripe for booking payments and refund processing.

Stripe credentials and webhook secrets must be configured through environment variables.

### Groq AI

The application uses Groq AI for AI-powered features including:

- Property search
- Travel assistant
- Personalized travel planning
- Property review summaries

Groq API credentials must be configured through environment variables.

---

## Admin Dashboard

The project includes a Filament administration dashboard for managing:

- Countries
- Cities
- Amenities
- Travel Categories
- Property Types
- Properties
- Room Types
- Rooms
- Bookings
- Reviews
- Review Tags and Categories
- Offers
- Users
- User Reward Points History
- Payments

---

## API Documentation

The API is tested and documented using Postman.

The API provides endpoints for:

- Authentication
- Properties and rooms
- Bookings
- Payments and refunds
- Reviews and ratings
- Offers
- Favorites
- Reward points
- User profile and account management
- AI-powered features

### Postman Collection

[View the API Collection on Postman]

(https://www.postman.com/red-shuttle-2874393/workspace/booking-app/collection/50798855-372dd1c8-4657-4a99-bc56-d08966045ef6?action=share&source=copy-link&creator=50798855)

---

## Testing

Automated tests will be added using PHPUnit, including unit and feature tests for the application's core functionality and business logic.

## Security

Never commit the `.env` file or expose sensitive credentials, including:

- Database credentials
- API keys
- Stripe secrets
- Webhook secrets
- AI API keys
- Mail credentials

Use `.env.example` to document the required environment variables without exposing sensitive values.

---

## Project Status

🚧 This project is currently under development.

---

## Author

**Sara Mohamed**  
Junior Backend PHP/Laravel Developer

GitHub: https://github.com/sara55m

---

## License

This project is currently intended for portfolio and educational purposes.
