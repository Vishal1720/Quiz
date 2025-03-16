<?php
include "dbconnect.php";

if($_SESSION['status'] !== "admin") {
    header("Location: login.php");
    exit();
}

$success = $error = '';

// Handle category operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new category
    if (isset($_POST['add_category']) && !empty($_POST['category_name'])) {
        $categoryName = mysqli_real_escape_string($con, $_POST['category_name']);
        
        $stmt = $con->prepare("INSERT INTO category (categoryname) VALUES (?)");
        $stmt->bind_param("s", $categoryName);
        
        if ($stmt->execute()) {
            $success = "Category added successfully!";
        } else {
            $error = "Error adding category: " . $con->error;
        }
    }
    
    // Delete category
    if (isset($_POST['delete_category']) && !empty($_POST['category_name'])) {
        $categoryName = mysqli_real_escape_string($con, $_POST['category_name']);
        
        // Check if category has quizzes
        $checkQuizzes = $con->prepare("SELECT COUNT(*) as count FROM quizdetails WHERE category = ?");
        $checkQuizzes->bind_param("s", $categoryName);
        $checkQuizzes->execute();
        $quizCount = $checkQuizzes->get_result()->fetch_assoc()['count'];
        
        if ($quizCount > 0) {
            $error = "Cannot delete category because it has quizzes. Delete the quizzes first.";
        } else {
            // First delete all subcategories of this category
            $deleteSubcategories = $con->prepare("DELETE FROM subcategories WHERE category_name = ?");
            $deleteSubcategories->bind_param("s", $categoryName);
            $deleteSubcategories->execute();
            
            // Then delete the category
            $stmt = $con->prepare("DELETE FROM category WHERE categoryname = ?");
            $stmt->bind_param("s", $categoryName);
            
            if ($stmt->execute()) {
                $success = "Category and all its subcategories deleted successfully!";
            } else {
                $error = "Error deleting category: " . $con->error;
            }
        }
    }
    
    // Add new subcategory
    if (isset($_POST['add_subcategory']) && !empty($_POST['subcategory_name']) && !empty($_POST['parent_category'])) {
        $subcategoryName = mysqli_real_escape_string($con, $_POST['subcategory_name']);
        $parentCategory = mysqli_real_escape_string($con, $_POST['parent_category']);
        
        $stmt = $con->prepare("INSERT INTO subcategories (subcategory_name, category_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $subcategoryName, $parentCategory);
        
        if ($stmt->execute()) {
            $success = "Subcategory added successfully!";
        } else {
            $error = "Error adding subcategory: " . $con->error;
        }
    }
    
    // Delete subcategory
    if (isset($_POST['delete_subcategory']) && !empty($_POST['subcategory_id'])) {
        $subcategoryId = mysqli_real_escape_string($con, $_POST['subcategory_id']);
        
        $stmt = $con->prepare("DELETE FROM subcategories WHERE id = ?");
        $stmt->bind_param("i", $subcategoryId);
        
        if ($stmt->execute()) {
            $success = "Subcategory deleted successfully!";
        } else {
            $error = "Error deleting subcategory: " . $con->error;
        }
    }
}

// Get all categories
$categoryQuery = "SELECT categoryname FROM category ORDER BY categoryname ASC";
$categories = $con->query($categoryQuery);

// Get all subcategories with their parent category names
$subcategoryQuery = "SELECT s.id, s.subcategory_name, s.category_name 
                    FROM subcategories s 
                    JOIN category c ON s.category_name = c.categoryname 
                    ORDER BY s.category_name, s.subcategory_name";
$subcategories = $con->query($subcategoryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - QuizMaster</title>
    <style>
        :root {
            --primary: #4a90e2;
            --primary-dark: #357abd;
            --secondary: #ec4899;
            --accent: #8b5cf6;
            --background: #1a1a2e;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #22c55e;
            --error: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
            text-align: center;
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .success {
            background-color: rgba(34, 197, 94, 0.2);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .error {
            background-color: rgba(239, 68, 68, 0.2);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            font-size: 1rem;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.3);
        }
        
        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .table-container {
            overflow-x: auto;
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        th {
            background: rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: var(--primary);
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .btn-delete {
            background: var(--error);
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.8);
        }
        
        .empty-message {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include "components/header.php"; ?>
    
    <div class="container">
        <h1 class="page-title">Manage Categories</h1>
        <p class="page-subtitle">Add, edit, or delete categories and subcategories</p>
        
        <?php if($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="grid">
            <!-- Add Category Form -->
            <div class="card">
                <h2 class="card-title">Add New Category</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" required placeholder="Enter category name">
                    </div>
                    <button type="submit" name="add_category">Add Category</button>
                </form>
            </div>
            
            <!-- Add Subcategory Form -->
            <div class="card">
                <h2 class="card-title">Add New Subcategory</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="parent_category">Parent Category</label>
                        <select id="parent_category" name="parent_category" required>
                            <option value="">Select a category</option>
                            <?php 
                            if ($categories && $categories->num_rows > 0) {
                                $categories->data_seek(0);
                                while($category = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($category['categoryname']); ?>">
                                    <?php echo htmlspecialchars($category['categoryname']); ?>
                                </option>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subcategory_name">Subcategory Name</label>
                        <input type="text" id="subcategory_name" name="subcategory_name" required placeholder="Enter subcategory name">
                    </div>
                    <button type="submit" name="add_subcategory">Add Subcategory</button>
                </form>
            </div>
        </div>
        
        <!-- Categories Table -->
        <h2 class="card-title">Categories</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($categories && $categories->num_rows > 0):
                        $categories->data_seek(0);
                        while($category = $categories->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['categoryname']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category? This will also delete all subcategories.');">
                                    <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($category['categoryname']); ?>">
                                    <button type="submit" name="delete_category" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="2" class="empty-message">No categories found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Subcategories Table -->
        <h2 class="card-title">Subcategories</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subcategory Name</th>
                        <th>Parent Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subcategories && $subcategories->num_rows > 0): ?>
                        <?php while($subcategory = $subcategories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $subcategory['id']; ?></td>
                                <td><?php echo htmlspecialchars($subcategory['subcategory_name']); ?></td>
                                <td><?php echo htmlspecialchars($subcategory['category_name']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                        <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id']; ?>">
                                        <button type="submit" name="delete_subcategory" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-message">No subcategories found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include "components/footer.php"; ?>
    
    <script>
        // Add confirmation for delete actions
        document.addEventListener('DOMContentLoaded', function() {
            // This is handled by the onsubmit attribute, but we could add additional JS here if needed
        });
    </script>
</body>
</html> 