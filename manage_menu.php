<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}
if (($_SESSION['admin'] ?? 'NO') !== 'YES') {
    header("location: index.php");
    exit();
}

$allowedCategories = ["Veg Burger", "Non-Veg Burger", "Combo", "Drink"];

// Category -> image folder (Assets/Store/<folder>/)
$categoryFolders = [
    "Veg Burger"     => "vegburger",
    "Non-Veg Burger" => "non-vegburger",
    "Combo"          => "combo",
    "Drink"          => "drink",
];

$premiumDir        = "Assets/Store/premium";
$allowedImageTypes = [
    "image/png"  => "png",
    "image/jpeg" => "jpg",
    "image/webp" => "webp",
];
$imageExtensions = ["png", "jpg", "jpeg", "webp"];
$maxImageSize    = 2 * 1024 * 1024; // 2 MB

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function flash($type, $text) {
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

// Relative folder for a category, e.g. Assets/Store/vegburger
function categoryDir($category, $folders) {
    return "Assets/Store/" . ($folders[$category] ?? "drink");
}

// Relative path of an existing item image (premium first, then category folder), or null
function findImage($name, $category, $folders, $premiumDir, $extensions) {
    foreach ([$premiumDir, categoryDir($category, $folders)] as $dir) {
        foreach ($extensions as $ext) {
            $rel = $dir . "/" . $name . "." . $ext;
            if (is_file(__DIR__ . "/" . $rel)) {
                return $rel;
            }
        }
    }
    return null;
}

// Encode a relative path so it works in an <img src>
function urlPath($rel) {
    return implode("/", array_map('rawurlencode', explode("/", $rel)));
}

// Create a folder (relative to this file) if it doesn't exist
function ensureDir($rel) {
    $abs = __DIR__ . "/" . $rel;
    return is_dir($abs) || mkdir($abs, 0775, true);
}

function deleteFile($rel) {
    $abs = __DIR__ . "/" . $rel;
    if (is_file($abs)) {
        unlink($abs);
    }
}

// Load one product by id, or null if not found
function getProduct($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, price, category FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $pid, $pname, $pprice, $pcat);
    $row = null;
    if (mysqli_stmt_fetch($stmt)) {
        $row = ['id' => $pid, 'name' => $pname, 'price' => $pprice, 'category' => $pcat];
    }
    mysqli_stmt_close($stmt);
    return $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add' || $action === 'edit') {
            $id       = (int)($_POST['id'] ?? 0);
            $name     = trim($_POST['name'] ?? '');
            $price    = $_POST['price'] ?? '';
            $category = $_POST['category'] ?? '';
            $file     = $_FILES['image'] ?? null;
            $hasFile  = $file !== null && $file['error'] !== UPLOAD_ERR_NO_FILE;

            $newExt = null;
            $error  = null;

            if ($name === '' || mb_strlen($name) > 100) {
                $error = 'Item name is required (max 100 characters).';
            } elseif (preg_match('/[\\\\\/:*?"<>|]/', $name)) {
                $error = 'Name cannot contain these characters: \ / : * ? " < > |';
            } elseif (!is_numeric($price) || (float)$price < 0) {
                $error = 'Enter a valid price.';
            } elseif (!in_array($category, $allowedCategories, true)) {
                $error = 'Choose a valid category.';
            } elseif ($action === 'add' && !$hasFile) {
                $error = 'Please upload an image for the item.';
            } elseif ($hasFile && $file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Image upload failed. Please try again.';
            } elseif ($hasFile && $file['size'] > $maxImageSize) {
                $error = 'Image must be 2 MB or smaller.';
            } elseif ($hasFile) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);
                if (!isset($allowedImageTypes[$mime])) {
                    $error = 'Only PNG, JPG, or WEBP images are allowed.';
                } else {
                    $newExt = $allowedImageTypes[$mime];
                }
            }

            if ($error !== null) {
                flash('error', $error);

            } elseif ($action === 'add') {
                $priceVal  = round((float)$price, 2);
                $dir       = categoryDir($category, $categoryFolders);
                $targetRel = $dir . "/" . $name . "." . $newExt;
                ensureDir($dir);

                $stmt = mysqli_prepare($conn, "INSERT INTO products (name, price, category) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sds", $name, $priceVal, $category);
                mysqli_stmt_execute($stmt);
                $newId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                if (move_uploaded_file($file['tmp_name'], __DIR__ . "/" . $targetRel)) {
                    flash('success', "\"$name\" was added to the menu with its image.");
                } else {
                    // Image could not be saved, so remove the row to keep things consistent
                    $del = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
                    mysqli_stmt_bind_param($del, "i", $newId);
                    mysqli_stmt_execute($del);
                    mysqli_stmt_close($del);
                    flash('error', 'Could not save the image file. Check folder permissions.');
                }

            } else { // edit
                $old = getProduct($conn, $id);

                if (!$old) {
                    flash('error', 'Item not found.');
                } else {
                    $priceVal = round((float)$price, 2);
                    $stmt = mysqli_prepare($conn, "UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "sdsi", $name, $priceVal, $category, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $oldRel = findImage($old['name'], $old['category'], $categoryFolders, $premiumDir, $imageExtensions);
                    $dir    = categoryDir($category, $categoryFolders);

                    if ($hasFile) {
                        // New picture uploaded: save it and remove the old one
                        $newRel = $dir . "/" . $name . "." . $newExt;
                        ensureDir($dir);

                        if (move_uploaded_file($file['tmp_name'], __DIR__ . "/" . $newRel)) {
                            if ($oldRel !== null && strcasecmp($oldRel, $newRel) !== 0) {
                                deleteFile($oldRel);
                            }
                            flash('success', 'Item updated successfully.');
                        } else {
                            flash('error', 'Item updated, but the new image could not be saved. Check folder permissions.');
                        }

                    } elseif ($oldRel !== null && ($old['name'] !== $name || $old['category'] !== $category)) {
                        // No new picture, but name or category changed: move the existing image to match
                        $ext    = pathinfo($oldRel, PATHINFO_EXTENSION);
                        $newRel = $dir . "/" . $name . "." . $ext;
                        ensureDir($dir);

                        $sameFile = strcasecmp($newRel, $oldRel) === 0;
                        if ($sameFile || !is_file(__DIR__ . "/" . $newRel)) {
                            if (rename(__DIR__ . "/" . $oldRel, __DIR__ . "/" . $newRel)) {
                                flash('success', 'Item updated successfully.');
                            } else {
                                flash('error', 'Item updated, but the image could not be moved. Check folder permissions.');
                            }
                        } else {
                            flash('error', 'Item updated, but an image with the new name already exists, so the old picture was kept.');
                        }

                    } else {
                        flash('success', 'Item updated successfully.');
                    }
                }
            }

        } elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $old = getProduct($conn, $id);

            if (!$old) {
                flash('error', 'Item not found.');
            } else {
                $rel = findImage($old['name'], $old['category'], $categoryFolders, $premiumDir, $imageExtensions);

                $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($rel !== null) {
                    deleteFile($rel);
                }
                flash('success', 'Item deleted successfully.');
            }
        }

    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() == 1062) {
            flash('error', 'An item with this name already exists.');
        } else {
            flash('error', 'Database error: ' . $ex->getMessage());
        }
    }

    header("Location: manage_menu.php");
    exit();
}

