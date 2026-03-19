// Clear cart after WhatsApp order - SYNCHRONOUS
function clearCartAfterWhatsApp(callback) {
    if (cartId && cartId !== '0' && cartId !== '') {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/clear_cart.php', false); // SYNCHRONOUS (false)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        try {
            xhr.send('cart_id=' + encodeURIComponent(cartId));
            console.log('Cart clear response:', xhr.responseText);
            
            if (callback && typeof callback === 'function') {
                callback(true);
            }
        } catch (e) {
            console.error('Error clearing cart:', e);
            if (callback && typeof callback === 'function') {
                callback(false);
            }
        }
    } else {
        if (callback && typeof callback === 'function') {
            callback(false);
        }
    }
}

// Submit WhatsApp Order - UPDATED
function submitWhatsAppOrder() {
    var name = document.getElementById('wa_customer_name').value.trim();
    var phone = document.getElementById('wa_customer_phone').value.trim();
    var province = document.getElementById('wa_province').value;
    var district = document.getElementById('wa_district').value;
    var sector = document.getElementById('wa_sector').value;
    var addressDetails = document.getElementById('wa_address_details').value.trim();
    var notes = document.getElementById('wa_notes').value.trim();
    
    // Validation
    if (!name) {
        alert('Please enter your name');
        document.getElementById('wa_customer_name').focus();
        return;
    }
    if (!phone) {
        alert('Please enter your phone number');
        document.getElementById('wa_customer_phone').focus();
        return;
    }
    if (!province) {
        alert('Please select your Province');
        document.getElementById('wa_province').focus();
        return;
    }
    if (!district) {
        alert('Please select your District');
        document.getElementById('wa_district').focus();
        return;
    }
    if (!sector) {
        alert('Please select your Sector');
        document.getElementById('wa_sector').focus();
        return;
    }
    if (!addressDetails) {
        alert('Please enter your street/house details');
        document.getElementById('wa_address_details').focus();
        return;
    }
    
    // Disable submit button to prevent double submission
    var submitBtn = document.querySelector('.wa-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
    
    // Calculate grand total
    var grandTotal = cartSubtotal + waDeliveryFee;
    
    // Build WhatsApp message
    var message = "🛒 *NEW ORDER FROM GBDELIVERING*\n";
    message += "━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    message += "👤 *Customer Details*\n";
    message += "• Name: " + name + "\n";
    message += "• Phone: " + phone + "\n\n";
    
    message += "📍 *Delivery Address*\n";
    message += "• Province: " + province + "\n";
    message += "• District: " + district + "\n";
    message += "• Sector: " + sector + "\n";
    message += "• Details: " + addressDetails + "\n";
    if (notes) {
        message += "• Notes: " + notes + "\n";
    }
    message += "\n";
    
    message += "📦 *Order Items*\n";
    message += "━━━━━━━━━━━━━━━━━━━━━━\n";
    
    cartItemsData.forEach(function(item, index) {
        message += (index + 1) + ". " + item.name + "\n";
        message += "   Qty: " + item.qty + " " + item.unit + "\n";
        message += "   Price: " + Number(item.price).toLocaleString() + " RWF\n\n";
    });
    
    message += "━━━━━━━━━━━━━━━━━━━━━━\n";
    message += "📊 *Order Summary*\n";
    message += "• Subtotal: " + Number(cartSubtotal).toLocaleString() + " RWF\n";
    message += "• Delivery Fee: " + Number(waDeliveryFee).toLocaleString() + " RWF\n";
    message += "• *GRAND TOTAL: " + Number(grandTotal).toLocaleString() + " RWF*\n\n";
    
    if (waDeliveryTime) {
        message += "🕐 Estimated Delivery: " + waDeliveryTime + "\n\n";
    }
    
    message += "✅ Please confirm this order.\n";
    message += "Thank you for shopping with us! 🙏";
    
    // WhatsApp phone number
    var whatsappNumber = "250783654454";
    
    // Encode message for URL
    var encodedMessage = encodeURIComponent(message);
    
    // WhatsApp URL
    var whatsappURL = "https://wa.me/" + whatsappNumber + "?text=" + encodedMessage;
    
    // FIRST: Clear the cart (synchronous)
    clearCartAfterWhatsApp(function(success) {
        console.log('Cart cleared:', success);
    });
    
    // Close modal
    closeWhatsAppModal();
    
    // Show success message
    showOrderSuccess(whatsappURL);
}

// Show success message - UPDATED to open WhatsApp after showing message
function showOrderSuccess(whatsappURL) {
    var overlay = document.createElement('div');
    overlay.id = 'order-success-overlay';
    overlay.innerHTML = '<div class="success-content">' +
        '<div class="success-icon"><i class="fas fa-check-circle"></i></div>' +
        '<h2>Order Sent Successfully!</h2>' +
        '<p>Your cart has been cleared.<br>Opening WhatsApp to send your order...</p>' +
        '<p class="redirect-text">Redirecting to shop in <span id="countdown">5</span> seconds...</p>' +
        '</div>';
    
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;' +
        'background:rgba(0,0,0,0.85);display:flex;align-items:center;' +
        'justify-content:center;z-index:99999;';
    
    var style = document.createElement('style');
    style.textContent = '.success-content{background:#fff;padding:40px;border-radius:12px;' +
        'text-align:center;max-width:400px;animation:popIn 0.3s ease;}' +
        '@keyframes popIn{from{transform:scale(0.8);opacity:0;}to{transform:scale(1);opacity:1;}}' +
        '.success-icon{width:80px;height:80px;background:#d4edda;border-radius:50%;' +
        'display:flex;align-items:center;justify-content:center;margin:0 auto 20px;}' +
        '.success-icon i{font-size:40px;color:#28a745;}' +
        '.success-content h2{color:#28a745;margin:0 0 10px;font-size:22px;}' +
        '.success-content p{color:#666;margin:0 0 10px;font-size:14px;}' +
        '.redirect-text{color:#999!important;font-style:italic;font-size:13px!important;}';
    
    document.head.appendChild(style);
    document.body.appendChild(overlay);
    
    // Open WhatsApp immediately
    window.open(whatsappURL, '_blank');
    
    // Countdown and redirect
    var count = 5;
    var countdownEl = document.getElementById('countdown');
    var interval = setInterval(function() {
        count--;
        if (countdownEl) countdownEl.textContent = count;
        if (count <= 0) {
            clearInterval(interval);
            // Force reload to show empty cart
            window.location.href = 'index.php?shop';
        }
    }, 1000);
}