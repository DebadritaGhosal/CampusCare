<?php
session_start();
require_once 'config/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// PHP helper functions
function getSuggestedPriceRange($asking_price) {
    if (!$asking_price) return ['min' => 0, 'max' => 0];
    
    $min = $asking_price * 0.5;  // 50% of asking price
    $max = $asking_price * 1.2;  // 120% of asking price
    
    return [
        'min' => round($min, 2),
        'max' => round($max, 2),
        'suggested_min' => round($asking_price * 0.7, 2),
        'suggested_max' => round($asking_price * 0.9, 2)
    ];
}

function checkForNewBids($product_id, $last_check, $current_user_id) {
    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as new_bids 
         FROM proposals 
         WHERE product_id = ? 
         AND created_at > ? 
         AND buyer_id != ?"
    );
    $stmt->execute([$product_id, $last_check, $current_user_id]);
    return $stmt->fetch()['new_bids'];
}

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
        
        /* New bidding features styles */
        .smart-suggestion {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .auto-bid {
            margin: 15px 0;
            padding: 15px;
            background: #e8f4f8;
            border-radius: 8px;
            border: 1px solid #b8daff;
        }
        
        .smart-slider {
            width: 100%;
            margin: 10px 0;
        }
        
        .bid-graph-container {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        
        .live-bidding-panel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .bid-notification {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 15px;
            display: none;
            max-width: 300px;
        }
        
        .bidding-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }
        
        .best-offer {
            font-size: 1.2em;
            font-weight: bold;
            color: #2E8B57;
        }
        
        .time-left {
            font-size: 1.2em;
            font-weight: bold;
            color: #dc3545;
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
    
    <!-- Live Bidding Notification -->
    <div class="live-bidding-panel" id="liveBiddingPanel">
        <div class="bid-notification" id="bidNotification">
            <h4 style="margin: 0 0 10px 0;">🎯 Live Bidding Update</h4>
            <p id="bidMessage" style="margin: 0 0 10px 0;"></p>
            <button class="btn" onclick="refreshBidding()" style="padding: 5px 10px; font-size: 12px;">Refresh</button>
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
            
            // Get best offer
            $stmt = $pdo->prepare(
                "SELECT MAX(proposed_price) as best_offer 
                 FROM proposals 
                 WHERE product_id = ? AND status = 'pending'"
            );
            $stmt->execute([$product['id']]);
            $best_offer = $stmt->fetch()['best_offer'];
            
            // Calculate time until proposal expires
            $stmt = $pdo->prepare(
                "SELECT TIMESTAMPDIFF(HOUR, NOW(), expires_at) as hours_left 
                 FROM proposals 
                 WHERE product_id = ? AND status = 'pending' 
                 ORDER BY expires_at ASC LIMIT 1"
            );
            $stmt->execute([$product['id']]);
            $time_left = $stmt->fetch()['hours_left'];
            
            // Get suggested price range
            $range = getSuggestedPriceRange($product['asking_price'] ?? 0);
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
                
                <!-- Advanced Bidding Features -->
                <?php if($product['bidding_enabled']): ?>
                <div class="bidding-info">
                    <div style="flex: 1;">
                        <div style="font-size: 0.9em; color: #666;">Current best offer:</div>
                        <div class="best-offer" id="bestOffer_<?php echo $product['id']; ?>">
                            <?php echo $best_offer ? '₹' . number_format($best_offer, 2) : 'No offers yet'; ?>
                        </div>
                    </div>
                    <div style="margin-left: 20px;">
                        <div style="font-size: 0.9em; color: #666;">Time left:</div>
                        <div class="time-left" id="timeLeft_<?php echo $product['id']; ?>">
                            <?php echo $time_left > 0 ? $time_left . 'h' : '48h'; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Bidding History Graph -->
                <div class="bid-graph-container" id="bidGraph_<?php echo $product['id']; ?>">
                    <h5 style="margin: 0 0 10px 0;">📈 Bidding History</h5>
                    <div style="height: 60px; position: relative; border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                        <!-- Bidding points will be drawn here -->
                    </div>
                    <button class="btn" onclick="toggleBidGraph(<?php echo $product['id']; ?>)" style="padding: 5px 10px; font-size: 12px; margin-top: 10px;">
                        Hide Graph
                    </button>
                </div>
                <button class="btn" onclick="toggleBidGraph(<?php echo $product['id']; ?>)" style="padding: 5px 10px; font-size: 12px;">
                    Show Bidding Graph
                </button>
                <?php endif; ?>
                
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
                
                <!-- Smart Price Suggestion -->
                <?php if($range['min'] > 0): ?>
                <div class="smart-suggestion">
                    <h4 style="margin: 0 0 10px 0; color: #856404;">💡 Smart Price Suggestion</h4>
                    <p style="margin: 0 0 10px 0;">Based on bidding history and similar items:</p>
                    
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <input type="range" 
                               min="<?php echo $range['min']; ?>" 
                               max="<?php echo $range['max']; ?>" 
                               value="<?php echo $range['suggested_min']; ?>"
                               class="smart-slider"
                               id="smartSlider_<?php echo $product['id']; ?>"
                               oninput="updateSmartPrice(this, <?php echo $product['id']; ?>)">
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 0.9em;">
                        <span style="color: #dc3545;">Low: ₹<?php echo number_format($range['min'], 2); ?></span>
                        <span style="color: #28a745; font-weight: bold;">
                            Suggested: ₹<span id="smartPrice_<?php echo $product['id']; ?>">
                                <?php echo number_format($range['suggested_min'], 2); ?>
                            </span>
                        </span>
                        <span style="color: #dc3545;">High: ₹<?php echo number_format($range['max'], 2); ?></span>
                    </div>
                    <p style="font-size: 0.8em; color: #856404; margin-top: 10px;">
                        💡 Tip: Bids in the suggested range have 70% higher chance of acceptance
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- Auto-Bid Feature -->
                <?php if($isLoggedIn): ?>
                <div class="auto-bid">
                    <h4 style="margin: 0 0 10px 0; color: #004085;">🤖 Auto-Bid</h4>
                    <p style="margin: 0 0 10px 0;">Set your maximum price and let the system bid for you:</p>
                    
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div style="flex: 1;">
                            <input type="number" 
                                   id="autoBidMax_<?php echo $product['id']; ?>"
                                   placeholder="Your maximum bid"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div style="flex: 1;">
                            <input type="number" 
                                   id="autoBidIncrement_<?php echo $product['id']; ?>"
                                   placeholder="Increment (e.g., 50)"
                                   value="50"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <button class="btn" onclick="setAutoBid(<?php echo $product['id']; ?>)" style="background: #004085; color: white;">
                            Enable Auto-Bid
                        </button>
                    </div>
                    
                    <p style="font-size: 0.8em; color: #004085; margin-top: 10px;">
                        ⚡ Auto-bid will increase your bid automatically when outbid, up to your maximum
                    </p>
                </div>
                <?php endif; ?>
                
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
    let lastBidCheck = {};
    let bidCheckInterval = null;
    
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
    
    // Real-time bidding functions
    function startBidMonitoring(productId) {
        lastBidCheck[productId] = new Date().toISOString();
        
        if (!bidCheckInterval) {
            bidCheckInterval = setInterval(() => {
                Object.keys(lastBidCheck).forEach(productId => {
                    checkNewBids(productId);
                });
            }, 30000); // Check every 30 seconds
        }
    }
    
    function checkNewBids(productId) {
        fetch(`check_new_bids.php?product_id=${productId}&last_check=${lastBidCheck[productId]}`)
            .then(response => response.json())
            .then(data => {
                if (data.new_bids > 0) {
                    showBidNotification(productId, data.new_bids, data.latest_price);
                    lastBidCheck[productId] = new Date().toISOString();
                    
                    // Update best offer display
                    document.getElementById(`bestOffer_${productId}`).textContent = 
                        '₹' + data.latest_price.toLocaleString();
                }
            });
    }
    
    function showBidNotification(productId, count, latestPrice) {
        const notification = document.getElementById('bidNotification');
        const message = document.getElementById('bidMessage');
        
        message.textContent = `🚨 ${count} new bid${count > 1 ? 's' : ''} on this item! 
                               Latest offer: ₹${latestPrice.toLocaleString()}`;
        notification.style.display = 'block';
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 10000);
    }
    
    function refreshBidding() {
        // Refresh all visible product bidding info
        document.querySelectorAll('.list_container').forEach(item => {
            const productId = item.dataset.id;
            if (productId) {
                checkNewBids(productId);
            }
        });
        document.getElementById('bidNotification').style.display = 'none';
    }
    
    function updateSmartPrice(slider, productId) {
        const price = slider.value;
        document.getElementById(`smartPrice_${productId}`).textContent = 
            parseFloat(price).toLocaleString('en-IN', {minimumFractionDigits: 2});
    }
    
    function setAutoBid(productId) {
        const maxBid = document.getElementById(`autoBidMax_${productId}`).value;
        const increment = document.getElementById(`autoBidIncrement_${productId}`).value;
        
        if (!maxBid || !increment) {
            alert('Please enter both maximum bid and increment amount');
            return;
        }
        
        fetch('set_auto_bid.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                max_bid: maxBid,
                increment: increment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Auto-bid enabled! The system will bid for you automatically.');
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
    
    function toggleBidGraph(productId) {
        const graph = document.getElementById(`bidGraph_${productId}`);
        if (graph.style.display === 'none') {
            graph.style.display = 'block';
            loadBidGraph(productId);
        } else {
            graph.style.display = 'none';
        }
    }
    
    function loadBidGraph(productId) {
        fetch(`get_bid_history.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                drawBidGraph(productId, data);
            });
    }
    
    function drawBidGraph(productId, data) {
        const container = document.querySelector(`#bidGraph_${productId} > div`);
        container.innerHTML = '';
        
        if (data.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #666;">No bidding history yet</p>';
            return;
        }
        
        // Create SVG for graph
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', '100%');
        svg.setAttribute('height', '60');
        svg.style.overflow = 'visible';
        
        // Calculate min and max prices
        const prices = data.map(bid => bid.price);
        const minPrice = Math.min(...prices);
        const maxPrice = Math.max(...prices);
        const priceRange = maxPrice - minPrice;
        
        // Draw bidding points
        data.forEach((bid, index) => {
            const x = (index / (data.length - 1)) * 100;
            const y = 60 - ((bid.price - minPrice) / priceRange * 50);
            
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', x + '%');
            circle.setAttribute('cy', y);
            circle.setAttribute('r', '3');
            circle.setAttribute('fill', bid.status === 'accepted' ? '#28a745' : 
                                          bid.status === 'pending' ? '#007bff' : '#dc3545');
            svg.appendChild(circle);
            
            // Add price label for first, last, and highest points
            if (index === 0 || index === data.length - 1 || bid.price === maxPrice) {
                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', x + '%');
                text.setAttribute('y', y - 5);
                text.setAttribute('font-size', '8');
                text.setAttribute('fill', '#666');
                text.textContent = '₹' + Math.round(bid.price);
                svg.appendChild(text);
            }
        });
        
        container.appendChild(svg);
    }
    
    // Contact seller function
    function contactSeller(sellerName) {
        alert(`Opening chat with ${sellerName}...`);
        // Implement chat functionality here
    }
    
    function loadMoreItems() {
        alert('Loading more items...');
        // Implement load more functionality
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
        
        // Start bidding monitoring for all visible products
        document.querySelectorAll('.list_container').forEach(item => {
            const productId = item.dataset.id;
            if (productId) {
                startBidMonitoring(productId);
            }
        });
        
        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                closeModal({stopPropagation: () => {}});
                closeNotifications();
            }
        });
    };
    </script>
</body>
</html>