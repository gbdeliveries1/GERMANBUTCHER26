<?php
$redirect_after = $_SESSION['redirect_after_login'] ?? '';
?>
<style>
.si-wrap{min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:linear-gradient(135deg,#f8f9fa,#e9ecef)}
.si-card{width:100%;max-width:420px;background:#fff;border-radius:20px;box-shadow:0 15px 50px rgba(0,0,0,0.1);overflow:hidden}
.si-head{background:linear-gradient(135deg,#ff5000,#ff8533);padding:35px;text-align:center;color:#fff}
.si-icon{width:70px;height:70px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;font-size:30px}
.si-body{padding:35px}
.si-group{margin-bottom:20px}
.si-group label{display:block;margin-bottom:8px;font-size:13px;font-weight:600;color:#444}
.si-group input{width:100%;padding:14px 15px 14px 45px;border:2px solid #e5e5e5;border-radius:10px;font-size:15px;box-sizing:border-box}
.si-group input:focus{outline:none;border-color:#ff5000}
.si-input-wrap{position:relative}
.si-input-wrap i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#aaa}
.si-input-wrap .toggle{position:absolute;right:15px;top:50%;transform:translateY(-50%);color:#aaa;cursor:pointer}
.si-btn{width:100%;padding:16px;background:linear-gradient(135deg,#ff5000,#ff7033);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer}
.si-btn:hover{box-shadow:0 5px 20px rgba(255,80,0,0.4)}
.si-btn:disabled{background:#ccc}
.si-msg{padding:12px;border-radius:8px;margin-bottom:20px;font-size:14px;display:none}
.si-msg.show{display:block}
.si-msg.success{background:#d4edda;color:#155724}
.si-msg.error{background:#f8d7da;color:#721c24}
.si-msg.info{background:#cce5ff;color:#004085}
.si-foot{text-align:center;padding:25px;background:#f9f9f9;border-top:1px solid #eee}
.si-foot a{color:#ff5000;text-decoration:none}
.si-spinner{display:inline-block;width:18px;height:18px;border:3px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<div class="si-wrap">
    <div class="si-card">
        <div class="si-head">
            <div class="si-icon"><i class="fas fa-user"></i></div>
            <h1 style="margin:0 0 5px;font-size:24px;">Welcome Back</h1>
            <p style="margin:0;opacity:0.9;">Sign in to continue</p>
        </div>
        
        <div class="si-body">
            <?php if ($redirect_after === 'checkout'): ?>
            <div class="si-msg show info">
                <i class="fas fa-shopping-cart"></i> Sign in to complete your checkout
            </div>
            <?php endif; ?>
            
            <div class="si-msg" id="siMsg"></div>
            
            <form onsubmit="doLogin(event)">
                <div class="si-group">
                    <label>Email, Username or Phone</label>
                    <div class="si-input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="siUser" placeholder="Enter email or phone" required>
                    </div>
                </div>
                
                <div class="si-group">
                    <label>Password</label>
                    <div class="si-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="siPass" placeholder="Enter password" required>
                        <i class="fas fa-eye toggle" onclick="togglePass()"></i>
                    </div>
                </div>
                
                <button type="submit" class="si-btn" id="siBtn">Sign In</button>
            </form>
            
            <p style="text-align:center;margin-top:20px;">
                <a href="index.php?lost-password" style="color:#ff5000;font-size:13px;">Forgot Password?</a>
            </p>
        </div>
        
        <div class="si-foot">
            <p style="margin:0 0 10px;color:#666;">Don't have an account? <a href="index.php?sign-up"><b>Sign Up</b></a></p>
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</div>

<script>
function togglePass() {
    var input = document.getElementById('siPass');
    var icon = document.querySelector('.toggle');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function doLogin(e) {
    e.preventDefault();
    
    var user = document.getElementById('siUser').value.trim();
    var pass = document.getElementById('siPass').value;
    var btn = document.getElementById('siBtn');
    var msg = document.getElementById('siMsg');
    
    if (!user || !pass) {
        msg.className = 'si-msg show error';
        msg.innerHTML = 'Please fill all fields';
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="si-spinner"></span> Signing in...';
    msg.className = 'si-msg';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'action/login.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        var res = xhr.responseText.trim().toUpperCase();
        
        if (res === 'ADMIN') {
            msg.className = 'si-msg show success';
            msg.innerHTML = '✓ Admin login successful!';
            setTimeout(function() { window.location.href = 'user/admin/'; }, 1000);
        } else if (res === 'CLIENT' || res === 'SELLER' || res === 'SUCCESS') {
            msg.className = 'si-msg show success';
            msg.innerHTML = '✓ Login successful!';
            setTimeout(function() {
                <?php if ($redirect_after === 'checkout'): ?>
                window.location.href = 'index.php?checkout';
                <?php else: ?>
                window.location.href = 'index.php';
                <?php endif; ?>
            }, 1000);
        } else if (res === 'WRONG_PASSWORD') {
            msg.className = 'si-msg show error';
            msg.innerHTML = '✗ Wrong password';
            resetBtn();
        } else if (res === 'NOT_FOUND') {
            msg.className = 'si-msg show error';
            msg.innerHTML = '✗ Account not found';
            resetBtn();
        } else if (res === 'INACTIVE') {
            msg.className = 'si-msg show error';
            msg.innerHTML = '✗ Account is inactive';
            resetBtn();
        } else {
            msg.className = 'si-msg show error';
            msg.innerHTML = '✗ ' + res;
            resetBtn();
        }
    };
    
    xhr.onerror = function() {
        msg.className = 'si-msg show error';
        msg.innerHTML = '✗ Connection error';
        resetBtn();
    };
    
    xhr.send('username=' + encodeURIComponent(user) + '&password=' + encodeURIComponent(pass));
}

function resetBtn() {
    var btn = document.getElementById('siBtn');
    btn.disabled = false;
    btn.innerHTML = 'Sign In';
}
</script>