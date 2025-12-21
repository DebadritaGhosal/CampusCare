<?php
function displayProfilePicture($userId, $name, $profilePic = null, $size = 'medium', $role = 'student') {
    // Size classes
    $sizes = [
        'small' => ['width' => '40px', 'height' => '40px', 'font' => '16px'],
        'medium' => ['width' => '60px', 'height' => '60px', 'font' => '24px'],
        'large' => ['width' => '100px', 'height' => '100px', 'font' => '36px']
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['medium'];
    
    // Role-based colors
    $roleColors = [
        'student' => '#7209b7',
        'mentor' => '#4361ee',
        'admin' => '#2E8B57'
    ];
    $color = $roleColors[$role] ?? '#2E8B57';
    
    // If profile picture exists and file exists
    if ($profilePic && file_exists($profilePic)) {
        echo '<img src="' . htmlspecialchars($profilePic) . '" 
                   alt="' . htmlspecialchars($name) . '" 
                   style="width: ' . $sizeClass['width'] . '; 
                          height: ' . $sizeClass['height'] . '; 
                          border-radius: 50%; 
                          object-fit: cover; 
                          border: 2px solid ' . $color . ';">';
    } else {
        // Display initials
        $initials = strtoupper(substr($name, 0, 1));
        echo '<div style="width: ' . $sizeClass['width'] . '; 
                          height: ' . $sizeClass['height'] . '; 
                          border-radius: 50%; 
                          background: ' . $color . '; 
                          color: white; 
                          display: flex; 
                          align-items: center; 
                          justify-content: center; 
                          font-size: ' . $sizeClass['font'] . '; 
                          font-weight: bold;">' . $initials . '</div>';
    }
}
?>