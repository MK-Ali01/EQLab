-- eqlab_schema.sql
-- EQLab Full Database Schema
-- Run this once in phpMyAdmin

CREATE DATABASE IF NOT EXISTS eqlab_db;
USE eqlab_db;

-- ─────────────────────────────────────────
-- USERS
-- ─────────────────────────────────────────
CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,       -- store hashed (password_hash)
    age         INT,
    gender      ENUM('Male', 'Female', 'Other'),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- QUESTIONS (OECD EQ Questionnaire)
-- ─────────────────────────────────────────
CREATE TABLE questions (
    question_id     INT AUTO_INCREMENT PRIMARY KEY,
    question_text   TEXT NOT NULL,
    domain          VARCHAR(50) NOT NULL,   -- e.g., Self-Awareness
    facet           VARCHAR(50) NOT NULL,   -- e.g., Emotional Identification
    is_reverse      TINYINT(1) DEFAULT 0,  -- 1 = reverse scored
    scale_min       INT DEFAULT 1,
    scale_max       INT DEFAULT 5
);

-- ─────────────────────────────────────────
-- ASSESSMENT SESSIONS
-- ─────────────────────────────────────────
CREATE TABLE assessment_sessions (
    session_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    started_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at    DATETIME,
    is_complete     TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ─────────────────────────────────────────
-- RAW RESPONSES (per question, per session)
-- ─────────────────────────────────────────
CREATE TABLE responses (
    response_id     INT AUTO_INCREMENT PRIMARY KEY,
    session_id      INT NOT NULL,
    user_id         INT NOT NULL,
    question_id     INT NOT NULL,
    response_value  INT NOT NULL,           -- 1–5 Likert
    submitted_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(session_id),
    FOREIGN KEY (user_id)    REFERENCES users(user_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

-- ─────────────────────────────────────────
-- COMPUTED SCORES (per session)
-- ─────────────────────────────────────────
CREATE TABLE scores (
    score_id            INT AUTO_INCREMENT PRIMARY KEY,
    session_id          INT NOT NULL,
    user_id             INT NOT NULL,
    domain              VARCHAR(50) NOT NULL,
    facet               VARCHAR(50) NOT NULL,
    raw_score           DECIMAL(5,2),
    normalized_score    DECIMAL(5,2),       -- 0–100 scale
    percentile          DECIMAL(5,2),
    level               ENUM('Low','Average','High'),
    scored_at           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(session_id),
    FOREIGN KEY (user_id)    REFERENCES users(user_id)
);

-- ─────────────────────────────────────────
-- EQ PROFILES (overall profile per session)
-- ─────────────────────────────────────────
CREATE TABLE eq_profiles (
    profile_id          INT AUTO_INCREMENT PRIMARY KEY,
    session_id          INT NOT NULL,
    user_id             INT NOT NULL,
    overall_score       DECIMAL(5,2),
    profile_type        VARCHAR(100),       -- e.g., "Empathetic Leader"
    strengths           TEXT,               -- JSON array as string
    weaknesses          TEXT,               -- JSON array as string
    priority_facets     TEXT,               -- JSON array — feeds recommendation engine
    ai_feedback         TEXT,               -- AI API generated text
    generated_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(session_id),
    FOREIGN KEY (user_id)    REFERENCES users(user_id)
);

-- ─────────────────────────────────────────
-- LESSONS
-- ─────────────────────────────────────────
CREATE TABLE lessons (
    lesson_id       INT AUTO_INCREMENT PRIMARY KEY,
    facet           VARCHAR(50) NOT NULL,
    domain          VARCHAR(50) NOT NULL,
    title           VARCHAR(200) NOT NULL,
    content         TEXT NOT NULL,
    difficulty      ENUM('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
);

-- ─────────────────────────────────────────
-- QUIZZES
-- ─────────────────────────────────────────
CREATE TABLE quizzes (
    quiz_id         INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id       INT NOT NULL,
    question_text   TEXT NOT NULL,
    option_a        VARCHAR(255),
    option_b        VARCHAR(255),
    option_c        VARCHAR(255),
    option_d        VARCHAR(255),
    correct_option  ENUM('A','B','C','D'),
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id)
);

-- ─────────────────────────────────────────
-- PROGRESS LOGS (lesson + quiz completion)
-- ─────────────────────────────────────────
CREATE TABLE progress_logs (
    log_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    lesson_id       INT,
    quiz_id         INT,
    action          ENUM('lesson_started','lesson_completed','quiz_attempted','quiz_passed'),
    score_achieved  DECIMAL(5,2),           -- for quiz attempts
    logged_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(user_id),
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id),
    FOREIGN KEY (quiz_id)   REFERENCES quizzes(quiz_id)
);