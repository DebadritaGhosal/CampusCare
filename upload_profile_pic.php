<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $userId = $_SESSION['user_id'];
    
    // File upload settings
    $uploadDir = 'uploads/profile_pics/';
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['profile_picture'];
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = mime_content_type($fileTmpName);
    
    // Generate unique filename
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = 'user_' . $userId . '_' . time() . '.' . strtolower($fileExt);
    $uploadPath = $uploadDir . $newFileName;
    
    // Validate file
    if ($fileError !== UPLOAD_ERR_OK) {
        $message = 'File upload error: ' . $fileError;
        $message_type = 'error';
    } elseif (!in_array($fileType, $allowedTypes)) {
        $message = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        $message_type = 'error';
    } elseif ($fileSize > $maxFileSize) {
        $message = 'File size must be less than 5MB.';
        $message_type = 'error';
    } else {
        // Resize image to 500x500 for consistency
        $resized = resizeImage($fileTmpName, 500, 500);
        
        if ($resized) {
            // Save resized image
            imagejpeg($resized, $uploadPath, 90);
            imagedestroy($resized);
            
            // Delete old profile picture if exists
            $stmt = $pdo->prepare('SELECT profile_pic FROM signup_details WHERE id = ?');
            $stmt->execute([$userId]);
            $oldPic = $stmt->fetchColumn();
            
            if ($oldPic && file_exists($oldPic)) {
                unlink($oldPic);
            }
            
            // Update database
            $stmt = $pdo->prepare('UPDATE signup_details SET profile_pic = ? WHERE id = ?');
            if ($stmt->execute([$uploadPath, $userId])) {
                $_SESSION['profile_pic'] = $uploadPath;
                $message = 'Profile picture updated successfully!';
                $message_type = 'success';
            } else {
                $message = 'Database update failed.';
                $message_type = 'error';
            }
        } else {
            $message = 'Image processing failed.';
            $message_type = 'error';
        }
    }
}

// Function to resize image
function resizeImage($file, $w, $h) {
    $imageInfo = getimagesize($file);
    $mime = $imageInfo['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($file);
            break;
        case 'image/png':
            $image = imagecreatefrompng($file);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($file);
            break;
        default:
            return false;
    }
    
    $origWidth = imagesx($image);
    $origHeight = imagesy($image);
    
    // Calculate aspect ratio
    $ratio = $origWidth / $origHeight;
    
    if ($w/$h > $ratio) {
        $newWidth = $h * $ratio;
        $newHeight = $h;
    } else {
        $newWidth = $w;
        $newHeight = $w / $ratio;
    }
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }
    
    // Resize image
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    return $newImage;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile Picture | CampusCare</title>
    <style>
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .profile-pic-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .current-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2E8B57;
            margin-bottom: 15px;
        }
        
        .initials-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #2E8B57;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        
        .upload-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-button {
            display: block;
            padding: 15px;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 5px;
            text-align: center;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input-button:hover {
            background: #e9ecef;
            border-color: #2E8B57;
        }
        
        .preview-container {
            margin-top: 20px;
            text-align: center;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 50%;
            border: 2px solid #2E8B57;
            display: none;
        }
        
        .btn {
            padding: 12px 24px;
            background: #2E8B57;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #267349;
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .requirements ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; color: #2E8B57;">Update Profile Picture</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-pic-container">
            <?php
            // Get current profile picture
            $stmt = $pdo->prepare('SELECT profile_pic, name FROM signup_details WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user['profile_pic'] && file_exists($user['profile_pic'])): 
            ?>
                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                     alt="Profile Picture" 
                     class="current-pic"
                     id="currentPicture">
            <?php else: ?>
                <div class="initials-circle" id="initialsCircle">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
        </div>
        
        <div class="requirements">
            <p><strong>Requirements:</strong></p>
            <ul>
                <li>Maximum file size: 5MB</li>
                <li>Allowed formats: JPG, JPEG, PNG, GIF</li>
                <li>Recommended size: 500x500 pixels (will be resized automatically)</li>
                <li>Square images work best</li>
            </ul>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label>Choose new profile picture:</label>
                <div class="file-input-wrapper">
                    <div class="file-input-button" id="fileButton">
                        Click to browse or drag & drop
                    </div>
                    <input type="file" 
                           id="profile_picture" 
                           name="profile_picture" 
                           accept="image/*"
                           required>
                </div>
                <small id="fileName" style="display: block; margin-top: 5px; color: #666;"></small>
            </div>
            
            <div class="preview-container">
                <img id="imagePreview" class="preview-image" alt="Image Preview">
            </div>
            
            <button type="submit" class="btn" id="submitBtn">Upload Picture</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>
    
    <script>
        // File input handling
        const fileInput = document.getElementById('profile_picture');
        const fileButton = document.getElementById('fileButton');
        const fileName = document.getElementById('fileName');
        const imagePreview = document.getElementById('imagePreview');
        const submitBtn = document.getElementById('submitBtn');
        const currentPicture = document.getElementById('currentPicture');
        const initialsCircle = document.getElementById('initialsCircle');
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file size
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    fileInput.value = '';
                    fileName.textContent = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Only JPG, JPEG, PNG & GIF files are allowed');
                    fileInput.value = '';
                    fileName.textContent = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                
                // Update UI
                fileName.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    
                    // Hide current picture/initials
                    if (currentPicture) currentPicture.style.display = 'none';
                    if (initialsCircle) initialsCircle.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = '';
                imagePreview.style.display = 'none';
                
                // Show current picture/initials again
                if (currentPicture) currentPicture.style.display = 'block';
                if (initialsCircle) initialsCircle.style.display = 'flex';
            }
        });
        
        // Drag and drop functionality
        fileButton.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.background = '#e9ecef';
            this.style.borderColor = '#2E8B57';
        });
        
        fileButton.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.background = '#f8f9fa';
            this.style.borderColor = '#ddd';
        });
        
        fileButton.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.background = '#f8f9fa';
            this.style.borderColor = '#ddd';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('Please select a file to upload');
                return false;
            }
            
            // Disable button during upload
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            return true;
        });
    </script>
</body>
</html>