<?php
session_start();
require_once 'config/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Fetch products
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE status IN ('available', 'negotiating') ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCare - Price Proposal Marketplace</title>
    <link rel="stylesheet" href="styleMarket.css">
    <style>
        /* Indrive-style bidding interface */
        .price-proposal-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .proposal-form {
            display: <?php echo $isLoggedIn ? 'block' : 'none'; ?>;
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .price-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        
        .rupee-symbol {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }
        
        .proposal-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }
        
        .proposal-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .proposal-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        
        .suggest-price-btn {
            background: #48bb78;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .negotiation-badge {
            background: #ed8936;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .active-proposals {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .proposal-item {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .proposal-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-pending { background: #fed7d7; color: #c53030; }
        .status-accepted { background: #c6f6d5; color: #22543d; }
        .status-rejected { background: #fed7d7; color: #c53030; }
        .status-countered { background: #feebc8; color: #744210; }
        
        .counter-offer-form {
            display: none;
            background: #fffaf0;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 1.2em;
        }
        
        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.8em;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .price-comparison {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .suggested-range {
            font-size: 0.9em;
            color: #cbd5e0;
            margin-top: 5px;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .proposal-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            z-index: 1001;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
            color: #718096;
        }
        
        .price-slider {
            width: 100%;
            margin: 20px 0;
        }
        
        .negotiation-chat {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            margin: 15px 0;
        }
        
        .chat-message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .message-buyer {
            background: #ebf8ff;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }
        
        .message-seller {
            background: #f0fff4;
            margin-left: auto;
            border-bottom-right-radius: 0;
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
            <a href="marketplace.php" class="active">Marketplace</a>
            <a href="wellness.php">Mental Wellness</a>
            <a href="#">Peer Support</a>
            <a href="profile.php">Profile</a>
            <?php if($isLoggedIn): ?>
            <div class="notification-bell" onclick="showNotifications()">
                🔔
                <span class="notification-count" id="notificationCount">0</span>
            </div>
            <?php endif; ?>
        </nav>
        <div class="btns1">
            <button class="toggle-btn" id="theme" onclick="toggleTheme()"><span id="moonIcon">☾</span></button>
            <?php if($isLoggedIn): ?>
                <button id="sign_out" onclick="window.location.href='logout.php'">Sign Out</button>
            <?php else: ?>
                <button id="sign_in" onclick="window.location.href='signup.html'">Sign In</button>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Proposal Modal -->
    <div class="modal-overlay" id="proposalModal" onclick="closeModal(event)">
        <div class="proposal-modal" onclick="event.stopPropagation()">
            <span class="close-modal" onclick="closeModal(event)">&times;</span>
            <div id="proposalContent">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
    
    <!-- Notifications Modal -->
    <div class="modal-overlay" id="notificationsModal" onclick="closeNotifications()">
        <div class="proposal-modal" onclick="event.stopPropagation()">
            <span class="close-modal" onclick="closeNotifications()">&times;</span>
            <h3>Your Proposals & Offers</h3>
            <div id="notificationsContent">
                <!-- Notifications will load here -->
            </div>
        </div>
    </div>
    
    <div class="heading">
        <h1>🛵 Campus Marketplace - Suggest Your Price</h1>
        <p>See an item you like? Suggest your price! Sellers can accept, reject, or make a counter-offer.</p>
    </div>
    
    <div class="imageContainer">
        <?php foreach($products as $product): 
            // Check if user has already made a proposal
            $hasProposal = false;
            if($isLoggedIn) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM proposals WHERE product_id = ? AND buyer_id = ? AND status IN ('pending', 'countered')");
                $stmt->execute([$product['id'], $user_id]);
                $hasProposal = $stmt->fetchColumn() > 0;
            }
            
            // Get active proposals count
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM proposals WHERE product_id = ? AND status IN ('pending', 'countered')");
            $stmt->execute([$product['id']]);
            $proposalCount = $stmt->fetchColumn();
        ?>
        <div class="list_container" data-id="<?php echo $product['id']; ?>">
            <div class="topimg">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                <?php if($product['bidding_enabled']): ?>
                    <div class="negotiation-badge">Price Negotiable</div>
                <?php endif; ?>
            </div>
            <div class="content">
                <div class="parting">
                    <h2><?php echo htmlspecialchars($product['title']); ?></h2>
                    <?php if($product['asking_price']): ?>
                        <h3>₹<?php echo number_format($product['asking_price'], 2); ?></h3>
                    <?php else: ?>
                        <h3 style="color: #718096;">Price on Proposal</h3>
                    <?php endif; ?>
                </div>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                
                <!-- Indrive-style Price Proposal Section -->
                <?php if($product['bidding_enabled'] && $product['seller_id'] != $user_id): ?>
                <div class="price-proposal-card">
                    <h4>💡 Suggest Your Price</h4>
                    
                    <?php if($proposalCount > 0): ?>
                    <div class="active-proposals">
                        <small><?php echo $proposalCount; ?> active proposal(s)</small>
                        <?php 
                        // Show top 2 proposals
                        $stmt = $pdo->prepare("SELECT proposed_price, created_at FROM proposals WHERE product_id = ? AND status IN ('pending', 'countered') ORDER BY proposed_price DESC LIMIT 2");
                        $stmt->execute([$product['id']]);
                        $topProposals = $stmt->fetchAll();
                        foreach($topProposals as $prop): ?>
                        <div class="proposal-item">
                            <span>₹<?php echo number_format($prop['proposed_price'], 2); ?></span>
                            <small><?php echo date('H:i', strtotime($prop['created_at'])); ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($isLoggedIn): ?>
                        <?php if($hasProposal): ?>
                            <button class="proposal-btn" onclick="viewMyProposal(<?php echo $product['id']; ?>)">
                                📝 View Your Proposal
                            </button>
                        <?php else: ?>
                            <button class="proposal-btn" onclick="openProposalModal(<?php echo $product['id']; ?>)">
                                💰 Suggest Your Price
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="login-prompt">
                            <a href="signup.html" style="color: white; text-decoration: underline;">Login</a> to suggest a price
                        </div>
                    <?php endif; ?>
                    
                    <?php if($product['asking_price']): ?>
                    <div class="price-comparison">
                        <div>
                            <small>Seller's Price</small>
                            <div>₹<?php echo number_format($product['asking_price'], 2); ?></div>
                        </div>
                        <div style="text-align: center;">
                            <small>Suggested Range</small>
                            <div>₹<?php echo number_format($product['asking_price'] * 0.7, 2); ?> - ₹<?php echo number_format($product['asking_price'] * 0.9, 2); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="parting">
                    <p><span aria-label="Location: <?php echo htmlspecialchars($product['location']); ?>">
                        ⚲ <?php echo htmlspecialchars($product['location']); ?>
                    </span></p>
                    <h4><?php echo htmlspecialchars($product['condition']); ?></h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6><?php echo htmlspecialchars($product['seller_name']); ?></h6>
                </div>
                <button onclick="contactSeller('<?php echo htmlspecialchars($product['seller_name']); ?>')">
                    🗨 Contact Seller
                </button>
                
                <!-- For sellers viewing their own items -->
                <?php if($isLoggedIn && $product['seller_id'] == $user_id): ?>
                <button class="suggest-price-btn" onclick="viewProposals(<?php echo $product['id']; ?>)">
                    📋 View Proposals (<?php echo $proposalCount; ?>)
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <button class="btn" onclick="loadMoreItems()">Load More Items</button>
    
    <script>
    // Global variables
    let currentProductId = null;
    
    // Open proposal modal
    function openProposalModal(productId) {
        currentProductId = productId;
        
        fetch(`get_product_info.php?id=${productId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('proposalContent').innerHTML = data;
                document.getElementById('proposalModal').style.display = 'block';
                initializeSlider();
            });
    }
    
    // Submit proposal
    function submitProposal() {
        const price = document.getElementById('proposedPrice').value;
        const message = document.getElementById('proposalMessage').value;
        
        if(!price || price <= 0) {
            alert('Please enter a valid price');
            return;
        }
        
        const formData = new FormData();
        formData.append('product_id', currentProductId);
        formData.append('price', price);
        formData.append('message', message);
        
        fetch('submit_proposal.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Price suggestion submitted! The seller will review your offer.');
                closeModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
    
    // View seller's proposals
    function viewProposals(productId) {
        fetch(`view_proposals.php?product_id=${productId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('proposalContent').innerHTML = data;
                document.getElementById('proposalModal').style.display = 'block';
            });
    }
    
    // View buyer's own proposal
    function viewMyProposal(productId) {
        fetch(`view_my_proposal.php?product_id=${productId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('proposalContent').innerHTML = data;
                document.getElementById('proposalModal').style.display = 'block';
            });
    }
    
    // Seller actions
    function acceptProposal(proposalId) {
        if(confirm('Accept this price? The buyer will be notified.')) {
            fetch(`seller_action.php?action=accept&id=${proposalId}`)
                .then(response => response.json())
                .then(data => {
                    alert('Offer accepted! Contact the buyer to arrange pickup.');
                    closeModal();
                    location.reload();
                });
        }
    }
    
    function rejectProposal(proposalId) {
        const reason = prompt('Reason for rejection (optional):');
        fetch(`seller_action.php?action=reject&id=${proposalId}&reason=${encodeURIComponent(reason || '')}`)
            .then(response => response.json())
            .then(data => {
                alert('Offer rejected.');
                closeModal();
                location.reload();
            });
    }
    
    function makeCounterOffer(proposalId) {
        const counterPrice = prompt('Enter your counter offer price:');
        if(counterPrice && !isNaN(counterPrice)) {
            const message = prompt('Message to buyer (optional):');
            fetch(`seller_action.php?action=counter&id=${proposalId}&price=${counterPrice}&message=${encodeURIComponent(message || '')}`)
                .then(response => response.json())
                .then(data => {
                    alert('Counter offer sent!');
                    closeModal();
                    location.reload();
                });
        }
    }
    
    // Buyer actions
    function acceptCounter(proposalId) {
        if(confirm('Accept the counter offer?')) {
            fetch(`buyer_action.php?action=accept_counter&id=${proposalId}`)
                .then(response => response.json())
                .then(data => {
                    alert('Counter offer accepted! Contact the seller.');
                    closeModal();
                    location.reload();
                });
        }
    }
    
    function rejectCounter(proposalId) {
        fetch(`buyer_action.php?action=reject_counter&id=${proposalId}`)
            .then(response => response.json())
            .then(data => {
                alert('Counter offer rejected.');
                closeModal();
                location.reload();
            });
    }
    
    function withdrawProposal(proposalId) {
        if(confirm('Withdraw your proposal?')) {
            fetch(`buyer_action.php?action=withdraw&id=${proposalId}`)
                .then(response => response.json())
                .then(data => {
                    alert('Proposal withdrawn.');
                    closeModal();
                    location.reload();
                });
        }
    }
    
    // Modal functions
    function closeModal(event) {
        if(event) event.stopPropagation();
        document.getElementById('proposalModal').style.display = 'none';
        currentProductId = null;
    }
    
    function showNotifications() {
        fetch('get_notifications.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('notificationsContent').innerHTML = data;
                document.getElementById('notificationsModal').style.display = 'block';
            });
    }
    
    function closeNotifications() {
        document.getElementById('notificationsModal').style.display = 'none';
    }
    
    // Price slider initialization
    function initializeSlider() {
        const slider = document.getElementById('priceSlider');
        const priceDisplay = document.getElementById('sliderPrice');
        const askingPrice = parseFloat(document.getElementById('askingPrice').value);
        
        if(slider && priceDisplay) {
            const minPrice = askingPrice * 0.5;
            const maxPrice = askingPrice * 1.2;
            
            slider.min = minPrice;
            slider.max = maxPrice;
            slider.value = askingPrice * 0.8;
            
            priceDisplay.textContent = '₹' + parseInt(slider.value).toLocaleString();
            
            slider.oninput = function() {
                priceDisplay.textContent = '₹' + parseInt(this.value).toLocaleString();
                document.getElementById('proposedPrice').value = this.value;
            };
        }
    }
    
    // Update notification count
    function updateNotificationCount() {
        fetch('count_notifications.php')
            .then(response => response.json())
            .then(data => {
                if(data.count > 0) {
                    document.getElementById('notificationCount').textContent = data.count;
                    document.getElementById('notificationCount').style.display = 'flex';
                } else {
                    document.getElementById('notificationCount').style.display = 'none';
                }
            });
    }
    
    // Initialize on load
    window.onload = function() {
        // Theme loading
        const savedTheme = localStorage.getItem("theme");
        const icon = document.getElementById("moonIcon");
        
        if(savedTheme === "dark") {
            document.body.classList.add("dark-mode");
            icon.textContent = "☀︎";
        }
        
        // Update notifications if logged in
        <?php if($isLoggedIn): ?>
        updateNotificationCount();
        setInterval(updateNotificationCount, 30000); // Update every 30 seconds
        <?php endif; ?>
        
        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                closeModal({stopPropagation: () => {}});
                closeNotifications();
            }
        });
    };
    
    // Contact seller function
    function contactSeller(sellerName) {
        alert(`Opening chat with ${sellerName}...`);
        // Implement chat functionality here
    }
    </script>
</body>
</html>