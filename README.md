# YoPrint Laravel Coding Project

## Project Overview

This is a **Laravel CSV Upload Application** developed as part of the **YoPrint Laravel Engineer** coding assignment. The project demonstrates proficiency in Laravel development through a lightweight application for uploading and processing CSV files with real-time progress tracking via WebSocket connections.

### Assignment Details
- **Company**: YoPrint
- **Position**: Laravel Engineer
- **Estimated Duration**: 1 Hour
- **Submission Deadline**: July 14th, 2025
- **Eligibility**: Malaysian citizens and permanent residents currently residing in Malaysia

### Project Purpose
This mini project is intended to verify proficiency in Laravel development, kept short and simple to ensure completion within a few hours while demonstrating:
- Laravel framework expertise
- Real-time WebSocket implementation
- Background job processing
- Database design and migrations
- Modern PHP development practices

## Application Features

A lightweight Laravel application for uploading and processing CSV files with real-time progress tracking via WebSocket connections.

## Features

- **File Upload**: Drag & drop CSV file upload with 100MB size limit
- **Background Processing**: Chunked CSV processing using Laravel Queues
- **Real-time Updates**: WebSocket-based progress tracking via Laravel Reverb
- **Progress Dashboard**: Monitor upload status with live progress indicators
- **Database Storage**: SQLite database with file tracking and product storage
- **Anonymous Access**: No authentication required
- **Error Handling**: Comprehensive error logging and failed record tracking

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js & NPM (for frontend assets)
- SQLite (included with PHP)

## Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd yoprint
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations to create database tables
php artisan migrate
```

### 5. Build Frontend Assets
```bash
# Compile frontend assets
npm run build
```

## Running the Application

### Option 1: Using Helper Scripts (Recommended)

**Start All Services:**
```bash
# Double-click or run in terminal
start-all-services.bat
```

**Stop All Services:**
```bash
# Double-click or run in terminal
stop-all-services.bat
```

### Option 2: Manual Setup

**Terminal 1 - Laravel Development Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work --verbose
```

**Terminal 3 - WebSocket Server:**
```bash
php artisan reverb:start
```

### Access the Application

- **Main Dashboard**: [http://localhost:8000](http://localhost:8000)
- **WebSocket Server**: [ws://localhost:8080](ws://localhost:8080)

## Usage

### Uploading CSV Files

1. Navigate to [http://localhost:8000](http://localhost:8000)
2. Drag & drop a CSV file or click "Upload File" to browse
3. Monitor real-time progress in the dashboard
4. View upload history with detailed statistics

### CSV File Format

The application expects CSV files with the following headers:
- `UNIQUE_KEY` (required)
- `PRODUCT_TITLE`
- `PRODUCT_DESCRIPTION`
- `STYLE#`
- `SANMAR_MAINFRAME_COLOR`
- `SIZE`
- `COLOR_NAME`
- `PIECE_PRICE`

### API Endpoints

- `POST /api/upload` - Upload CSV file
- `GET /api/status?id={upload_id}` - Get upload status
- `GET /api/uploads` - Get all uploads

## Project Structure

```
├── app/
│   ├── Events/
│   │   └── FileProcessingUpdate.php      # WebSocket event broadcasting
│   ├── Http/Controllers/
│   │   └── FileUploadController.php      # Main upload controller
│   ├── Jobs/
│   │   └── ProcessCsvJob.php             # Background CSV processing
│   └── Models/
│       ├── FileUpload.php                # File upload tracking
│       └── Product.php                   # Product data model
├── database/
│   └── migrations/
│       ├── *_create_file_uploads_table.php
│       └── *_create_products_table.php
├── resources/
│   └── views/
│       └── upload.blade.php              # Main dashboard view
├── routes/
│   └── web.php                           # Application routes
├── storage/
│   └── app/
│       └── private/
│           └── uploads/                  # Uploaded CSV files
├── start-all-services.bat               # Helper script to start all services
├── stop-all-services.bat                # Helper script to stop all services
└── README.md                            # This file
```

## Configuration

### Environment Variables

Key configurations in `.env`:

```env
# Database
DB_CONNECTION=sqlite

# Queue
QUEUE_CONNECTION=database

# Broadcasting (WebSocket)
BROADCAST_CONNECTION=reverb

# Reverb Settings
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### File Upload Settings

- **Max File Size**: 100MB (configurable in `FileUploadController.php`)
- **Allowed File Types**: CSV, TXT
- **Storage Location**: `storage/app/private/uploads/`
- **Processing Chunk Size**: 100 records per batch

## Troubleshooting

### Common Issues

**1. Files stuck in "Pending" status**
- Ensure the queue worker is running: `php artisan queue:work --verbose`
- Check logs: `storage/logs/laravel.log`

**2. WebSocket connection failed**
- Ensure Reverb server is running: `php artisan reverb:start`
- Check if port 8080 is available

**3. File upload validation errors**
- Check file size (max 100MB)
- Ensure file extension is .csv or .txt
- Verify file is not corrupted

**4. Database errors**
- Run migrations: `php artisan migrate`
- Check SQLite file permissions

### Log Files

- **Application logs**: `storage/logs/laravel.log`
- **Queue processing**: Monitor queue worker terminal output
- **WebSocket events**: Monitor Reverb server terminal output

### Restart Services

If you encounter issues, restart all services:
```bash
stop-all-services.bat
start-all-services.bat
```

## Development

### Adding New Features

1. **Models**: Add to `app/Models/`
2. **Controllers**: Add to `app/Http/Controllers/`
3. **Jobs**: Add to `app/Jobs/`
4. **Migrations**: Create with `php artisan make:migration`
5. **Events**: Add to `app/Events/`

### Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=FileUploadTest
```

### Database Management

```bash
# Reset database
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Check migration status
php artisan migrate:status
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions, please check the troubleshooting section or review the application logs in `storage/logs/laravel.log`.
