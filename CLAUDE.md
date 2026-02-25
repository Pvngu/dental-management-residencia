# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **800dent**, a Laravel-based dental practice management system with a Vue.js frontend. It's a multi-tenant application that manages dental clinics with features for patient management, appointments, inventory, billing, and comprehensive dental charting.

## Development Commands

### Backend (Laravel)
- `composer install` - Install PHP dependencies
- `php artisan serve` - Start Laravel development server
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed database with sample data
- `php artisan queue:work` - Process background jobs
- `php artisan pail` - View application logs in real-time
- `php artisan test` - Run PHPUnit tests
- `php artisan pint` - Run Laravel Pint code formatter

### Frontend (Vue.js + Vite)
- `npm install` - Install JavaScript dependencies
- `npm run dev` - Start Vite development server with HMR
- `npm run build` - Build for production
- `vite build` - Alternative build command

### Combined Development
- `composer run dev` - Start all services concurrently (Laravel server, queue worker, logs, and Vite dev server)

### Testing
- `php artisan test` - Run backend tests
- `./vendor/bin/phpunit` - Alternative test runner

## Architecture Overview

### Backend Structure (Laravel)
- **Multi-tenant architecture** with company-based data isolation using `CompanyScope`
- **API-first design** using examyou/rest-api package for standardized REST endpoints
- **Modular controller structure** with separate request validation classes
- **Observer pattern** for model lifecycle events
- **Job queues** for background processing (questionnaires, notifications)

### Frontend Structure (Vue.js)
The frontend is split into multiple applications:

#### Main Application (`resources/js/main/`)
- Admin dashboard for dental practice management
- Uses Ant Design Vue components
- Vue 3 with Composition API
- Vuex for state management
- Vue Router for navigation

#### Landing Pages (`resources/js/landing/`)
- Public-facing marketing pages
- Company-specific landing pages
- Separate build target from main app

#### SuperAdmin (`resources/js/superadmin/`)
- Multi-tenant management interface
- Subscription and payment handling
- Global system administration

### Key Architectural Patterns

#### Models
- All models extend `BaseModel` with hashable IDs
- Use `CompanyScope` for multi-tenancy
- Hidden database IDs, exposed hashable `xid` fields
- Standardized `$fillable`, `$hidden`, and `$appends` properties

#### Controllers
- Extend `ApiBaseController` for standardized CRUD operations
- Use `CompanyTraits` for multi-tenant data access
- Implement `modifyIndex()` for custom query modifications
- Request validation handled by dedicated request classes

#### Frontend Components
- Standardized CRUD pattern with `fields.js`, `AddEdit.vue`, and `index.vue`
- Reusable components in `resources/js/common/`
- Composables for shared logic (`crud.js`, `apiAdmin.js`, `common.js`)

## Key Directories and Files

### Backend
- `app/Models/` - Eloquent models with multi-tenant patterns
- `app/Http/Controllers/Api/` - REST API controllers
- `app/Http/Requests/Api/` - Request validation classes
- `app/Classes/` - Utility classes (permissions, translations, common functions)
- `app/SuperAdmin/` - Multi-tenant management functionality
- `database/migrations/` - Database schema
- `database/seeders/` - Data seeding

### Frontend
- `resources/js/main/views/` - Admin interface views
- `resources/js/common/` - Shared components and utilities
- `resources/js/main/router/` - Route definitions
- `resources/js/common/composable/` - Vue composables for shared logic

### Configuration
- `vite.config.js` - Build configuration with chunk splitting
- `.github/copilot-instructions.md` - Detailed development patterns and conventions
- `composer.json` - PHP dependencies and scripts
- `package.json` - JavaScript dependencies and build scripts

## Dental Chart System

The application includes a sophisticated dental charting system:
- **Tooth-based data structure** with surface-level detail
- **Real-time image caching** for dental imagery
- **Component-based architecture** for different chart types (periodontal, restorative, pathology)
- **Section-based saving** system for efficient data persistence

## Multi-tenancy

The system implements company-based multi-tenancy:
- All data is scoped to companies using `CompanyScope`
- Models use `company_id` foreign keys
- Frontend automatically filters data by authenticated user's company
- SuperAdmin interface manages multiple companies

## Development Conventions

### Model Creation
- Extend `BaseModel`
- Include `CompanyScope` for multi-tenant models
- Use hashable IDs with `Hash` cast
- Define `$filterable` arrays for search functionality

### Controller Creation
- Extend `ApiBaseController`
- Use `CompanyTraits` for multi-tenant controllers
- Implement custom query logic in `modifyIndex()`
- Create corresponding request validation classes

### Frontend Development
- Follow the standardized CRUD pattern
- Use Ant Design Vue components consistently
- Implement search and filtering using `Filters.vue`
- Utilize composables for shared functionality

### Testing
- Write feature tests for API endpoints
- Test multi-tenant data isolation
- Use factories for test data generation

## Important Notes

- The system uses hashable IDs (xid) for frontend/API communication instead of database IDs
- All multi-tenant models must include `CompanyScope`
- Foreign key validation uses `ValidForeignKey` rule instead of standard Laravel exists rule
- The build process separates main app, landing pages, and superadmin into different chunks
- Queue workers are required for background processing (questionnaires, notifications)