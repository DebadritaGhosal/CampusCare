<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: signup.php');
    exit();
}
$user_data = [
    'name' => 'John Doe',
    'email' => 'john.doe@university.edu',
    'year' => 'Junior',
    'major' => 'Computer Science',
    'join_date' => '9/1/2023',
    'profile_pic' => 'dp.png'
];
$stats = [
    'listings' => 3,
    'wellness_checks' => 3,
    'support_sessions' => 3,
    'latest_wellness' => '85%'
];
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
        'posted_date' => '25/01/2025'
    ],
    [
        'id' => 2,
        'image' => 'kyeboard.png',
        'title' => 'Wireless Keyboard',
        'price' => 'Rs. 670/-',
        'status' => 'sold',
        'status_bg' => 'oklch(.379 .146 265.522)',
        'status_color' => 'oklch(.882 .059 254.128)',
        'views' => '67 views',
        'messages' => '8 messages',
        'posted_date' => '10/02/2025'
    ],
    [
        'id' => 3,
        'image' => 'desklamp.png',
        'title' => 'Desk Lamp',
        'price' => 'Rs. 150/-',
        'status' => 'inactive',
        'status_bg' => 'oklch(.21 .034 264.665)',
        'status_color' => 'oklch(.928 .006 264.531)',
        'views' => '12 views',
        'messages' => '1 messages',
        'posted_date' => '15/01/2025'
    ]
];
$wellness_history = [
    [
        'date' => '1/14/2025',
        'score' => '85%',
        'status' => 'good',
        'status_bg' => 'oklch(.379 .146 265.522)',
        'status_color' => 'oklch(.882 .059 254.128)'
    ],
    [
        'date' => '1/7/2025',
        'score' => '92%',
        'status' => 'excellent',
        'status_bg' => 'oklch(.393 .095 152.535)',
        'status_color' => 'oklch(.925 .084 155.995)'
    ],
    [
        'date' => '12/28/2024',
        'score' => '78%',
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
        'last_date' => '30/01/2025',
        'status' => 'ended',
        'status_bg' => 'oklch(.21 .034 264.665)',
        'status_color' => 'oklch(.928 .006 264.531)',
        'active' => false
    ],
    [
        'mentor_image' => 'help1.png',
        'mentor_name' => 'Maya Patel',
        'last_message' => 'How are you feeling about your exams this week?',
        'last_date' => '30/01/2025',
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
    <title>CampusCare - Profile</title>
    <link rel="stylesheet" href="styleProfile.css">
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-buttons button.active {
            background-color: #2E8B57;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="CampusCare Logo">
        </div>
        <nav class="nav">
            <a href="home.php">Home</a>
            <a href="marketplace.php">Marketplace</a>
            <a href="wellness.php">Mental Wellness</a>
            <a href="peer_support.php">Peer Support</a>
            <a href="profile.php" class="active">Profile</a>
        </nav>
        <div class="btns1">
            <button class="toggle-btn" id="theme" onclick="toggleTheme()">
                <span id="moonIcon">☾</span>
            </button>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php">
                    <button id="sign_in">Sign Out</button>
                </a>
            <?php else: ?>
                <a href="signup.php">
                    <button id="sign_in">Sign In</button>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <div class="info">
        <img src="<?php echo htmlspecialchars($user_data['profile_pic']); ?>" alt="Profile picture">
        <div class="part">
            <div class="vrPart">
                <h1><?php echo htmlspecialchars($user_data['name']); ?></h1>
                <a href="mailto:<?php echo htmlspecialchars($user_data['email']); ?>">
                    <?php echo htmlspecialchars($user_data['email']); ?>
                </a>
            </div>
            <div class="hrPart">
                <h2><?php echo htmlspecialchars($user_data['year']); ?> • <?php echo htmlspecialchars($user_data['major']); ?></h2>
                <h2>Member since <?php echo htmlspecialchars($user_data['join_date']); ?></h2>
            </div>
        </div>
        <div class="btn">
            <button style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                <img src="edit.png" style="width: 20px; height: 20px;" alt="Edit">Edit Profile
            </button>
        </div>
    </div>
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
        </div>
    </div>
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