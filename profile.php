<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connect to database and fetch user data
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=campuscare;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // NOTE: use the same table name you insert into. changed to signup_details (no underscore)
    $stmt = $pdo->prepare('SELECT * FROM signup_details
     WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        // User not found in database
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Format join date
    $joined_date = date('n/j/Y', strtotime($user_data['joined_date'] ?? 'now'));
    
    // Prepare user data for display
    $display_data = [
        'name' => $user_data['name'] ?? 'User',
        'email' => $user_data['email'],
        'year' => $user_data['year'] ?? 'Student',
        'major' => $user_data['major'] ?? 'Undeclared',
        'joined_date' => $joined_date,
        'profile_pic' => $user_data['profile_pic'] ?? 'dp.png',
        'college' => $user_data['college'] ?? 'University',
        'phone' => $user_data['phone'] ?? '',
        'gender' => $user_data['gender'] ?? '',
        'dob' => $user_data['dob'] ?? ''
    ];
    
    // Fetch user stats (you'll need to create these tables)
    // For now, using mock data - you should replace with actual queries
    $stats = [
        'listings' => 0, // Count from marketplace table
        'wellness_checks' => 0, // Count from wellness table
        'support_sessions' => 0, // Count from support_sessions table
        'latest_wellness' => 'N/A'
    ];
    
    // Try to fetch actual stats
    $listings_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM marketplace WHERE user_id = ?');
    $listings_stmt->execute([$_SESSION['user_id']]);
    $listings_count = $listings_stmt->fetch(PDO::FETCH_ASSOC);
    $stats['listings'] = $listings_count['count'] ?? 0;
    
    // Fetch user's listings
    $listings_stmt = $pdo->prepare('SELECT * FROM marketplace WHERE user_id = ? ORDER BY posted_date DESC LIMIT 5');
    $listings_stmt->execute([$_SESSION['user_id']]);
    $user_listings = $listings_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no listings in DB, use mock data
    if (empty($user_listings)) {
        $listings = [
            [
                'id' => 1,
                'image' => 'dsaBook.png',
                'title' => 'Data Structures Textbook',
                'price' => 'Rs. 235/-',
                'status' => 'active',
                'status_bg' => 'oklch(.393 .095 152.535)',
                'status_color' => 'oklch(.925 .084 155.995)',
                'views' => '24 views',
                'messages' => '3 messages',
                'posted_date' => date('d/m/Y', strtotime('-10 days'))
            ]
        ];
    } else {
        // Format listings from database
        $listings = [];
        foreach ($user_listings as $listing) {
            $listings[] = [
                'id' => $listing['id'],
                'image' => $listing['image'] ?? 'default_item.png',
                'title' => $listing['title'],
                'price' => 'Rs. ' . ($listing['price'] ?? '0') . '/-',
                'status' => $listing['status'] ?? 'active',
                'status_bg' => $this->getStatusColor($listing['status'] ?? 'active', 'bg'),
                'status_color' => $this->getStatusColor($listing['status'] ?? 'active', 'text'),
                'views' => ($listing['views'] ?? 0) . ' views',
                'messages' => ($listing['messages'] ?? 0) . ' messages',
                'posted_date' => date('d/m/Y', strtotime($listing['posted_date']))
            ];
        }
    }
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function for status colors (define this or remove if not needed)
function getStatusColor($status, $type = 'bg') {
    $colors = [
        'active' => ['bg' => 'oklch(.393 .095 152.535)', 'text' => 'oklch(.925 .084 155.995)'],
        'sold' => ['bg' => 'oklch(.379 .146 265.522)', 'text' => 'oklch(.882 .059 254.128)'],
        'inactive' => ['bg' => 'oklch(.21 .034 264.665)', 'text' => 'oklch(.928 .006 264.531)']
    ];
    return $colors[$status][$type] ?? $colors['active'][$type];
}

// Mock data for wellness and support (replace with actual database queries)
$wellness_history = [
    [
        'date' => date('n/j/Y', strtotime('-7 days')),
        'score' => '85%',
        'status' => 'good',
        'status_bg' => 'oklch(.379 .146 265.522)',
        'status_color' => 'oklch(.882 .059 254.128)'
    ]
];

$support_sessions = [
    [
        'mentor_image' => 'help1.png',
        'mentor_name' => 'Maya Patel',
        'last_message' => 'How are you feeling about your exams this week?',
        'last_date' => date('d/m/Y', strtotime('-5 days')),
        'status' => 'active',
        'status_bg' => 'oklch(.379 .146 265.522)',
        'status_color' => 'oklch(.882 .059 254.128)',
        'active' => true
    ],
     [
        'mentor_image' => 'help1.png',
        'mentor_name' => 'Mayabono Bihariniini',
        'last_message' => 'How are you feeling about your exams this week?',
        'last_date' => date('d/m/Y', strtotime('-5 days')),
        'status' => 'active',
        'status_bg' => 'oklch(.379 .146 265.522)',
        'status_color' => 'oklch(.882 .059 254.128)',
        'active' => true
    ]
    
];

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCare - <?php echo htmlspecialchars($display_data['name']); ?>'s Profile</title>
    <link rel="stylesheet" href="styleProfile.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-buttons button.active { background-color: #2E8B57; color: white; }
        .additional-info { 
            margin-top: 10px; 
            color: #666; 
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="CampusCare Logo">
        </div>
        <nav class="nav">
            <a href="Home.php">Home</a>
            <a href="marketplace.php">Marketplace</a>
            <a href="wellness.php">Mental Wellness</a>
            <a href="#">Peer Support</a>
            <a href="profile.php" class="active">Profile</a>
        </nav>
        <div class="btns1">
            <button class="toggle-btn" id="theme" onclick="toggleTheme()">
                <span id="moonIcon">☾</span>
            </button>
            <a href="logout.php">
                <button id="sign_in">Sign Out</button>
            </a>
        </div>
    </header>
    
    <div class="info">
        <img src="uploads/alan.jpg" alt="Profile picture">
        <div class="part">
            <div class="vrPart">
                <h1><?php echo htmlspecialchars($display_data['name']); ?></h1>
                <a href="mailto:<?php echo htmlspecialchars($display_data['email']); ?>">
                    <?php echo htmlspecialchars($display_data['email']); ?>
                </a>
                <!-- Additional user info -->
                <div class="additional-info">
                    <?php if (!empty($display_data['college'])): ?>
                        <p>🏫 <?php echo htmlspecialchars($display_data['college']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($display_data['phone'])): ?>
                        <p>📱 <?php echo htmlspecialchars($display_data['phone']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($display_data['dob'])): ?>
                        <p>🎂 <?php echo date('F j, Y', strtotime($display_data['dob'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hrPart">
                <h2><?php echo htmlspecialchars($display_data['year']); ?> • <?php echo htmlspecialchars($display_data['major']); ?></h2>
                <!--<h2>Member since <?php //echo htmlspecialchars($display_data['joined_date ']); ?></h2>-->
            </div>
        </div>
        <div class="btn">
            <button onclick="window.location.href='edit_profile.php'" style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                <img src="edit.png" style="width: 20px; height: 20px;" alt="Edit">Edit Profile
            </button>
        </div>
    </div>
    
    <!-- Rest of the profile page remains the same -->
    <div class="card_container">
        <div class="card">
            <img src="shopping_bag.png" alt="Listings">
            <h4><?php echo htmlspecialchars($stats['listings']); ?></h4>
            <p>Total Listings</p>
        </div>
        <div class="card">
            <img src="heart.png" alt="Wellness">
            <h4><?php echo htmlspecialchars($stats['wellness_checks']); ?></h4>
            <p>Wellness Checks</p>
        </div>
        <div class="card">
            <img src="chat.png" alt="Support">
            <h4><?php echo htmlspecialchars($stats['support_sessions']); ?></h4>
            <p>Support Sessions</p>
        </div>
        <div class="card">
            <img src="progress.png" alt="Progress">
            <h4><?php echo htmlspecialchars($stats['latest_wellness']); ?></h4>
            <p>Latest Wellness</p>
        </div>
    </div>
    
    <!-- Tabs and content (same as before) -->
    <div class="tab-buttons">
        <button onclick="showTab('tab1')" class="<?php echo $active_tab === 'tab1' ? 'active' : ''; ?>">Listings</button>
        <button onclick="showTab('tab2')" class="<?php echo $active_tab === 'tab2' ? 'active' : ''; ?>">Wellness</button>
        <button onclick="showTab('tab3')" class="<?php echo $active_tab === 'tab3' ? 'active' : ''; ?>">Support</button>
    </div>
    
    <div class="tab-content <?php echo $active_tab === 'tab1' ? 'active' : ''; ?>" id="tab1">
        <div class="upper">
            <h1>My Marketplace Listings</h1>
            <button onclick="window.location.href='marketplace.php?action=add'">+ Add New Listing</button>
        </div>
        <div class="parts">
            <?php if (empty($listings)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">No listings yet. <a href="marketplace.php?action=add">Add your first item!</a></p>
            <?php else: ?>
                <?php foreach($listings as $listing): ?>
                <div class="listCard">
                    <img src="<?php echo htmlspecialchars($listing['image']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                    <div class="vr">
                        <div class="hr1">
                            <h2><?php echo htmlspecialchars($listing['title']); ?></h2>
                            <div class="text" style="background: <?php echo $listing['status_bg']; ?>; color: <?php echo $listing['status_color']; ?>;">
                                <h3><?php echo htmlspecialchars($listing['status']); ?></h3>
                            </div>
                        </div>
                        <h4><?php echo htmlspecialchars($listing['price']); ?></h4>
                        <div class="hr2">
                            <p><?php echo htmlspecialchars($listing['views']); ?></p>
                            <p><?php echo htmlspecialchars($listing['messages']); ?></p>
                            <p>Posted <?php echo htmlspecialchars($listing['posted_date']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Wellness and Support tabs remain the same -->
    <div class="tab-content <?php echo $active_tab === 'tab2' ? 'active' : ''; ?>" id="tab2">
        <div class="upper">
            <h1>Wellness Check History</h1>
            <a href="mental_quiz.php" style="text-decoration: none;">
                <button>♡ Take New Quiz</button>
            </a>
        </div>
        <div class="parts">
            <?php foreach($wellness_history as $wellness): ?>
            <div class="wellnessCard">
                <div class="left">
                    <div class="hr">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-4 h-4 text-muted-foreground" aria-hidden="true">
                            <path d="M8 2v4"></path><path d="M16 2v4"></path>
                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg>
                        <p style="margin-bottom: 10px;"><?php echo htmlspecialchars($wellness['date']); ?></p>
                    </div>
                    <div class="text" style="background: <?php echo $wellness['status_bg']; ?>; color: <?php echo $wellness['status_color']; ?>;">
                        <h3><?php echo htmlspecialchars($wellness['status']); ?></h3>
                    </div>
                </div>
                <div class="right">
                    <h4><?php echo htmlspecialchars($wellness['score']); ?></h4>
                    <p>Wellness Score</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="color: #6b7280; text-align: center; margin-top: 20px; font-size: 12px;">
            All wellness check data is kept private and confidential.
        </p>
    </div>
    
    <div class="tab-content <?php echo $active_tab === 'tab3' ? 'active' : ''; ?>" id="tab3">
        <div class="upper">
            <h1>Peer Support Sessions</h1>
            <a href="peer_support.php" style="text-decoration: none;">
                <button style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                    <img src="chatdarkgreen.png" style="width: 20px; height: 20px;" alt="Chat">Start New Session
                </button>
            </a>
        </div>
        <div class="parts">
            <?php foreach($support_sessions as $session): ?>
            <div class="supportCard">
                <div class="left">
                    <div class="hr">
                        <img src="<?php echo htmlspecialchars($session['mentor_image']); ?>" alt="<?php echo htmlspecialchars($session['mentor_name']); ?>">
                        <div class="vr">
                            <h1><?php echo htmlspecialchars($session['mentor_name']); ?></h1>
                            <p><?php echo htmlspecialchars($session['last_message']); ?></p>
                            <p>Last message: <?php echo htmlspecialchars($session['last_date']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="right">
                    <div class="text" style="background: <?php echo $session['status_bg']; ?>; color: <?php echo $session['status_color']; ?>;">
                        <h3><?php echo htmlspecialchars($session['status']); ?></h3>
                    </div>
                    <?php if($session['active']): ?>
                    <button>Continue Chat</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <hr>
    <footer>
        <img src="logo.png" alt="CampusCare Logo">
        <p>Supporting student well-being through community connection, peer support, and accessible mental health resources.</p>
        <h6>© <?php echo date('Y'); ?> CampusCare. All rights reserved.</h6>
    </footer>
    
    <script src="script2.js"></script>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-buttons button').forEach(button => {
                button.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = '<?php echo $active_tab; ?>';
            if (activeTab) {
                showTab(activeTab);
            }
        });
    </script>
</body>
</html>