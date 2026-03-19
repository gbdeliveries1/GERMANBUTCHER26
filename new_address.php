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

                                        <a href="index.php?dashboard">My Addresses</a></li>
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

                                                    <a class="dash-active" href="index.php?manage_address">Manage My Addresses</a></li>
                                                <li>

                                                    <a href="index.php?my-profile">My Profile</a></li>
                                                <!--<li>

                                                    <a href="index.php?address-book">Address Book</a></li>-->
                                                <li>

                                                    <a href="index.php?track-my-order">Track Order</a></li>
                                                <li>

                                                    <a href="index.php?my-orders">My Orders</a></li>
                                                
                                                <li>

                                                    <a href="index.php?cancellation">My Returns & Cancellations</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="dash__box dash__box--bg-white dash__box--shadow dash__box--w">
                                        <div class="dash__pad-1">
                                            <ul class="dash__w-list">
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
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-1"><i class="fas fa-times"></i></span>

                                                        <span class="dash__w-text"><?php echo $c_orders_count ?></span>

                                                        <span class="dash__w-name">Cancel Orders</span></div>
                                                </li>
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-3"><i class="far fa-heart"></i></span>

                                                        <span class="dash__w-text"><?php echo $wishlist_count ?></span>

                                                        <span class="dash__w-name">Wishlist</span></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!--====== End - Dashboard Features ======-->
                                </div>
                                <div class="col-lg-9 col-md-12">
                                    <div class="dash__box dash__box--shadow dash__box--radius dash__box--bg-white u-s-m-b-30">
                                        <div class="dash__pad-2">
                                            <h1 class="dash__h1 u-s-m-b-14">New Address</h1>

                                            <span class="dash__text u-s-m-b-30">Add another delivery address.</span>
                                            <!--<form class="m-order u-s-m-b-30">
                                                <div class="m-order__select-wrapper">

                                                    <label class="u-s-m-r-8" for="my-order-sort">Show:</label><select class="select-box select-box--primary-style" id="my-order-sort">
                                                        <option selected>Last 5 orders</option>
                                                        <option>Last 15 days</option>
                                                        <option>Last 30 days</option>
                                                        <option>Last 6 months</option>
                                                        <option>Orders placed in 2018</option>
                                                        <option>All Orders</option>
                                                    </select></div>
                                            </form>-->
                                        	<div>
                                            	<a class="dash__custom-link btn--e-brand-b-2" href="index.php?manage_address">View Addresses</a>
                                        	</div><br>
                                            <div class="m-order__list checkout-f">
                                            
                                            <div class="m-order__get">

                                                	<div class="manage-o__header">
                                            
<form class="checkout-f__delivery" method="post" action="#/" onsubmit="save_address('<?php echo $user_id ?>');return false;">
                                        <div class="u-s-m-b-30">
                                               

                                            <!--====== Country ======-->
                                            <div class="u-s-m-b-15">

                                                <!--====== Select Box ======-->

                                                <label class="gl-label" for="billing-country">STATE / PROVINCE *</label><select class="select-box select-box--primary-style" id="province" data-bill="" required onclick='get_districts();' onchange='get_districts();'>
                                                    <option selected value="">Choose State/Province</option>
                                                    <?php
                                                    $sql4 = "SELECT province from rw_location group by province";
                                                    $result4 = $conn->query($sql4);

                                                    while ($row4 = $result4->fetch_assoc()) {
                                                        $province = $row4['province'];
                                                        ?>
                                                        <option value="<?php echo $province; ?>"><?php echo $province; ?></option>
                                                        <?php 
                                                    } ?>
                                                </select>
                                                <!--====== End - Select Box ======-->
                                            </div>
                                            <!--====== End - Country ======-->


                                            <div class="u-s-m-b-15">

                                                <!--====== Select Box ======-->

                                                <label class="gl-label" for="billing-state">DISTRICT *</label><select class="select-box select-box--primary-style" id="district" data-bill=""required onclick='get_sectors();' onchange='get_sectors();'>
                                                    <option selected value="">Choose State/Province first</option>
                                                </select>
                                                <!--====== End - Select Box ======-->
                                            </div>

                                            <div class="u-s-m-b-15">

                                                <!--====== Select Box ======-->

                                                <label class="gl-label" for="billing-state">SECTOR *</label><select class="select-box select-box--primary-style" id="sector" data-bill="" required onclick='get_cells();' onchange='get_cells();'>
                                                    <option selected value="">Choose District first</option>
                                                </select>
                                                <!--====== End - Select Box ======-->
                                            </div>

                                            <div class="u-s-m-b-15">

                                                <!--====== Select Box ======-->

                                                <label class="gl-label" for="billing-state">CELL *</label><select class="select-box select-box--primary-style" id="cell" data-bill="" required onclick='get_villages();' onchange='get_villages();'>
                                                    <option selected value="">Choose Sector first</option>
                                                </select>
                                                <!--====== End - Select Box ======-->
                                            </div>

                                            <div class="u-s-m-b-15">

                                                <!--====== Select Box ======-->

                                                <label class="gl-label" for="billing-state">VILLAGE *</label><select class="select-box select-box--primary-style" id="village" data-bill="" required>
                                                    <option selected value="">Choose Cell first</option>
                                                </select>
                                                <!--====== End - Select Box ======-->
                                            </div>


                                            <!--====== Street Address ======-->
                                            <div class="u-s-m-b-15">

                                                <label class="gl-label" for="billing-street">STREET ADDRESS *</label>

                                                <input class="input-text input-text--primary-style" type="text" id="street" placeholder="House name or street name" data-bill="" required></div>
                                            <div class="u-s-m-b-15">

                                                <label for="billing-street-optional">OTHER DESCRIBED ADDRESS (Optional)</label>

                                                <input class="input-text input-text--primary-style" type="text" id="described_address" placeholder="Road, apartment, suite unit etc. Anything you can say to describe your address (optional)" data-bill=""></div>
                                            <!--====== End - Street Address ======-->


                                            <div>

                                                <button class="btn btn--e-transparent-brand-b-2" type="submit">SAVE</button></div>
                                        </div>
                                    </form>

									</div>
                                                    </div>
                                            
                                            
                                            <input type="text" id="result_response" style="display: none;">
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