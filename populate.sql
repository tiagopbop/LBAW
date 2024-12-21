-- Populate authenticated_user table
INSERT INTO authenticated_user (id, username, email, password, user_creation_date, suspended_status, pfp, pronouns, bio, country)
VALUES
    (1, 'john_doe', 'john@example.com', 'password123', CURRENT_DATE, FALSE, 'profile1.jpg', 'he/him', 'Software Developer', 'USA'),
    (2, 'jane_smith', 'jane@example.com', 'password456', CURRENT_DATE, FALSE, 'profile2.jpg', 'she/her', 'Project Manager', 'UK'),
    (3, 'alice_wong', 'alice@example.com', 'password789', CURRENT_DATE, TRUE, 'profile3.jpg', 'they/them', 'Designer', 'Canada');

-- Populate admin table
INSERT INTO admin (admin_tag, admin_username, password)
VALUES
    ('admin01', 'admin_john', 'adminpass1'),
    ('admin02', 'admin_jane', 'adminpass2');

-- Populate notifications table
INSERT INTO notifications (title, content, created_at)
VALUES
    ('Project Update', 'New changes have been made to your project.', CURRENT_DATE),
    ('Task Assignment', 'You have been assigned a new task.', CURRENT_DATE),
    ('Reminder', 'Your task deadline is approaching.', CURRENT_DATE);

-- Populate project table
INSERT INTO project (project_id, availability, project_creation_date, archived_status, project_title, project_description)
VALUES
    (1, TRUE, CURRENT_DATE, FALSE, 'Ovelha', 'Branco'),
    (2, TRUE, CURRENT_DATE, FALSE, 'Abelha', 'Amarelo'),
    (3, FALSE, CURRENT_DATE, TRUE, 'Àrvore', 'Castanho');

-- Populate task table
INSERT INTO task (project_id, task_name, status, details, due_date, priority, created_at, updated_at)
VALUES
    (1, 'Design Homepage', 'Ongoing', 'Create the homepage layout', CURRENT_DATE + INTERVAL '5 days', 'High', CURRENT_DATE, CURRENT_DATE),
    (2, 'Develop Backend', 'On-hold', 'Set up the backend structure', CURRENT_DATE + INTERVAL '10 days', 'Medium', CURRENT_DATE, CURRENT_DATE),
    (3, 'Testing', 'Finished', 'Test the application thoroughly', CURRENT_DATE + INTERVAL '15 days', 'Low', CURRENT_DATE, CURRENT_DATE);

-- Populate task_comments table
INSERT INTO task_comments (id, task_id, comment, created_at)
VALUES
    (1, 1, 'Started working on the homepage design.', CURRENT_DATE),
    (2, 1, 'Initial layout created.', CURRENT_DATE),
    (2, 2, 'Backend setup is pending.', CURRENT_DATE);

-- Populate project_member table
INSERT INTO project_member (id, project_id, "role")
VALUES
    (1, 1, 'Project manager'),
    (2, 1, 'Project member'),
    (3, 2, 'Project owner');

-- Populate post table
INSERT INTO post (project_id, id, content, post_creation)
VALUES
    (1, 1, 'Kick-off meeting scheduled for tomorrow.', CURRENT_DATE),
    (2, 2, 'Project scope finalized.', CURRENT_DATE);

-- Populate reply table
INSERT INTO reply (id, post_id, content, reply_creation)
VALUES
    (2, 1, 'Looking forward to the meeting!', CURRENT_DATE),
    (3, 2, 'Great, let’s get started!', CURRENT_DATE);

-- Populate favourited table
INSERT INTO favourited (id, project_id, checks)
VALUES
    (1, 1, TRUE),
    (2, 2, TRUE);

-- Populate task_not table
INSERT INTO task_not (notification_id, task_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3);

-- Populate invite_not table
INSERT INTO invite_not (notification_id, project_id)
VALUES
    (1, 1),
    (2, 2);

-- Populate authenticated_user_notifications table
INSERT INTO authenticated_user_notifications (id, notification_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3);

-- Populate user_task table
INSERT INTO user_task (id, task_id)
VALUES
    (1, 1),  -- User with id=1 assigned to Task with task_id=1
    (1, 2),  -- User with id=1 assigned to Task with task_id=2
    (2, 3),  -- User with id=2 assigned to Task with task_id=3
    (3, 1),  -- User with id=3 assigned to Task with task_id=1
    (2, 2);  -- User with id=2 assigned to Task with task_id=2