<?php
// Database connection
$server = 'localhost';
$username = 'root';
$password = '';
$database = 'todo_app';

$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission for adding a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_name'])) {
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $query = "INSERT INTO tasks (task_name, created_at, status) VALUES ('$task_name', NOW(), 'pending')";
    mysqli_query($conn, $query);
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid resubmission
    exit;
}

// Handle status update
if (isset($_GET['update_status']) && isset($_GET['task_id'])) {
    $task_id = (int)$_GET['task_id'];
    $new_status = $_GET['update_status'] === 'pending' ? 'completed' : 'pending';
    $query = "UPDATE tasks SET status = '$new_status' WHERE id = $task_id";
    mysqli_query($conn, $query);
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the table
    exit;
}

// Handle delete task
if (isset($_GET['delete_task']) && isset($_GET['task_id'])) {
    $task_id = (int)$_GET['task_id'];
    $query = "DELETE FROM tasks WHERE id = $task_id";
    mysqli_query($conn, $query);
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the table
    exit;
}

// Fetch all tasks from the database
$query = "SELECT * FROM tasks ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List with Update & Delete</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f8f9fa;
        }
        .filter-form, .add-task-form {
            margin-bottom: 20px;
        }
        .filter-form label, .add-task-form label {
            margin-right: 15px;
        }
        .action-button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            margin-right: 5px;
        }
        .pending {
            background-color: orange;
        }
        .completed {
            background-color: green;
        }
        .delete {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>To-Do List</h1>

        <!-- Add Task Form -->
        <form class="add-task-form" method="POST" action="">
            <label for="task_name">New Task:</label>
            <input type="text" id="task_name" name="task_name" required>
            <button type="submit">Add Task</button>
        </form>

        <!-- Filter Form -->
        <div class="filter-form">
            <label><input type="radio" name="filter" value="all" checked> All</label>
            <label><input type="radio" name="filter" value="pending"> Pending</label>
            <label><input type="radio" name="filter" value="completed"> Completed</label>
        </div>

        <!-- Task Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr data-status="<?php echo htmlspecialchars($row['status']); ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                            <td>
                                <a href="?update_status=<?php echo $row['status']; ?>&task_id=<?php echo $row['id']; ?>" 
                                   class="action-button <?php echo $row['status']; ?>">
                                   <?php echo $row['status'] === 'pending' ? 'Mark Completed' : 'Mark Pending'; ?>
                                </a>
                                <a href="?delete_task=true&task_id=<?php echo $row['id']; ?>" 
                                   class="action-button delete"
                                   onclick="return confirm('Are you sure you want to delete this task?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No tasks found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Get filter form and table rows
        const filterRadios = document.querySelectorAll('input[name="filter"]');
        const rows = document.querySelectorAll('#taskTableBody tr');

        // Function to filter rows
        function filterTasks() {
            const filterValue = document.querySelector('input[name="filter"]:checked').value;
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                
                if (filterValue === 'all' || filterValue === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add event listeners to filter radio buttons
        filterRadios.forEach(radio => {
            radio.addEventListener('change', filterTasks);
        });

        // Initial filter (default: show all tasks)
        filterTasks();
    </script>
</body>
</html>
