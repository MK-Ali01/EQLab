[[Claude_fyp_list]]
# 🧩 ✅ **EQLab ERD – 9 Tables Structure**

---

# 🔹 1. USERS

**Purpose:** Stores user account information

**Attributes:**

- 🔑 user_id (PK)
    
- name
    
- email
    
- password
    
- role (student/faculty/admin)
    
- created_at
    

---

# 🔹 2. ASSESSMENTS

**Purpose:** Each test session taken by a user

**Attributes:**

- 🔑 assessment_id (PK)
    
- 🔗 user_id (FK → USERS.user_id)
    
- date_taken
    
- status (completed/incomplete)
    

---

# 🔹 3. QUESTIONS

**Purpose:** Stores all OECD-based questions

**Attributes:**

- 🔑 question_id (PK)
    
- question_text
    
- domain (e.g., Emotional Regulation)
    
- facet (e.g., Stress Tolerance)
    
- weight
    

---

# 🔹 4. RESPONSES

**Purpose:** Stores answers given by users

**Attributes:**

- 🔑 response_id (PK)
    
- 🔗 assessment_id (FK → ASSESSMENTS.assessment_id)
    
- 🔗 question_id (FK → QUESTIONS.question_id)
    
- answer_value (1–5 Likert scale)
    

---

# 🔹 5. SCORES

**Purpose:** Stores computed scores per domain

**Attributes:**

- 🔑 score_id (PK)
    
- 🔗 assessment_id (FK → ASSESSMENTS.assessment_id)
    
- domain
    
- raw_score
    
- normalized_score
    
- percentile
    

---

# 🔹 6. PROFILES

**Purpose:** Final EQ profile summary

**Attributes:**

- 🔑 profile_id (PK)
    
- 🔗 user_id (FK → USERS.user_id)
    
- overall_eq_score
    
- strength_area
    
- weakness_area
    
- created_at
    

---

# 🔹 7. LESSONS

**Purpose:** Stores learning content

**Attributes:**

- 🔑 lesson_id (PK)
    
- domain
    
- title
    
- content
    
- difficulty_level
    

---

# 🔹 8. QUIZZES

**Purpose:** Quiz linked to lessons

**Attributes:**

- 🔑 quiz_id (PK)
    
- 🔗 lesson_id (FK → LESSONS.lesson_id)
    
- question_text
    
- correct_answer
    

---

# 🔹 9. USER_PROGRESS

**Purpose:** Tracks learning progress

**Attributes:**

- 🔑 progress_id (PK)
    
- 🔗 user_id (FK → USERS.user_id)
    
- 🔗 lesson_id (FK → LESSONS.lesson_id)
    
- completion_status
    
- score
    
- last_updated
    

---

# 🔗 ✅ RELATIONSHIPS (VERY IMPORTANT FOR DRAWING)

---

### 1. USERS → ASSESSMENTS

**1 : Many**  
One user can take many assessments

---

### 2. ASSESSMENTS → RESPONSES

**1 : Many**  
Each assessment has multiple responses

---

### 3. QUESTIONS → RESPONSES

**1 : Many**  
Each question is answered multiple times

---

### 4. ASSESSMENTS → SCORES

**1 : Many**  
Each assessment generates multiple domain scores

---

### 5. USERS → PROFILES

**1 : 1 (or 1 : Many)**  
One user has one or multiple profiles over time

---

### 6. LESSONS → QUIZZES

**1 : Many**  
Each lesson contains multiple quiz questions

---

### 7. USERS → USER_PROGRESS

**1 : Many**  
Tracks progress across lessons

---

### 8. LESSONS → USER_PROGRESS

**1 : Many**  
Each lesson is tracked for multiple users

---

# 🎯 HOW TO DRAW (VERY IMPORTANT)

When you draw in draw.io:

### Step 1:

Draw 9 rectangles (tables)

### Step 2:

Mark:

- PK at top
    
- FK with arrows
    

### Step 3:

Connect with:

- Straight line for 1–1
    
- Crow’s foot for 1–Many
    

---

# 📌 VISUAL FLOW (MENTAL MAP)

```id="erd-flow"
USERS
  ↓
ASSESSMENTS
  ↓
RESPONSES ← QUESTIONS
  ↓
SCORES

USERS → PROFILES

USERS → USER_PROGRESS ← LESSONS → QUIZZES
```

---

# 🏆 WHY THIS ERD IS STRONG (For Panel)

✔ Covers full system (Assessment → AI → Learning)  
✔ Normalized database  
✔ Scalable for future ML  
✔ Clear separation of modules  
✔ Supports analytics + personalization

---

