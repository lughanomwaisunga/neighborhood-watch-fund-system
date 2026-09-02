# Quick Start Guide

## Installation Steps

### 1. Clone Repository
```bash
git clone https://github.com/lughanomwaisunga/neighborhood-watch-fund-system.git
cd neighborhood-watch-fund-system
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Database
```bash
mysql -u root -p < database/schema.sql
```

### 4. Configure Environment
```bash
cp .env.example .env
```

Edit `.env` with your settings:
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=neighborhood_watch
DB_PORT=3306

MPAMBA_API_KEY=your_key
MPAMBA_API_SECRET=your_secret

AIRTEL_API_KEY=your_key
AIRTEL_API_SECRET=your_secret
```

### 5. Start Application
```bash
php -S localhost:8000 -t public
```

Visit: `http://localhost:8000`

## Features Overview

### Dashboard
- Collection statistics
- Active members count
- Pending payments
- Recent transactions

### Member Management
- Register new members with plot numbers
- Auto-calculate contributions based on tier
- Search and filter members
- View member details and balance

### Payment Processing
- Mpamba mobile money integration
- Airtel Money integration
- Manual payment recording (cash/bank transfer)
- Payment verification and confirmation

### Financial Statements
- Individual member statements
- Transaction history
- Outstanding balance tracking
- PDF export functionality

### Reports
- Collection summary reports
- Member contribution analysis
- Payment method statistics

## User Roles

- **Admin**: Full system access
- **Treasurer**: Can manage payments and view reports
- **Viewer**: Read-only access

## API Endpoints

### Members
- `GET /members` - List all members
- `GET /members/{id}` - Get member details
- `POST /members/create` - Register new member
- `POST /members/{id}/update` - Update member
- `GET /members/search?q=query` - Search members

### Payments
- `GET /payments` - List all payments
- `POST /payments/initiate` - Initiate payment
- `POST /payments/verify` - Verify payment
- `GET /payments/{id}` - Get payment details

### Statements
- `GET /statements` - List all member statements
- `GET /statements/{id}` - Get member statement
- `GET /statements/{id}/pdf` - Download PDF statement

### Dashboard
- `GET /dashboard` - Dashboard overview
- `GET /dashboard/stats` - Statistics

## Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify credentials in `.env`
- Ensure database is created

### Payment Gateway Issues
- Verify API credentials in `.env`
- Check internet connection
- Review API documentation

### Permission Errors
- Ensure proper file permissions
- Check directory ownership
- Verify PHP execution rights

## Support

For issues or questions:
1. Check existing GitHub issues
2. Create a new issue with details
3. Contact the maintainer

## License

MIT License - See LICENSE file
