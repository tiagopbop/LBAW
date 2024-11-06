ROLLBACK;
BEGIN;
DROP TRIGGER IF EXISTS update_task_and_project_timestamp_trigger ON task;
DROP FUNCTION IF EXISTS update_task_and_project_timestamp;
DROP TRIGGER IF EXISTS TRIGGER_TASK_ASSIGN_NOTIFY ON user_task;
DROP FUNCTION IF EXISTS notify_task_assignment;

DROP TABLE IF EXISTS user_task CASCADE;
DROP TABLE IF EXISTS authenticated_user_notifications CASCADE;
DROP TABLE IF EXISTS invite_not CASCADE;
DROP TABLE IF EXISTS task_not CASCADE;
DROP TABLE IF EXISTS favourited CASCADE;
DROP TABLE IF EXISTS reply CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS project_member CASCADE;
DROP TABLE IF EXISTS task_comments CASCADE;
DROP TABLE IF EXISTS task CASCADE;
DROP TABLE IF EXISTS project CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS authenticated_user CASCADE;

DROP TYPE IF EXISTS priority;
DROP TYPE IF EXISTS "role";
DROP TYPE IF EXISTS status;

---------------------------------------------------------------

CREATE TYPE priority AS ENUM ('High', 'Medium', 'Low');
CREATE TYPE "role" AS ENUM ('Project member', 'Project manager', 'Project owner');
CREATE TYPE status AS ENUM ('Ongoing', 'On-hold', 'Finished');

---------------------------------------------------------------

CREATE TABLE authenticated_user (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_creation_date DATE DEFAULT CURRENT_DATE NOT NULL,
    suspended_status BOOLEAN NOT NULL,
    pfp VARCHAR(255),
    pronouns VARCHAR(50),
    bio TEXT,
    country VARCHAR(100)
);

CREATE TABLE admin (
    admin_id SERIAL PRIMARY KEY,
    admin_tag VARCHAR(255) UNIQUE NOT NULL,
    admin_username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE notifications (
    notification_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT CHECK (LENGTH(content) < 500) NOT NULL,
    created_at DATE DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE project (
    project_id SERIAL PRIMARY KEY,
    availability BOOLEAN NOT NULL,
    project_creation_date DATE DEFAULT CURRENT_DATE NOT NULL,
    archived_status BOOLEAN NOT NULL,
    updated_at DATE,
    project_title VARCHAR(50) NOT NULL,
    project_description TEXT CHECK (LENGTH(project_description) < 500)

);

CREATE TABLE task (
    task_id SERIAL PRIMARY KEY,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    task_name VARCHAR(255) NOT NULL,
    status status NOT NULL,
    details TEXT CHECK (LENGTH(details) < 500),
    due_date DATE CHECK (due_date > created_at),
    priority priority DEFAULT 'Medium' NOT NULL,
    created_at DATE DEFAULT CURRENT_DATE NOT NULL,
    updated_at DATE
);

CREATE TABLE task_comments (
    comment_id SERIAL PRIMARY KEY,
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    task_id INT REFERENCES task(task_id)ON UPDATE CASCADE ON DELETE CASCADE,
    comment TEXT CHECK (LENGTH(comment) < 500) NOT NULL,
    created_at DATE DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE project_member (
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    "role" "role" DEFAULT 'Project member' NOT NULL,
    PRIMARY KEY (id, project_id)
);

CREATE TABLE post (
    post_id SERIAL PRIMARY KEY,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    content TEXT CHECK (LENGTH(content) < 500) NOT NULL,
    post_creation DATE DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE reply (
    reply_id SERIAL PRIMARY KEY,
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    post_id INT REFERENCES post(post_id) ON UPDATE CASCADE ON DELETE CASCADE,
    content TEXT CHECK (LENGTH(content) < 500) NOT NULL,
    reply_creation DATE DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE favourited (
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    checks BOOLEAN NOT NULL,
    PRIMARY KEY (id, project_id)
);

CREATE TABLE task_not (
    task_not_id SERIAL PRIMARY KEY,
    notification_id INT REFERENCES notifications(notification_id)ON UPDATE CASCADE ON DELETE CASCADE,
    task_id INT REFERENCES task(task_id)ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE invite_not (
    invite_not_id SERIAL PRIMARY KEY,
    notification_id INT REFERENCES notifications(notification_id)ON UPDATE CASCADE ON DELETE CASCADE,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE authenticated_user_notifications (
    id INTEGER NOT NULL REFERENCES authenticated_user(id) ON UPDATE CASCADE ON DELETE CASCADE,
    notification_id INTEGER NOT NULL REFERENCES notifications(notification_id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (id, notification_id)
);

CREATE TABLE user_task (
    id INT REFERENCES authenticated_user(id) ON UPDATE CASCADE ON DELETE CASCADE,
    task_id INT REFERENCES task(task_id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (id, task_id)
);


CREATE FUNCTION update_task_and_project_timestamp() RETURNS TRIGGER AS $$

BEGIN
    -- Set the updated_at field for the task to the current timestamp when the task is modified
    NEW.updated_at := CURRENT_TIMESTAMP;

    -- Update the project’s updated_at field when the task is modified
    UPDATE project
    SET updated_at = CURRENT_TIMESTAMP
    WHERE project_id = NEW.project_id;

    RETURN NEW;  -- Return the modified task row
END
$$
LANGUAGE plpgsql;

CREATE TRIGGER update_task_and_project_timestamp_trigger
BEFORE UPDATE ON task
FOR EACH ROW
EXECUTE PROCEDURE update_task_and_project_timestamp();

-- Step 1: Create the function to notify user on task assignment
CREATE FUNCTION notify_task_assignment() RETURNS TRIGGER AS
$BODY$
DECLARE
    notification_title VARCHAR(255) := 'New Task Assignment';
    notification_content TEXT := 'You have been assigned a new task. Please review and start working on it as soon as possible.';
    new_notification_id INT;
BEGIN
    -- Insert notification into notifications table
    INSERT INTO notifications (title, content, created_at)
    VALUES (notification_title, notification_content, CURRENT_DATE)
    RETURNING notification_id INTO new_notification_id;
    -- Link the notification to the task and user
    INSERT INTO task_not (notification_id, task_id) VALUES (new_notification_id, NEW.task_id);
    INSERT INTO authenticated_user_notifications (id, notification_id) VALUES (NEW.id, new_notification_id);
    RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER TRIGGER_TASK_ASSIGN_NOTIFY
AFTER INSERT ON user_task
FOR EACH ROW
EXECUTE PROCEDURE notify_task_assignment();


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