$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$editId = (int)($_GET['edit'] ?? 0);

$products = [];
$res = mysqli_query($conn, "SELECT id, name, price, category FROM products
                            ORDER BY FIELD(category, 'Veg Burger', 'Non-Veg Burger', 'Combo', 'Drink'), name");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $row['id'] = (int)$row['id'];
        $rel = findImage($row['name'], $row['category'], $categoryFolders, $premiumDir, $imageExtensions);
        $row['image'] = $rel !== null ? urlPath($rel) : "Assets/logo.png";
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #111;
            color: #fff;
            margin: 0;
        }

        /* ---------- HEADER ---------- */
        .nav-container {
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            min-height: 70px;
            padding: 10px 30px;
            background: #111;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
            white-space: nowrap;
        }
        .brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .nav-right > a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            padding: 6px 12px;
            border-radius: 5px;
            transition: color 0.3s;
        }
        .nav-right > a:hover {
            color: #ff4d4d;
        }
        .nav-right > a.active {
            color: #ff4d4d;
            font-weight: bold;
        }
        .profile {
            position: relative;
            margin-left: 10px;
        }
        .profile img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #fff;
            object-fit: cover;
            cursor: pointer;
            display: block;
        }
        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: #fff;
            min-width: 150px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            overflow: hidden;
        }
        .dropdown a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }
        .dropdown a:hover {
            background: #f0f0f0;
            color: #007BFF;
        }

        /* ---------- PAGE ---------- */
        .page {
            padding: 30px 20px;
        }
        h1 {
            color: #ff6b6b;
            text-align: center;
            margin: 0 0 25px;
        }
        .card {
            background: #1d1d1d;
            padding: 20px;
            border-radius: 10px;
            max-width: 1000px;
            margin: 0 auto 25px;
        }
        .card h2 {
            margin-top: 0;
            font-size: 22px;
        }
        .hint {
            color: #aaa;
            font-size: 13px;
            margin: 0 0 12px;
        }
        .flash {
            max-width: 1000px;
            margin: 0 auto 20px;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
        }
        .flash.success { background: #1e7e34; }
        .flash.error   { background: #b02a37; }

        .form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        input, select {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #444;
            background: #2a2a2a;
            color: #fff;
        }
        input[type="file"] {
            max-width: 260px;
        }
        button, .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            background: #ff4d4d;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover { background: #e04343; }
        .btn-edit { background: #3498db; }
        .btn-edit:hover { background: #2980b9; }
        .btn-save { background: #2ecc71; }
        .btn-save:hover { background: #27ae60; }
        .btn-cancel { background: #555; }
        .btn-cancel:hover { background: #444; }

        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #333;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background: #ff6b6b;
        }
        .thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            background: #2a2a2a;
            display: block;
        }
        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .actions form {
            margin: 0;
        }
        .edit-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin: 0;
        }
        .edit-form .note {
            color: #aaa;
            font-size: 13px;
        }
        .cat-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 13px;
            background: #333;
        }

        @media (max-width: 900px) {
            .nav-container {
                flex-wrap: wrap;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <header class="nav-container">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png" alt="logo">
            <span>B K's Burger House</span>
        </a>

        <nav class="nav-right">
            <a href="index.php">Home</a>
            <a href="sales.php">Sales</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_menu.php" class="active">Manage Menu</a>

            <div class="profile">
                <img src="uploads/bk.png" id="profileBtn" alt="Profile">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="page">
        <h1>Manage Menu</h1>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>"><?= e($flash['text']) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Add New Item</h2>
            <p class="hint">Upload a picture (PNG, JPG or WEBP, max 2 MB). It is saved to the matching category folder automatically.</p>
            <form method="post" enctype="multipart/form-data" class="form-row">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Item name" maxlength="100" required>
                <input type="number" name="price" placeholder="Price" min="0" step="0.01" required>
                <select name="category" required>
                    <?php foreach ($allowedCategories as $cat): ?>
                        <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" required>
                <button type="submit">Add Item</button>
            </form>
        </div>

        <div class="card">
            <h2>Current Menu</h2>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>

                    <?php if (!$products): ?>
                        <tr><td colspan="5">No items yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($products as $p): ?>
                        <?php if ($p['id'] === $editId): ?>
                            <tr>
                                <td colspan="5">
                                    <form method="post" enctype="multipart/form-data" class="edit-form">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

                                        <input type="text" name="name" value="<?= e($p['name']) ?>" maxlength="100" required>
                                        <input type="number" name="price" value="<?= e($p['price']) ?>" min="0" step="0.01" required>
                                        <select name="category" required>
                                            <?php foreach ($allowedCategories as $cat): ?>
                                                <option value="<?= e($cat) ?>" <?= $cat === $p['category'] ? 'selected' : '' ?>><?= e($cat) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
                                        <span class="note">Leave the picture empty to keep the current one.</span>

                                        <button type="submit" class="btn-save">Save</button>
                                        <a href="manage_menu.php" class="btn btn-cancel">Cancel</a>
                                    </form>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td>
                                    <img class="thumb" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                                </td>
                                <td><?= e($p['name']) ?></td>
                                <td><span class="cat-tag"><?= e($p['category']) ?></span></td>
                                <td>₹<?= e($p['price']) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="manage_menu.php?edit=<?= (int)$p['id'] ?>" class="btn btn-edit">Edit</a>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this item from the menu?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("profileBtn");
            const menu = document.getElementById("dropMenu");

            btn.addEventListener("click", function (e) {
                e.stopPropagation();
                menu.style.display = menu.style.display === "block" ? "none" : "block";
            });

            document.addEventListener("click", function () {
                menu.style.display = "none";
            });
        });
    </script>
</body>
</html>