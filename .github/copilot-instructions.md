# GitHub Copilot Instructions for Salla Gamification System

## Project Context

Salla Gamification System is a multi-tenant gamification engine designed to enhance merchant onboarding in Salla's e-commerce platform. It guides merchants through setup tasks and rewards their progress.

### Core Purpose

- Increase merchant activation and engagement
- Accelerate platform adoption
- Create a fun and goal-driven onboarding experience

## Technology Stack

### Backend

- **Framework**: Laravel PHP framework
- **Gamification Engine**: [level-up package](https://github.com/cjmellor/level-up) integration
- **Database**:
   - MySQL for relational data and mission progression
   - Redis for caching and real-time state
- **Analytics**:
   - ClickHouse for efficient event analytics
   - Jitsu for event tracking (following Segment's naming conventions)
- **Search**: Elasticsearch for indexing and search functionality

### Frontend

- **Framework**: React
- **Architecture**: SingleSpa micro-frontend architecture
- **Component Style**: Web components for task display and progress tracking

## Domain Model & Terminology

### Key Entities

- **Tasks**: Individual actions merchants need to complete (e.g., "Add First Product", "Set Store Logo")
- **Missions**: Collections of related tasks with overall progress tracking
- **Rules**: Conditions that determine when missions start or complete
- **Rewards**: Points, badges, or coupons granted upon mission completion
- **Lockers**: Conditions that keep missions locked until prerequisites are met

### Entity Relationships

- One mission has many tasks (one-to-many)
- Missions can have lockers that determine visibility based on other mission completions
- Rules govern the starting and finishing conditions for missions

## Architecture Considerations

### Multi-Tenant Design

- Every merchant's data is scoped by a unique Store(Tenant) ID
- All queries must be tenant-aware to ensure data isolation

### Event-Driven Architecture

- Task completion tracked through platform events
- Response time for event processing should be < 1 second

### Configuration

- Runtime configuration of tasks and rewards without redeployment
- Configuration updates must propagate in configured time (respect caching)

## Development Guidelines

### Code Structure

- Follow Laravel best practices and coding standards
- Use Eloquent ORM for database interactions
- Implement proper multi-tenant data isolation
- Use type hinting and thorough documentation

### Frontend Development

- Structure React components following SingleSpa guidelines
- Ensure components are modular and reusable across the platform
- Optimize for real-time progress updates

### Performance Requirements

- System must support all merchants concurrently
- Queries should be optimized for multi-tenant environments
- Use caching strategies appropriately for configuration and progress data

## Implementation Phases

1. **MVP**: Logic engine (Task, Mission, Rule), Event integration, Reward logic and progress DB, Merchant UI component
2. **Admin Panel & Analytics**: Config UI for tasks, Funnel analytics and reporting dashboard
3. **Gamified Enhancements**: Badges, tiers, additional missions, Seasonal campaigns and limited-time quests

## Success Metrics

- Onboarding task completion rate > 60%
- Increase in 7-day merchant retention
- 10% boost in setup completeness within first week