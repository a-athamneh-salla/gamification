# Salla Gamification System - Class Diagram

The following diagram illustrates the class structure and relationships within the Salla Gamification System.

```mermaid
classDiagram
    class Mission {
        +int id
        +string title
        +string description
        +string icon
        +int progress
        +boolean is_completed
        +boolean is_locked
        +created_at
        +updated_at
        +getProgress()
        +isCompleted()
        +isLocked()
    }
    
    class Task {
        +int id
        +string title
        +string description
        +string icon
        +string event_name
        +json event_conditions
        +created_at
        +updated_at
        +complete()
        +isCompleted()
    }
    
    class TaskCompletion {
        +int id
        +int task_id
        +int store_id
        +datetime completed_at
        +created_at
        +updated_at
    }
    
    class Rule {
        +int id
        +string name
        +string description
        +string type
        +json conditions
        +created_at
        +updated_at
        +evaluate(event, store_id)
    }
    
    class Reward {
        +int id
        +string type
        +string value
        +string name
        +string description
        +string icon
        +created_at
        +updated_at
        +award(store_id)
    }
    
    class Badge {
        +int id
        +string name
        +string description
        +string icon
        +created_at
        +updated_at
    }
    
    class StoreBadge {
        +int id
        +int store_id
        +int badge_id
        +datetime earned_at
        +created_at
        +updated_at
    }
    
    class Locker {
        +int id
        +int mission_id
        +string type
        +string value
        +created_at
        +updated_at
        +isUnlocked(store_id)
    }
    
    class StoreProgress {
        +int id
        +int store_id
        +int mission_id
        +int progress
        +boolean is_completed
        +boolean is_ignored
        +datetime completed_at
        +datetime ignored_at
        +created_at
        +updated_at
    }
    
    class EventLog {
        +int id
        +int store_id
        +string event_name
        +json payload
        +created_at
    }
    
    class GamificationService {
        +handleEvent(event_name, store_id, payload)
        +getProgressSummary(store_id)
        +completeMission(mission_id, store_id)
        +completeTask(task_id, store_id)
        +ignoreMission(mission_id, store_id)
        +getAvailableMissions(store_id)
    }
    
    Mission "1" --> "*" Task : has
    Mission "1" --> "*" Rule : has start/finish rules
    Mission "1" --> "*" Reward : grants
    Mission "1" --> "*" Locker : has
    Mission "1" --> "*" StoreProgress : tracks progress per store
    
    Task "1" --> "*" TaskCompletion : tracks completion per store
    
    Badge "1" --> "*" StoreBadge : awarded to stores
    
    GamificationService --> Mission : manages
    GamificationService --> Task : manages
    GamificationService --> Rule : evaluates
    GamificationService --> Reward : awards
    GamificationService --> EventLog : processes
```

This class diagram represents the core entities and their relationships in the Salla Gamification System. The main entity is `Mission`, which contains multiple `Task` objects. Each `Mission` can have start and finish `Rule` objects that determine when the mission becomes available and when it's completed. `Reward` objects are granted upon mission completion, and `Locker` objects can keep missions locked until certain conditions are met. The system also tracks progress for each store (tenant) using the `StoreProgress` and `TaskCompletion` objects.