# Neighborhood Watch Fund Collection System

A comprehensive PHP/MySQL-based fund collection and management system for neighborhood associations.

## Features

✅ **Member Registration** - Register all members with plot numbers, names, and contact information
✅ **Auto-Calculate Amount Due** - Automatically calculate monthly/annual contributions based on member tier
✅ **Payment Integration** - Accept payments via Mpamba and Airtel Money mobile money platforms
✅ **Member Contributions Tracking** - View all members' payment history and contributions
✅ **Financial Statements** - Generate individual member statements showing balances and transaction history
✅ **Dashboard** - Overview of collection progress, pending payments, and system statistics

## Project Structure

```
neighborhood-watch-fund-system/
├── config/
│   ├── database.php          # Database connection configuration
│   └── constants.php         # Application constants
├── src/
│   ├── classes/
│   │   ├── Member.php        # Member management class
│   │   ├── Payment.php       # Payment processing class
│   │   ├── PaymentGateway.php # Payment gateway integration
│   │   └── Statement.php     # Statement generation class
│   ├── controllers/
│   │   ├── MemberController.php
│   │   ├── PaymentController.php
│   │   └── DashboardController.php
│   └── views/
│       ├── members/
│       ├── payments/
│       ├── statements/
│       └── dashboard/
├── public/
│   ├── index.php             # Application entry point
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── database/
│   └── schema.sql            # Database schema
├── assets/
│   └── images/
├── .env.example              # Environment variables template
├── .gitignore
└── composer.json             # PHP dependencies
```

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Composer
- Mpamba API credentials
- Airtel Money API credentials

## Installation

1. Clone the repository:
```bash
git clone https://github.com/lughanomwaisunga/neighborhood-watch-fund-system.git
cd neighborhood-watch-fund-system
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
# Edit .env with your database and API credentials
```

4. Setup database:
```bash
mysql -u root -p < database/schema.sql
```

5. Start the application:
```bash
php -S localhost:8000 public/index.php
```

Visit `http://localhost:8000` in your browser.

## Usage

### 1. Register Members
- Navigate to Members section
- Add new member with plot number, name, email, phone
- System auto-assigns contribution amount based on tier

### 2. Track Payments
- Members can make payments via Mpamba or Airtel Money
- System records all transactions
- Auto-updates member balance

### 3. View Statements
- Members can view their transaction history
- See outstanding balance
- Download statement as PDF

### 4. Admin Dashboard
- View collection progress
- See pending payments
- Generate financial reports

## Payment Integration

### Mpamba
- API endpoint configuration in `.env`
- Callback URL for payment confirmation
- See `src/classes/PaymentGateway.php`

### Airtel Money
- API endpoint configuration in `.env`
- Callback URL for payment confirmation
- See `src/classes/PaymentGateway.php`

## Database Schema

### Members Table
- member_id (Primary Key)
- plot_number
- full_name
- email
- phone_number
- contribution_tier
- amount_due
- balance
- status (active/inactive)
- created_at
- updated_at

### Payments Table
- payment_id (Primary Key)
- member_id (Foreign Key)
- amount
- payment_method (mpamba/airtel)
- transaction_id
- status (pending/completed/failed)
- payment_date
- created_at

### Transactions Table
- transaction_id (Primary Key)
- member_id (Foreign Key)
- transaction_type (debit/credit)
- amount
- description
- balance_after
- created_at

## Security

- Input validation and sanitization
- SQL injection prevention using prepared statements
- CSRF token protection
- Password hashing for admin accounts
- API request validation

## License

MIT License - See LICENSE file for details

## Support

For issues, feature requests, or contributions, please open an issue on GitHub.

## Contact

**Developer**: Lughan Omwaisunga
**Email**: lughanomwaisunga@example.com
