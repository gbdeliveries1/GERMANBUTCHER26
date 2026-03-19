            <!--====== Section 1 ======-->
            <div class="u-s-p-y-60">

                <!--====== Section Content ======-->
                <div class="section__content">
                    <div class="container">
                        <div class="breadcrumb">
                            <div class="breadcrumb__wrap">
                                <ul class="breadcrumb__list">
                                    <li class="has-separator">

                                        <a href="index.php">Home</a></li>
                                    <li class="is-marked">

                                        <a href="index.php?dashboard">My Account</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--====== End - Section 1 ======-->

            <!--====== Section 2 ======-->
            <div class="u-s-p-b-60">

                <!--====== Section Content ======-->
                <div class="section__content">
                    <div class="dash">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">

                                    <!--====== Dashboard Features ======-->
                                    <div class="dash__box dash__box--bg-white dash__box--shadow u-s-m-b-30">
                                        <div class="dash__pad-1">

                                            <span class="dash__text u-s-m-b-16">Hello, <?php echo $user_first_name." ".$user_last_name ?></span>
                                            <ul class="dash__f-list">
                                                <li>

                                                    <a href="index.php?dashboard">Manage My Account</a></li>
                                            	<li>

                                                    <a href="index.php?manage_address">Manage My Addresses</a></li>
                                                <li>

                                                    <a class="dash-active" href="index.php?my-profile">My Profile</a></li>
                                                <!--<li>

                                                    <a href="index.php?address-book">Address Book</a></li>-->
                                                <li>

                                                    <a href="index.php?track-my-order">Track Order</a></li>
                                                <li>

                                                    <a href="index.php?my-orders">My Orders</a></li>
                                                
                                                <!--<li>

                                                    <a href="index.php?cancellation">My Returns & Cancellations</a></li>-->
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="dash__box dash__box--bg-white dash__box--shadow dash__box--w">
                                        <div class="dash__pad-1">
                                            <ul class="dash__w-list">
                                                <a href="index.php?my-orders">
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-2"><i class="fas fa-cart-arrow-down"></i></span>

                                                    <?php
                                                    
		$sql="SELECT count(o.order_id) as c1 from orders o, cart c where o.cart_id=c.cart_id and c.customer_id='$user_id' and o.status not like 'Pending_payment'";
		$result=$conn->query($sql);
		while ($row = $result->fetch_assoc()) {
			$orders_count=$row['c1'];
		}
                                            
        $sql="SELECT count(o.order_id) as c1 from orders o, cart c where o.cart_id=c.cart_id and c.customer_id='$user_id' and o.status='Canceled'";
		$result=$conn->query($sql);
		while ($row = $result->fetch_assoc()) {
			$c_orders_count=$row['c1'];
		}
                                            
        $sql="SELECT count(wi.item_id) as c1 from wishlist_item wi, wishlist w where wi.wishlist_id=w.wishlist_id and w.customer_id='$user_id'";
		$result=$conn->query($sql);
		while ($row = $result->fetch_assoc()) {
			$wishlist_count=$row['c1'];
		}
                                                    
                                                    ?>
                                                    
                                                        <span class="dash__w-text"><?php echo $orders_count ?></span>

                                                        <span class="dash__w-name">Orders Placed</span></div>
                                                </li>
                                                </a>
                                                <a href="index.php?my-orders">
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-1"><i class="fas fa-times"></i></span>

                                                        <span class="dash__w-text"><?php echo $c_orders_count ?></span>

                                                        <span class="dash__w-name">Cancel Orders</span></div>
                                                </li>
                                                </a>
                                                <a href="index.php?wishlist">
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-3"><i class="far fa-heart"></i></span>

                                                        <span class="dash__w-text"><?php echo $wishlist_count ?></span>

                                                        <span class="dash__w-name">Wishlist</span></div>
                                                </li>
                                                </a>
                                            </ul>
                                        </div>
                                    </div>
                                    <!--====== End - Dashboard Features ======-->
                                </div>
                                <div class="col-lg-9 col-md-12">
                                    <div class="dash__box dash__box--shadow dash__box--radius dash__box--bg-white u-s-m-b-30">
                                        <div class="dash__pad-2">
                                            <h1 class="dash__h1 u-s-m-b-14">My Profile</h1>

                                            <!--<span class="dash__text u-s-m-b-30">Look all your info, you could customize your profile.</span>-->
                                            <div class="row">
                                                <div class="col-lg-6 u-s-m-b-30">
                                                    <h2 class="dash__h2 u-s-m-b-8">First Name</h2>
                                                    <!--<span class="dash__text"><?php echo $user_first_name." ".$user_last_name ?></span>-->
                                                    <input class="input-text input-text--primary-style" value="<?php echo $user_id ?>" type="text" id="user_user_id" style="width:100%;display:none;">
                                                    <input class="input-text input-text--primary-style" value="<?php echo $user_first_name ?>" type="text" id="user_first_name" style="width:100%;">
                                                </div>
                                                <div class="col-lg-6 u-s-m-b-30">
                                                    <h2 class="dash__h2 u-s-m-b-8">Last Name</h2>
                                                    <input class="input-text input-text--primary-style" value="<?php echo $user_last_name ?>" type="text" id="user_last_name" style="width:100%;">
                                                </div>
                                                <div class="col-lg-6 u-s-m-b-30">
                                                    <h2 class="dash__h2 u-s-m-b-8">E-mail</h2>
                                                    <!--<span class="dash__text"><?php echo $user_email ?></span>-->
                                                    <input class="input-text input-text--primary-style" value="<?php echo $user_email ?>" type="text" id="user_email" style="width:100%;">
                                                    <!--<div class="dash__link dash__link--secondary">

                                                        <a href="#">Change</a></div>-->
                                                </div>
                                                <div class="col-lg-6 u-s-m-b-30">
                                                    <h2 class="dash__h2 u-s-m-b-8">Phone</h2>
                                                    <!--<span class="dash__text"><?php echo $user_phone_no ?></span>-->
                                                    <input class="input-text input-text--primary-style" value="<?php echo $user_phone_no ?>" type="text" id="user_phone_no" style="width:100%;">
                                                    <!--<div class="dash__link dash__link--secondary">

                                                        <a href="#">Add</a></div>-->
                                                </div>
                                                <div class="col-lg-4 u-s-m-b-30">
                                                    <h2 class="dash__h2 u-s-m-b-8">Birthday</h2>

                                                    <span class="dash__text"><?php echo $user_dob ?></span>
                                                </div>
                                                <!--<div class="col-lg-4 u-s-m-b-30">
                                                    <h2 class="dash__h2 u-s-m-b-8">Gender</h2>

                                                    <span class="dash__text">Male</span>
                                                </div>-->
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <!--<div class="dash__link dash__link--secondary u-s-m-b-30">

                                                        <a data-modal="modal" data-modal-id="#dash-newsletter">Subscribe Newsletter</a></div>
                                                    <div class="u-s-m-b-16">

                                                        <a class="dash__custom-link btn--e-transparent-brand-b-2" href="dash-edit-profile.html">Edit Profile</a></div>-->
                                                    <div>
                                                        <input type='text' style='display:none;' id='result_response'>

                                                        <a class="dash__custom-link btn--e-brand-b-2" href="#/" onclick="update_user_profile();">Update Profile</a>
                                                        <a class="dash__custom-link btn--e-brand-b-2" href="#" style="float:right;">Change Password</a>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--====== End - Section Content ======-->
            </div>
            <!--====== End - Section 2 ======-->