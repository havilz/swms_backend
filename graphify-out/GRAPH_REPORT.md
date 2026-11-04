# Graph Report - swms_backend  (2026-11-04)

## Corpus Check
- Corpus is ~21,292 words - fits in a single context window. You may not need a graph.

## Summary
- 462 nodes · 671 edges · 45 communities (22 shown, 23 thin omitted)
- Graph health warning: 2 AST edges have missing targets; 27 edges with repeated endpoint pairs were collapsed during graph construction.
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 18 edges (avg confidence: 0.85)
- Semantic extraction token usage unavailable (agent tool did not expose per-call usage; Graphify recorded 0).

## Community Hubs (Navigation)
- Report Management
- User Management
- Database Migrations
- HTTP Middleware
- Authentication Services
- Request Validation
- Model Factories
- CI Workflow Steps
- Container Publishing
- Feature Test Coverage
- Frontend Asset Pipeline
- Mobile API Overview
- Composer Project Metadata
- Development Dependencies
- Composer Lifecycle Scripts
- Report API Workflow
- Production Deployment
- Composer Configuration
- Graphify Knowledge Tools
- Namespace Autoloading
- Laravel Dependencies
- Unit Test Example
- Test Namespace
- Laravel Package Discovery
- Architecture Principles
- Dashboard Blade View
- Report List View
- Report Detail View
- Report Create View
- Report Edit View
- User List View

## God Nodes (most connected - your core abstractions)
1. `User` - 28 edges
2. `Report` - 23 edges
3. `with` - 21 edges
4. `ReportService` - 18 edges
5. `SwmsFeatureTest` - 15 edges
6. `Controller` - 13 edges
7. `AuthService` - 12 edges
8. `UserController` - 11 edges
9. `AuthController` - 9 edges
10. `ReportController` - 9 edges

## Surprising Connections (you probably didn't know these)
- `Mobile API` --references--> `Mobile API Contract`  [EXTRACTED]
  README.md → docs/API_DOCUMENTATION.md
- `Report Status Workflow` --references--> `Admin Dashboard`  [EXTRACTED]
  docs/API_DOCUMENTATION.md → README.md
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/Auth/AuthController.php → app/Http/Controllers/Controller.php
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Web/Admin/AuthController.php → app/Http/Controllers/Controller.php
- `UserController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Web/Admin/UserController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Endpoints in the mobile authentication flow** — docs_api_documentation_user_registration, docs_api_documentation_user_login, docs_api_documentation_current_user_profile_endpoint, docs_api_documentation_token_logout_endpoint [EXTRACTED 1.00]

## Communities (45 total, 23 thin omitted)

### Community 0 - "Report Management"
Cohesion: 0.07
Nodes (9): ReportController, Controller, DashboardController, ReportController, AdminReportIndexRequest, ApiReportIndexRequest, Report, {closure#1}() (+1 more)

### Community 1 - "User Management"
Cohesion: 0.10
Nodes (4): UserController, User, UserService, DatabaseSeeder

### Community 2 - "Database Migrations"
Cohesion: 0.12
Nodes (10): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+2 more)

### Community 3 - "HTTP Middleware"
Cohesion: 0.12
Nodes (7): EnsureIsAdmin, EnsureIsUser, AppServiceProvider, {closure#1}(), {closure#1}(), {closure#3}(), {closure#4}()

### Community 4 - "Authentication Services"
Cohesion: 0.14
Nodes (4): AuthController, AuthController, LoginRequest, AuthService

### Community 5 - "Request Validation"
Cohesion: 0.12
Nodes (5): RegisterRequest, StoreReportRequest, UpdateReportStatusRequest, StoreUserRequest, UpdateUserRequest

### Community 7 - "CI Workflow Steps"
Cohesion: 0.08
Nodes (26): steps, id, name, run, uses, with, steps, cache (+18 more)

### Community 8 - "Container Publishing"
Cohesion: 0.09
Nodes (23): name, needs, permissions, runs-on, env, APP_ENV, APP_KEY, IMAGE_NAME (+15 more)

### Community 9 - "Feature Test Coverage"
Cohesion: 0.11
Nodes (3): ExampleTest, SwmsFeatureTest, TestCase

### Community 10 - "Frontend Asset Pipeline"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 11 - "Mobile API Overview"
Cohesion: 0.13
Nodes (18): API Authentication Rate Limit, Mobile API Contract, API Error Responses, Mobile Authentication Flow, API Contract Tests, Current User Profile Endpoint, Eloquent API Resource, Mobile API Limitations (+10 more)

### Community 13 - "Composer Project Metadata"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 14 - "Development Dependencies"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/boost, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 15 - "Composer Lifecycle Scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 16 - "Report API Workflow"
Cohesion: 0.25
Nodes (8): Image Path Limitation, Report Collection Endpoint, Report Creation Endpoint, Report Detail Endpoint, Report Image Upload Constraints, Report Status Workflow, Admin Dashboard, Report Status Workflow

### Community 17 - "Production Deployment"
Cohesion: 0.25
Nodes (6): BuildKit Provenance and SBOM, GitHub Actions CI/CD Workflow, GitHub Actions Token Permissions, GitHub Container Registry, Release-time Database Migration, Render Runtime Configuration

### Community 18 - "Composer Configuration"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 19 - "Graphify Knowledge Tools"
Cohesion: 0.40
Nodes (5): Graphify Explain, Graphify Knowledge Graph, Graphify Path, Graphify Query, Graphify Wiki

### Community 20 - "Namespace Autoloading"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 21 - "Laravel Dependencies"
Cohesion: 0.40
Nodes (5): require, laravel/framework, laravel/sanctum, laravel/tinker, php

### Community 27 - "Test Namespace"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 28 - "Laravel Package Discovery"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **27 isolated node(s):** `layouts.admin`, `layouts.admin`, `layouts.admin`, `layouts.admin`, `layouts.admin` (+22 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 209 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **23 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Management` to `Report Management`, `Authentication Services`, `Request Validation`, `Model Factories`, `Feature Test Coverage`?**
  _High betweenness centrality (0.074) - this node is a cross-community bridge._
- **What connects `layouts.admin`, `layouts.admin`, `layouts.admin` to the rest of the system?**
  _27 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Report Management` be split into smaller, more focused modules?**
  _Cohesion score 0.06894049346879536 - nodes in this community are weakly interconnected._
- **Why does `Report` connect `Report Management` to `User Management`, `Authentication Services`, `Request Validation`, `Model Factories`, `Feature Test Coverage`?**
  _High betweenness centrality (0.059) - this node is a cross-community bridge._
- **Should `User Management` be split into smaller, more focused modules?**
  _Cohesion score 0.0957983193277311 - nodes in this community are weakly interconnected._
- **Should `Database Migrations` be split into smaller, more focused modules?**
  _Cohesion score 0.11904761904761904 - nodes in this community are weakly interconnected._
- **Should `HTTP Middleware` be split into smaller, more focused modules?**
  _Cohesion score 0.1225071225071225 - nodes in this community are weakly interconnected._
