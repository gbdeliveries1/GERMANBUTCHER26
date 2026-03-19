<?php
// Cart Fix - Include this in front-script.php or before </body>
?>
<!-- ============================================ -->
<!-- CART FIX - Handles SUCCESS response properly -->
<!-- ============================================ -->
<script>
(function() {
    'use strict';
    
    // Wait for jQuery to load
    var checkJQuery = setInterval(function() {
        if (typeof jQuery !== 'undefined') {
            clearInterval(checkJQuery);
            initCartFix();
        }
    }, 100);
    
    function initCartFix() {
        var $ = jQuery;
        
        // ============================================
        // Override the add to cart function
        // ============================================
        window.addToCartFixed = function(product_id, price, qty) {
            qty = qty || 1;
            var customer_id = $('#customer_temp_id').val() || '';
            
            $.ajax({
                url: 'action/insert.php',
                type: 'POST',
                data: {
                    action: 'ADD_TO_CART',
                    product_id: product_id,
                    price: price,
                    product_quantity: qty,
                    customer_id: customer_id
                },
                beforeSend: function() {
                    // Show loading
                    Swal.fire({
                        title: 'Adding to cart...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    response = response.trim();
                    
                    if (response === 'SUCCESS' || response.indexOf('SUCCESS') > -1) {
                        // Close loading
                        Swal.close();
                        
                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            text: 'Product has been added to your cart',
                            showConfirmButton: true,
                            confirmButtonText: 'Continue Shopping',
                            showCancelButton: true,
                            cancelButtonText: 'View Cart',
                            confirmButtonColor: '#ff5000',
                            cancelButtonColor: '#28a745',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(function(result) {
                            if (result.dismiss === Swal.DismissReason.cancel) {
                                window.location.href = 'index.php?cart';
                            }
                        });
                        
                        // Update cart count
                        updateCartCount();
                        
                        // Close any open modals
                        closeAllModals();
                        
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response || 'Failed to add to cart'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Please try again'
                    });
                }
            });
        };
        
        // ============================================
        // Update cart count
        // ============================================
        window.updateCartCount = function() {
            $.post('action/insert.php', { action: 'GET_CART_COUNT' }, function(count) {
                count = parseInt(count) || 0;
                $('#cart_items_count_1, #cart_items_count_2, .cart-count, .total-item-round').text(count);
            });
        };
        
        // ============================================
        // Close all modals
        // ============================================
        window.closeAllModals = function() {
            // Bootstrap modals
            $('.modal').modal('hide');
            
            // Remove backdrops
            $('.modal-backdrop').remove();
            
            // Reset body
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'padding-right': ''
            });
            
            // Hide quick-look modal
            $('#quick-look').modal('hide');
        };
        
        // ============================================
        // Override existing cart functions
        // ============================================
        
        // Override add_to_cart if it exists
        if (typeof window.add_to_cart !== 'undefined') {
            var originalAddToCart = window.add_to_cart;
            window.add_to_cart = function(product_id, price, qty) {
                addToCartFixed(product_id, price, qty);
            };
        }
        
        // Override addToCart if it exists
        if (typeof window.addToCart !== 'undefined') {
            window.addToCart = function(product_id, price, qty) {
                addToCartFixed(product_id, price, qty);
            };
        }
        
        // ============================================
        // Intercept AJAX responses
        // ============================================
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url && settings.url.indexOf('insert.php') > -1) {
                var response = (xhr.responseText || '').trim();
                
                // Check if SUCCESS is being displayed raw
                if (response === 'SUCCESS') {
                    // Find and hide any element showing just "SUCCESS"
                    $('body *').each(function() {
                        var $el = $(this);
                        if ($el.children().length === 0 && $el.text().trim() === 'SUCCESS') {
                            // Close modal
                            $el.closest('.modal').modal('hide');
                            
                            // Show toast instead
                            showCartToast('✓ Added to cart!');
                            
                            // Update cart
                            updateCartCount();
                            
                            // Hide the element
                            $el.hide();
                        }
                    });
                }
            }
        });
        
        // ============================================
        // Check for stuck SUCCESS text periodically
        // ============================================
        setInterval(function() {
            // Find any visible SUCCESS text
            $('.modal-body, .modal-content, #result_modal').each(function() {
                var $el = $(this);
                if ($el.text().trim() === 'SUCCESS' || $el.html().trim() === 'SUCCESS') {
                    // Close modal
                    $el.closest('.modal').modal('hide');
                    closeAllModals();
                    
                    // Show notification
                    showCartToast('✓ Added to cart!');
                    
                    // Update cart
                    updateCartCount();
                    
                    // Clear the element
                    $el.html('');
                }
            });
        }, 500);
        
        // ============================================
        // Toast notification
        // ============================================
        window.showCartToast = function(message, type) {
            type = type || 'success';
            
            // Remove existing toasts
            $('.gb-cart-toast').remove();
            
            var bgColor = type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : '#dc3545';
            
            var $toast = $('<div class="gb-cart-toast">')
                .html('<i class="fas fa-shopping-cart"></i> ' + message)
                .css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'background': bgColor,
                    'color': '#fff',
                    'padding': '18px 30px',
                    'border-radius': '12px',
                    'font-size': '16px',
                    'font-weight': '600',
                    'z-index': '999999',
                    'box-shadow': '0 10px 40px rgba(0,0,0,0.3)',
                    'display': 'none'
                })
                .appendTo('body')
                .fadeIn(300);
            
            setTimeout(function() {
                $toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        };
        
        // ============================================
        // Get cart items (fix existing function)
        // ============================================
        window.get_cart_items = function() {
            var customer_temp_id = $('#customer_temp_id').val() || '';
            
            $.post('action/select.php', {
                action: 'GET_CART_ITEMS',
                customer_id: customer_temp_id
            }, function(response) {
                // Update cart count
                updateCartCount();
            });
        };
        
        // ============================================
        // Get cart items description (for mini cart)
        // ============================================
        window.get_cart_items_desc = function() {
            var customer_temp_id = $('#customer_temp_id').val() || '';
            
            $.post('action/select.php', {
                action: 'GET_CART_ITEMS_DESC',
                customer_id: customer_temp_id
            }, function(response) {
                $('#cart_response').html(response);
            });
        };
        
        // ============================================
        // Initialize cart count on page load
        // ============================================
        updateCartCount();
        
        console.log('Cart fix initialized successfully');
    }
})();
</script>