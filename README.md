# FitTrack: Hybrid Multi-Tenant Wellbeing Application

## Overview
FitTrack is a comprehensive physical wellbeing tracking system designed for multi-tenant use. Built entirely with vanilla technologies, it demonstrates advanced architectural patterns without relying on modern frameworks. 

The application utilises a hybrid architecture:
- **Multi-Page Application (MPA):** Handles authentication, tenant onboarding, and administrative CRUD operations via traditional server-side rendering.
- **Single Page Application (SPA):** Powers the user dashboard, providing asynchronous data visualisation, real-time progress tracking, and dynamic state management via the Fetch API.

## Technology Stack
- **Backend:** Vanilla PHP 8.x (PDO, strict typing, MVC-inspired structure)
- **Frontend:** Vanilla JavaScript (ES6+), HTML5, CSS3 (Custom properties, animations)
- **Database:** MariaDB 11.4
- **Infrastructure:** Docker & Docker Compose (LAMP stack replication)
- **Visualisation:** Native SVG generation and manipulation

## Key Features
- **Security-First Design:** Prepared statements, CSRF token validation, XSS prevention, and custom rate-limiting logic.
- **Data Visualisation:** Hand-coded SVG progress rings and line graphs for workout analytics.
- **Audit Logging:** Comprehensive tracking of user actions and security events.
- **Responsive Design:** Apple-inspired UI focusing on typography, whitespace, and subtle CSS transitions.

## Local Development Setup

### Prerequisites
- Docker & Docker Compose installed.

### Installation
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd Web_App_Dev