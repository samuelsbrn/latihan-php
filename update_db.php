<?php
require_once('config.php');

$conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
if (!$conn) {
    die('Connection failed');
}

pg_query($conn, 'ALTER TABLE todo ADD COLUMN IF NOT EXISTS description TEXT');
pg_query($conn, 'ALTER TABLE todo ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
pg_query($conn, 'ALTER TABLE todo ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

// Rename columns only if they exist
$result = pg_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_name='todo' AND column_name='activity'");
if (pg_num_rows($result) > 0) {
    pg_query($conn, 'ALTER TABLE todo RENAME COLUMN activity TO title');
}

$result = pg_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_name='todo' AND column_name='status'");
if (pg_num_rows($result) > 0) {
    pg_query($conn, 'ALTER TABLE todo RENAME COLUMN status TO is_finished');
}

echo 'Database updated successfully';
?>
