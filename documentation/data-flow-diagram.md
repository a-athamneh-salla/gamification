# Salla Gamification System - Data Flow Diagram

The following Data Flow Diagram (DFD) illustrates how data moves through the Salla Gamification System.

## Level 0 DFD (Context Diagram)

```mermaid
flowchart TD
    Merchant([Merchant])
    Admin([Admin])
    Platform([Salla Platform])
    GamSystem[Gamification System]
    Analytics([Analytics System])

    Merchant -->|Performs actions| GamSystem
    GamSystem -->|Progress updates & rewards| Merchant
    Admin -->|Configure tasks & missions| GamSystem
    Platform -->|Events & merchant data| GamSystem
    GamSystem -->|Event completion data| Platform
    GamSystem -->|Usage metrics| Analytics
```

## Level 1 DFD

```mermaid
flowchart TD
    %% External Entities
    Merchant([Merchant])
    Admin([Admin])
    Platform([Salla Platform])
    Analytics([Analytics System])

    %% Processes
    EventProcessing[1.0\nEvent Processing]
    TaskManagement[2.0\nTask Management]
    MissionTracking[3.0\nMission Tracking]
    RewardSystem[4.0\nReward System]
    ProgressTracking[5.0\nProgress Tracking]
    Configuration[6.0\nSystem Configuration]

    %% Data Stores
    EventsDB[(Events DB)]
    TasksDB[(Tasks DB)]
    MissionsDB[(Missions DB)]
    RewardsDB[(Rewards DB)]
    ProgressDB[(Progress DB)]
    ConfigDB[(Configuration DB)]

    %% Connections - External to Process
    Platform -->|Store actions & events| EventProcessing
    Merchant -->|Complete tasks manually| TaskManagement
    Merchant -->|View progress| ProgressTracking
    Merchant -->|Ignore missions| MissionTracking
    Admin -->|Configure system| Configuration
    ProgressTracking -->|Progress updates| Merchant
    RewardSystem -->|Rewards earned| Merchant
    MissionTracking -->|Mission status| Merchant

    %% Process to Process
    EventProcessing -->|Task matches| TaskManagement
    TaskManagement -->|Task completion| MissionTracking
    MissionTracking -->|Mission completion| RewardSystem
    MissionTracking -->|Progress update| ProgressTracking

    %% Process to Data Store
    EventProcessing -->|Log events| EventsDB
    TaskManagement -->|Update task status| TasksDB
    MissionTracking -->|Update mission progress| MissionsDB
    RewardSystem -->|Store rewards given| RewardsDB
    ProgressTracking -->|Store progress data| ProgressDB
    Configuration -->|Store settings| ConfigDB

    %% Data Store to Process
    TasksDB -->|Task rules & conditions| EventProcessing
    TasksDB -->|Task details| TaskManagement
    MissionsDB -->|Mission rules| MissionTracking
    RewardsDB -->|Reward configurations| RewardSystem
    ProgressDB -->|Current progress| ProgressTracking
    ConfigDB -->|System settings| EventProcessing

    %% To Analytics
    EventsDB -->|Event data| Analytics
    ProgressDB -->|Progress metrics| Analytics
```