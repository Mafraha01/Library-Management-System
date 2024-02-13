<?php
session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_system";


$database = new mysqli($servername, $username, $password, $dbname);


if ($database->connect_error) {
    die("Connection failed: " . $database->connect_error);
}


function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}


function validate_book_id($book_id) {
    return preg_match('/^B\d{3}$/', $book_id);
}

function validate_category_id($category_id) {
    return in_array($category_id, array("C001", "C002"));
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $book_id = sanitize_input($_POST['book_id']);
        $book_name = sanitize_input($_POST['book_name']);
        $category_id = sanitize_input($_POST['category_id']);

        
        $checkResult = $database->query("SELECT * FROM book WHERE book_id='$book_id'");
        if ($checkResult->num_rows > 0) {
            $_SESSION['message'] = "Book ID already exists!";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_book_id($book_id)) {
            $_SESSION['message'] = "Invalid Book ID format. Example: B001";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_category_id($category_id)) {
            $_SESSION['message'] = "Category ID not exists!";
            $_SESSION['msg_type'] = "danger";
        } else {
            $database->query("INSERT INTO book (book_id, book_name, category_id) VALUES ('$book_id', '$book_name', '$category_id')")
                or die($database->error);

            $_SESSION['message'] = "Book added successfully!";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
    

    if (isset($_POST['update'])) {
        
        $originalbook_id = sanitize_input($_POST['originalbook_id']);
        $book_id = sanitize_input($_POST['book_id']);
        $book_name = sanitize_input($_POST['book_name']);
        $category_id = sanitize_input($_POST['category_id']);

        
        if (!validate_book_id($book_id)) {
            $_SESSION['message'] = "Invalid Book ID format. Example: B001";
            $_SESSION['msg_type'] = "danger";
        } else {
            $database->query("UPDATE book SET book_id='$book_id', book_name='$book_name', category_id='$category_id' WHERE book_id='$originalbook_id'")
                or die($database->error);

            $_SESSION['message'] = "Book updated successfully!";
            $_SESSION['msg_type'] = "warning";
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}


if (isset($_GET['delete'])) {
    $book_id = sanitize_input($_GET['delete']);
    $database->query("DELETE FROM book WHERE book_id='$book_id'") or die($database->error);

    $_SESSION['message'] = "Book deleted successfully!";
    $_SESSION['msg_type'] = "danger";
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}


if (isset($_GET['edit'])) {
    $editbook_id = sanitize_input($_GET['edit']);
    $editResult = $database->query("SELECT * FROM book WHERE book_id='$editbook_id'") or die($database->error);

    if ($editResult->num_rows == 1) {
        $editData = $editResult->fetch_assoc();
        $editbook_id = $editData['book_id'];
        $editbook_name = $editData['book_name'];
        $editcategory_id = $editData['category_id'];
    } else {
        
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Book Registration</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('lm.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        .center-title {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            background-color:#FFA407;
            padding: 10px;
            border-radius: 5px;
        }

        .error-message {
            color: red;
        }

        .form-group {
            text-align: left;
            margin-bottom: 30px;
        }

        .form-group input, .form-group select {
            width: 450px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 17px;
        }

        table {
            margin-top: 20px;
        }

        th, td {
            text-align: center;
        }

        th {
            background-color: #343a40;
            color: #ffffff;
        }

        .btn-warning, .btn-danger, .btn-secondary {
            padding: 5px 10px;
            margin-right: 5px;
        }

        .btn-warning:hover, .btn-danger:hover, .btn-secondary:hover {
            opacity: 0.8;
        }

        .btn-primary {
            background-color: #28a745; 
            border: none;
        }

        .btn-primary:hover {
            background-color: #218838; 
        }

        .btn-secondary {
            background-color: #dc3545; 
            border: none;
        }

        .btn-secondary:hover {
            background-color: #c82333; 
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<?php
    include("admin.php");
?>
<body>
    <div class="container">

        <h2 class="center-title display-5">Library Book Registration</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?>" role="alert">
                <?= $_SESSION['message'] ?>
            </div>
            <?php
            
            unset($_SESSION['message']);
            unset($_SESSION['msg_type']);
            ?>
        <?php endif; ?>

        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="mx-auto col-lg-6">
        <div class="form-group">
    <label for="book_id">Book ID:</label>
    <input type="text" class="form-control" id="book_id" name="book_id" 
           value="<?= isset($editbook_id) ? $editbook_id : '' ?>" 
           <?= isset($editbook_id) ? 'readonly' : '' ?> required>
    <small class="error-message">
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_book_id($_POST['book_id'])) echo "Invalid Book ID format. Example: B001"; ?>
    </small>
</div>

            <div class="form-group">
                <label for="book_name">Book Name:</label>
                <input type="text" class="form-control" id="book_name" name="book_name" value="<?= isset($editbook_name) ? $editbook_name : '' ?>" required>
            </div>
            <div class="form-group">
    <label for="category_id">Category ID:</label>
    <input type="text" class="form-control" id="category_id" name="category_id" value="<?= isset($editcategory_id) ? $editcategory_id : '' ?>" required>
</div>


            <div class="button-container">
                <button type="submit" class="btn btn-warning" name="<?= isset($editbook_id) ? 'update' : 'add' ?>">
                    <?= isset($editbook_id) ? 'Update Book' : 'Add Book' ?>
                </button>
                <?php if (isset($editbook_id)): ?>
                    <input type="hidden" name="originalbook_id" value="<?= $editbook_id ?>">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <br>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Book Name</th>
                    <th>Book Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $database->query("SELECT * FROM book") or die($database->error);

                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $row['book_id'] ?></td>
                        <td><?= $row['book_name'] ?></td>
                        <td><?= $row['category_id'] ?></td>
                        <td>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?edit=<?= $row['book_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?delete=<?= $row['book_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
