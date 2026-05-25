# School Result SaaS - WordPress Plugin

A production-ready multi-tenant Exam & Result Management SaaS WordPress plugin designed for schools to manage students, exams, and results with automated grade computation and PDF generation.

## Features

✅ **Multi-Tenant SaaS Architecture** - Multiple schools with complete data isolation  
✅ **Student Management** - Add and manage students with unique IDs  
✅ **Exam Management** - Create exams per term and session  
✅ **Result Upload & Computation** - Automated grade calculation and positioning  
✅ **Student Portal** - Students can login and view their results  
✅ **Dynamic Result Templates** - 5 pre-built templates with customization  
✅ **PDF Generation** - Generate and download printable result sheets  
✅ **Subscription Billing** - Paystack-ready subscription system  
✅ **Admin Dashboard** - Comprehensive school admin controls  
✅ **REST API** - Full WordPress REST API integration  
✅ **Security** - Nonce validation, capability checks, data isolation  

## Requirements

- WordPress 5.0+
- PHP 7.6+
- MySQL 5.7+
- DomPDF PHP Library

## Installation

1. Upload the `school-result-saas` folder to `/wp-content/plugins/`
2. Activate the plugin from WordPress admin panel
3. Navigate to "School Result SaaS" menu
4. Complete initial setup

## Quick Start

### For Super Admin
1. Create a new school
2. Configure school branding
3. Set subscription plans

### For School Admin
1. Add students
2. Create classes and subjects
3. Create exams
4. Upload results
5. View reports and analytics

### For Students
1. Login with student UID
2. View exam results
3. Download PDF result sheet

## Database Schema

- `srs_schools` - School information and branding
- `srs_students` - Student records
- `srs_classes` - Class/Grade information
- `srs_subjects` - Subject information
- `srs_exams` - Exam records
- `srs_results` - Student exam results
- `srs_subscriptions` - Billing and subscription data

## Result Templates

1. **Classic** - Traditional result format
2. **Modern** - Contemporary design
3. **Minimal** - Minimalist approach
4. **WAEC Style** - WAEC examination format
5. **Premium** - Premium customizable template

## Grade System

| Score Range | Grade | Remark |
|------------|-------|--------|
| 70-100 | A | Excellent |
| 60-69 | B | Good |
| 50-59 | C | Credit |
| 45-49 | D | Pass |
| 40-44 | E | Pass |
| 0-39 | F | Fail |

## API Endpoints

### Schools
- `GET /wp-json/srs/v1/schools`
- `POST /wp-json/srs/v1/schools`
- `GET /wp-json/srs/v1/schools/{id}`
- `PUT /wp-json/srs/v1/schools/{id}`

### Students
- `GET /wp-json/srs/v1/students`
- `POST /wp-json/srs/v1/students`
- `GET /wp-json/srs/v1/students/{id}`
- `PUT /wp-json/srs/v1/students/{id}`

### Results
- `GET /wp-json/srs/v1/results`
- `POST /wp-json/srs/v1/results`
- `GET /wp-json/srs/v1/results/{id}/pdf`

## Security

- Multi-tenancy with strict `school_id` filtering
- WordPress nonce validation
- User capability checks
- Sanitized SQL queries using prepared statements
- Secure file uploads
- Role-based access control

## Development

```bash
# Install dependencies
composer install
npm install

# Build assets
npm run build

# Watch for changes
npm run watch
```

## File Structure

```
school-result-saas/
├── includes/
│   ├── core/           # Core plugin functionality
│   ├── database/       # Database schema and migrations
│   ├── schools/        # School management
│   ├── students/       # Student management
│   ├── classes/        # Class management
│   ├── subjects/       # Subject management
│   ├── exams/          # Exam management
│   ├── results/        # Result management & computation
│   ├── templates/      # Template engine
│   ├── pdf/            # PDF generation
│   ├── billing/        # Subscription & billing
│   ├── api/            # REST API endpoints
│   └── services/       # Business logic services
├── admin/              # Admin dashboard UI
├── public/             # Student portal UI
├── templates/          # HTML templates
│   └── results/        # Result sheet templates
├── assets/             # CSS, JS, Images
└── school-result-saas.php  # Main plugin file
```

## License

GPL v3 or later - See LICENSE file for details

## Support

For issues and feature requests, visit the [GitHub Issues](https://github.com/orjik1/school-result-saas/issues) page.
