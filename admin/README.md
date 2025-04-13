# Salla Gamification Admin Dashboard

This is the administration panel for the Salla Gamification System, built as a React micro-frontend application integrated with Salla's main admin interface using Single-Spa architecture.

## Overview

The Gamification Admin Dashboard provides a comprehensive interface for managing all aspects of the gamification system, including:

- Tasks and missions configuration
- Rules engine management
- Reward system administration
- Analytics and reporting
- Merchant progress tracking
- Leaderboard visualization

## Project Structure

```
admin/
├── App.js                # Main application component and route definitions
├── App.css               # Global application styles
├── index.js              # Entry point with Single-Spa integration
├── components/           # Shared UI components
│   ├── Layout.js         # Main layout with navigation sidebar
│   └── Layout.css        # Layout styles
├── hooks/                # Custom React hooks
├── pages/                # Page components organized by domain
│   ├── Dashboard.js      # Main dashboard
│   ├── NotFound.js       # 404 page
│   ├── tasks/            # Task management pages
│   ├── missions/         # Mission management pages
│   └── rules/            # Rule management pages
├── services/             
│   └── api.js            # API client and endpoints
└── utils/                # Utility functions
```

## Technology Stack

- **React**: UI library for building the interface
- **React Router**: For navigation and routing
- **Axios**: HTTP client for API communications
- **Single-Spa**: For micro-frontend architecture integration
- **CSS**: For styling components

## Prerequisites

- Node.js (v14+)
- npm or yarn
- Access to the Salla platform backend API

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd packages/gamification
```

2. Install dependencies:
```bash
npm install
# or using yarn
yarn install
```

3. Create a `.env` file with necessary environment variables:
```bash
cp .env.example .env
```

4. Configure the API endpoint in the `.env` file:
```
REACT_APP_API_URL=/api/gamification
```

## Development

To run the development server:

```bash
npm run start
# or using yarn
yarn start
```

This will start the development server at [http://localhost:3000](http://localhost:3000).

### Development Within Salla

For development within the Salla platform:

1. Build the micro-frontend:
```bash
npm run build
```

2. Configure the Salla admin to load your local build by updating the Single-Spa import map.

## Building for Production

```bash
npm run build
# or using yarn
yarn build
```

The build artifacts will be stored in the `build/` directory, ready for deployment.

## Features

### Dashboard

The dashboard provides an overview of:
- Merchant activity
- Task completion rates
- Mission progression statistics
- Recent reward distributions

### Task Management

- Create, edit, and delete tasks
- Configure completion criteria
- Set point values and rewards
- Organize tasks by categories

### Mission Management

- Create multi-step missions
- Add tasks to missions
- Set mission prerequisites
- Configure rewards for mission completion

### Rules Engine

- Define trigger conditions
- Create action sequences
- Set up conditional logic
- Test rule execution

### Rewards Configuration

- Create different reward types
- Configure reward distribution
- Set up tiered reward structures
- Link rewards to tasks and missions

### Analytics

- View merchant progress
- Analyze completion rates
- Track engagement metrics
- Generate performance reports

## API Integration

The admin panel communicates with the backend via RESTful API endpoints defined in `services/api.js`. All API requests include CSRF token for Laravel authentication.

Main API modules:

- `taskApi`: Manage gamification tasks
- `missionApi`: Handle mission configuration
- `ruleApi`: Control the rules engine
- `rewardApi`: Configure the reward system
- `analyticsApi`: Retrieve analytics data

## Deployment

The application is designed to be deployed as part of the Salla platform. The build output can be integrated with the main Salla admin application through Single-Spa module federation.

## Contributing

1. Create a feature branch from `develop`
2. Make your changes
3. Submit a pull request

## Troubleshooting

### Common Issues

1. **API Connection Failed**: Ensure the backend server is running and accessible
2. **CSRF Token Error**: Check that the CSRF token is properly configured
3. **Single-Spa Integration Issues**: Verify import map configuration

## License

This project is proprietary and confidential. Unauthorized copying, transfer, or reproduction of the contents is strictly prohibited.

## Contact

For support or questions, contact the Salla development team.