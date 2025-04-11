# Salla Gamification System - Sequence Diagram

The following sequence diagram illustrates the process of task completion and mission progression within the Salla Gamification System.

## Task Completion Flow

```mermaid
sequenceDiagram
    participant Merchant as Merchant
    participant Frontend as Frontend App
    participant API as API Layer
    participant GS as GamificationService
    participant ES as Event System
    participant DB as Database

    Merchant->>Frontend: Performs an action (e.g., adds product)
    Frontend->>API: Sends platform event
    API->>ES: Dispatches event
    ES->>GS: Triggers event handler
    GS->>DB: Checks applicable tasks
    DB-->>GS: Returns matching tasks
    GS->>GS: Evaluates task conditions
    GS->>DB: Updates task completion status
    GS->>GS: Calculates mission progress
    GS->>DB: Updates mission progress
    GS->>GS: Checks if mission completed
    alt Mission Completed
        GS->>GS: Processes rewards
        GS->>DB: Records mission completion
        GS->>DB: Awards rewards to merchant
        GS->>API: Returns mission completion details
        API-->>Frontend: Notifies of mission completion
        Frontend-->>Merchant: Shows celebration & rewards
    else Mission In Progress
        GS->>API: Returns updated progress
        API-->>Frontend: Updates progress UI
        Frontend-->>Merchant: Shows updated progress
    end
```

## Manual Task Completion

```mermaid
sequenceDiagram
    participant Merchant as Merchant
    participant Frontend as Frontend App
    participant API as API Layer
    participant GS as GamificationService
    participant DB as Database

    Merchant->>Frontend: Manually completes task
    Frontend->>API: POST /tasks/{id}/complete
    API->>GS: Calls completeTask(task_id, store_id)
    GS->>DB: Updates task completion
    GS->>DB: Updates mission progress
    GS->>API: Returns updated status
    API-->>Frontend: Returns success response
    Frontend-->>Merchant: Shows updated progress
```

## Mission Ignore Flow

```mermaid
sequenceDiagram
    participant Merchant as Merchant
    participant Frontend as Frontend App
    participant API as API Layer
    participant GS as GamificationService
    participant DB as Database

    Merchant->>Frontend: Chooses to ignore mission
    Frontend->>API: POST /missions/{id}/ignore
    API->>GS: Calls ignoreMission(mission_id, store_id)
    GS->>DB: Marks mission as ignored
    GS->>DB: Updates mission list
    GS->>API: Returns updated mission list
    API-->>Frontend: Returns success response
    Frontend-->>Merchant: Shows updated missions
```