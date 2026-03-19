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

                                                    <a href="index.php?my-profile">My Profile</a></li>
                                                <!--<li>

                                                    <a href="index.php?address-book">Address Book</a></li>-->
                                                <li>

                                                    <a href="index.php?track-my-order">Track Order</a></li>
                                                <li>

                                                    <a class="dash-active" href="index.php?my-orders">My Orders</a></li>
                                                
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
                                            <h1 class="dash__h1 u-s-m-b-14">My Orders</h1>

                                            <span class="dash__text u-s-m-b-30">Here you can see all orders you have placed.</span>
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
                                            <div class="m-order__list">
                                            
<?php
       
$sql53="SELECT o.order_id,o.first_name,o.last_name,o.email,o.phone_no,o.order_number,o.cart_id,o.address_id,o.shipping_amount,o.sub_total_amount,o.total_amount,o.order_description,o.order_date,o.estimated_delivery_date,o.shipped_date,o.delivered_date,o.status from orders o, cart c where o.cart_id=c.cart_id and c.customer_id='$user_id' and o.status not like 'Pending_payment' GROUP BY o.order_id ORDER BY o.order_date DESC";
                      $result53=$conn->query($sql53);
                      $a = 1;
                      while ($row53 = $result53->fetch_assoc()) {
                        $order_id =$row53['order_id'];
                        $first_name =$row53['first_name'];
                        $last_name=$row53['last_name'];
                        $email =$row53['email'];
                        $phone_no =$row53['phone_no'];
                        $order_number =$row53['order_number'];
                        $cart_id =$row53['cart_id'];
                      $address_id =$row53['address_id'];
                      $shipping_amount =$row53['shipping_amount'];
                      $sub_total_amount =$row53['sub_total_amount'];
                      $total_amount =$row53['total_amount'];
                      $order_description =$row53['order_description'];
                      $order_date =$row53['order_date'];
                      $estimated_delivery_date =$row53['estimated_delivery_date'];
                      $shipped_date =$row53['shipped_date'];
                      $delivered_date =$row53['delivered_date'];
                      $status =$row53['status'];

                        $id1="Mine".$a;
                        $id2="Mine1".$a;
                        $id3="Mine2".$a;
                        $id4="Mine3".$a;
                        $id5="Mine4".$a;

                        ?>                                            
                                            
                                                <div class="m-order__get">
                                                    <div class="manage-o__header u-s-m-b-30">
                                                        <div class="dash-l-r">
                                                            <div>
                                                                <div class="manage-o__text-2 u-c-secondary">Order #<?php echo $order_number ?></div>
                                                                <div class="manage-o__text u-c-silver">Placed on <?php echo $order_date ?></div>
                                                            </div>
                                                            <div>
                                                                <div class="dash__link dash__link--brand">

                                                                    <a href="index.php?track-order&order_number=<?php echo $order_number ?>&phone_number=<?php echo $phone_no ?>">MANAGE</a></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="manage-o__description">
                                                        <div class="description__container">
                                                            <div class="description-title">Total: <?php echo number_format( $total_amount, 0 ); ?> RWF</div>
                                                        </div>
                                                        <div class="description__info-wrap">
                                                            <div>
                                                            
                          <?php if ($status === 'Processing') { ?>
                            <span class="manage-o__badge badge--processing"><?php echo $status; ?></span>
                          <?php } ?>
                          <?php if ($status === 'Shipped') { ?>
                            <span class="manage-o__badge badge--shipped"><?php echo $status; ?></span>
                          <?php } ?>
                          <?php if ($status === 'Delivered') { ?>
                            <span class="manage-o__badge badge--delivered"><?php echo $status; ?></span>
                          <?php } ?>  
                          <?php if ($status === 'Canceled') { ?>
                            <span class="manage-o__badge badge--canceled" style="background:red;color:white;"><?php echo $status; ?></span>
                          <?php } ?>  
                                                                
                                                        	</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            
                                            <?php } ?>
                                            
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