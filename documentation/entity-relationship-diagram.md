# Salla Gamification System - Entity Relationship Diagram

The following Entity-Relationship (ER) diagram illustrates the database structure of the Salla Gamification System.

```mermaid
erDiagram
    MISSION ||--o{ TASK : contains
    MISSION ||--o{ REWARD : grants
    MISSION ||--o{ LOCKER : has
    MISSION ||--o{ RULE : has
    MISSION ||--o{ STORE_PROGRESS : tracks
    
    TASK ||--o{ TASK_COMPLETION : tracks
    
    BADGE ||--o{ STORE_BADGE : awarded
    
    STORE ||--o{ STORE_PROGRESS : has
    STORE ||--o{ TASK_COMPLETION : has
    STORE ||--o{ STORE_BADGE : has
    STORE ||--o{ EVENT_LOG : generates
    
    RULE ||--o{ MISSION_RULE : links
    MISSION ||--o{ MISSION_RULE : has
    
    MISSION {
        int id PK
        string title
        string description
        string icon
        timestamp created_at
        timestamp updated_at
    }
    
    TASK {
        int id PK
        int mission_id FK
        string title
        string description
        string icon
        string event_name
        json event_conditions
        timestamp created_at
        timestamp updated_at
    }
    
    TASK_COMPLETION {
        int id PK
        int task_id FK
        int store_id FK
        timestamp completed_at
        timestamp created_at
        timestamp updated_at
    }
    
    STORE_PROGRESS {
        int id PK
        int store_id FK
        int mission_id FK
        int progress
        boolean is_completed
        boolean is_ignored
        timestamp completed_at
        timestamp ignored_at
        timestamp created_at
        timestamp updated_at
    }
    
    REWARD {
        int id PK
        string type
        string value
        string name
        string description
        string icon
        timestamp created_at
        timestamp updated_at
    }
    
    MISSION_REWARD {
        int id PK
        int mission_id FK
        int reward_id FK
        timestamp created_at
        timestamp updated_at
    }
    
    BADGE {
        int id PK
        string name
        string description
        string icon
        timestamp created_at
        timestamp updated_at
    }
    
    STORE_BADGE {
        int id PK
        int store_id FK
        int badge_id FK
        timestamp earned_at
        timestamp created_at
        timestamp updated_at
    }
    
    RULE {
        int id PK
        string name
        string description
        string type
        json conditions
        timestamp created_at
        timestamp updated_at
    }
    
    MISSION_RULE {
        int id PK
        int mission_id FK
        int rule_id FK
        string rule_type
        timestamp created_at
        timestamp updated_at
    }
    
    LOCKER {
        int id PK
        int mission_id FK
        string type
        string value
        timestamp created_at
        timestamp updated_at
    }
    
    EVENT_LOG {
        int id PK
        int store_id FK
        string event_name
        json payload
        timestamp created_at
    }
    
    STORE {
        int id PK
        string name
        string domain
        timestamp created_at
        timestamp updated_at
    }
```

The ER diagram above shows all entities in the Salla Gamification System and their relationships. The system is designed with a multi-tenant architecture, where each merchant (Store) has its own progress, task completions, and badges. The main entities include Mission, Task, Rule, Reward, and Badge. Join tables like MISSION_RULE and MISSION_REWARD enable many-to-many relationships between entities.