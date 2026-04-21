USE eqlab_db;

INSERT INTO questions (question_text, domain, facet, is_reverse, scale_min, scale_max) VALUES

-- SELF-AWARENESS → Emotional Identification
('I can easily identify what emotion I am feeling at any moment.', 'Self-Awareness', 'Emotional Identification', 0, 1, 5),
('I often feel confused about my own emotions.', 'Self-Awareness', 'Emotional Identification', 1, 1, 5),
('I notice changes in my mood before others do.', 'Self-Awareness', 'Emotional Identification', 0, 1, 5),

-- SELF-AWARENESS → Emotional Understanding
('I understand why I feel the way I do in difficult situations.', 'Self-Awareness', 'Emotional Understanding', 0, 1, 5),
('My emotions sometimes surprise me and I cannot explain them.', 'Self-Awareness', 'Emotional Understanding', 1, 1, 5),
('I can trace my feelings back to specific events or thoughts.', 'Self-Awareness', 'Emotional Understanding', 0, 1, 5),

-- SELF-AWARENESS → Emotional Expression
('I find it easy to express how I am feeling to others.', 'Self-Awareness', 'Emotional Expression', 0, 1, 5),
('I keep my emotions to myself even when it would help to share them.', 'Self-Awareness', 'Emotional Expression', 1, 1, 5),
('I can describe my feelings clearly when asked.', 'Self-Awareness', 'Emotional Expression', 0, 1, 5),

-- SELF-MANAGEMENT → Emotional Regulation
('I can calm myself down quickly when I feel stressed or angry.', 'Self-Management', 'Emotional Regulation', 0, 1, 5),
('My emotions often get out of control in tense situations.', 'Self-Management', 'Emotional Regulation', 1, 1, 5),
('I use specific strategies to manage my emotions effectively.', 'Self-Management', 'Emotional Regulation', 0, 1, 5),

-- SELF-MANAGEMENT → Stress Management
('I handle pressure and deadlines without becoming overwhelmed.', 'Self-Management', 'Stress Management', 0, 1, 5),
('I find it hard to function normally when I am under stress.', 'Self-Management', 'Stress Management', 1, 1, 5),
('I recover quickly after stressful events.', 'Self-Management', 'Stress Management', 0, 1, 5),

-- SELF-MANAGEMENT → Impulse Control
('I think before I act, even when I feel a strong urge to react.', 'Self-Management', 'Impulse Control', 0, 1, 5),
('I often say or do things impulsively and regret them later.', 'Self-Management', 'Impulse Control', 1, 1, 5),
('I can delay gratification when working toward a long-term goal.', 'Self-Management', 'Impulse Control', 0, 1, 5),

-- SOCIAL AWARENESS → Empathy
('I can sense how someone is feeling even before they say anything.', 'Social Awareness', 'Empathy', 0, 1, 5),
('I find it difficult to understand why people react emotionally.', 'Social Awareness', 'Empathy', 1, 1, 5),
('I genuinely care about the emotional wellbeing of people around me.', 'Social Awareness', 'Empathy', 0, 1, 5),

-- SOCIAL AWARENESS → Social Perception
('I pick up on social cues and unspoken dynamics in group settings.', 'Social Awareness', 'Social Perception', 0, 1, 5),
('I often miss subtle hints in conversations.', 'Social Awareness', 'Social Perception', 1, 1, 5),
('I can read the mood of a room quickly when I walk in.', 'Social Awareness', 'Social Perception', 0, 1, 5),

-- SOCIAL AWARENESS → Organizational Awareness
('I understand the informal power structures in groups I belong to.', 'Social Awareness', 'Organizational Awareness', 0, 1, 5),
('I am often unaware of the political dynamics in group situations.', 'Social Awareness', 'Organizational Awareness', 1, 1, 5),
('I know how to navigate group dynamics to get things done.', 'Social Awareness', 'Organizational Awareness', 0, 1, 5),

-- RELATIONSHIP MANAGEMENT → Conflict Resolution
('I can resolve disagreements without damaging relationships.', 'Relationship Management', 'Conflict Resolution', 0, 1, 5),
('Conflicts make me so uncomfortable that I avoid addressing them.', 'Relationship Management', 'Conflict Resolution', 1, 1, 5),
('I help others find common ground during disagreements.', 'Relationship Management', 'Conflict Resolution', 0, 1, 5),

-- RELATIONSHIP MANAGEMENT → Collaboration
('I work effectively with people who have very different personalities.', 'Relationship Management', 'Collaboration', 0, 1, 5),
('I prefer to work alone because group work creates unnecessary friction.', 'Relationship Management', 'Collaboration', 1, 1, 5),
('I actively contribute to building a positive team atmosphere.', 'Relationship Management', 'Collaboration', 0, 1, 5),

-- RELATIONSHIP MANAGEMENT → Influence
('I can motivate others to take action without using authority.', 'Relationship Management', 'Influence', 0, 1, 5),
('I struggle to persuade others even when I know I am right.', 'Relationship Management', 'Influence', 1, 1, 5),
('People often look to me for direction in uncertain situations.', 'Relationship Management', 'Influence', 0, 1, 5);