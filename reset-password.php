<?php

if (isset($_GET['user'])) {
    $user_id = $_GET['user'];
} else {
    $user_id = '';
}

    $sql4="SELECT * from user where user_id='$user_id'";
    $result4=$conn->query($sql4);
    while ($row4 = $result4->fetch_assoc()) {

      $user_id=$row4['user_id'];
      $first_name=$row4['first_name'];
      $last_name=$row4['last_name'];
      $email=$row4['email'];
      $username=$row4['username'];
      $status=$row4['status'];
    
    }

?>
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

                                        <a href="index.php?lost-password">Reset password</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--====== End - Section 1 ======-->


            <!--====== Section 2 ======-->
            <div class="u-s-p-b-60">

                <!--====== Section Intro ======-->
                <div class="section__intro u-s-m-b-60">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section__text-wrap">
                                    <h1 class="section__heading u-c-secondary">PASSWORD RESET</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--====== End - Section Intro ======-->


                <!--====== Section Content ======-->
                <div class="section__content">
                    <div class="container">
                        <div class="row row--center">
                            <div class="col-lg-6 col-md-8 u-s-m-b-30">
                                <div class="l-f-o">
                                    <div class="l-f-o__pad-box">
                                        
                                        <h1 class="gl-h1"><?php echo $email ?></h1>

                                        <form class="l-f-o__form" method="post" action="#/" onsubmit="reset_password('<?php echo $user_id; ?>');return false;">
                                            
                                            <div class="u-s-m-b-30">
                                                <label class="gl-label" for="login-email">NEW PASSWORD *</label>
                                                <input class="input-text input-text--primary-style" type="password" id="password" placeholder="Password" required><span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                        	</div>
                                        	<div class="u-s-m-b-30">
                                                <label class="gl-label" for="login-email">CONFIRM PASSWORD *</label>
                                                <input class="input-text input-text--primary-style" type="password" id="conf_password" placeholder="Confirm password" required><span toggle="#conf_password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                        	</div>
                                            
                                            <div class="gl-inline">
                                                <div class="u-s-m-b-30">
                                                    <input type="text" id="result_response" style="display: none;">
                                                    <input type="text" id="redirect_link" style="display: none;">
                                                    <button class="btn btn--e-transparent-brand-b-2" type="submit">SUBMIT</button>
                                            	</div>
                                            	<div class="u-s-m-b-30">
                                                    <a class="gl-link" href="index.php?sign-in">Back to Login</a>
                                            	</div>
                                            </div>
                                        </form>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--====== End - Section Content ======-->
            </div>
            <!--====== End - Section 2 ======-->
