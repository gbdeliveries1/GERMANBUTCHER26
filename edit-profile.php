        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end flex-wrap">
                  <div class="mr-md-3 mr-xl-5">
                    <h2>Edit prodile</h2>
                    <p class="mb-md-0">Edit your profile.</p>
                  </div>
                  <div class="d-flex">
                    <i class="mdi mdi-home text-muted hover-cursor"></i>
                    <p class="text-muted mb-0 hover-cursor">&nbsp;/&nbsp;Dashboard&nbsp;/&nbsp;</p>
                    <p class="text-primary mb-0 hover-cursor">Edit profile</p>
                  </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-end flex-wrap">
                  <!--<button type="button" class="btn btn-light bg-white btn-icon mr-3 d-none d-md-block ">
                    <i class="mdi mdi-download text-muted"></i>
                  </button>
                  <button type="button" class="btn btn-light bg-white btn-icon mr-3 mt-2 mt-xl-0">
                    <i class="mdi mdi-clock-outline text-muted"></i>
                  </button>
                  <button type="button" class="btn btn-light bg-white btn-icon mr-3 mt-2 mt-xl-0">
                    <i class="mdi mdi-plus text-muted"></i>
                  </button> -->
                  <a href="index.php?profile"><button class="btn btn-primary mt-2 mt-xl-0"><i class="mdi mdi-clock-outline"></i> Your profile</button></a>
                </div>

              </div>
            </div>
          </div>

          <!--
          <div class="row">
            <div class="col-md-7 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Cash deposits</p>
                  <p class="mb-4">To start a blog, think of a topic about and first brainstorm party is ways to write details</p>
                  <div id="cash-deposits-chart-legend" class="d-flex justify-content-center pt-3"></div>
                  <canvas id="cash-deposits-chart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-md-5 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Total sales</p>
                  <h1>$ 28835</h1>
                  <h4>Gross sales over the years</h4>
                  <p class="text-muted">Today, many people rely on computers to do homework, work, and create or store useful information. Therefore, it is important </p>
                  <div id="total-sales-chart-legend"></div>                  
                </div>
                <canvas id="total-sales-chart"></canvas>
              </div>
            </div>
          </div>
        -->
        <div class="row">
          <div class="col-12 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">Edit profile</h4>
                
                <form class="forms-sample" method="post" action="#/" onsubmit="edit_profile_user();return false;">
                
                  <input type="text" id="user_id" value="<?php echo $user_id; ?>" style="display: none;">

                  <div class="form-group">
                    <label>First Name</label>
                    <input type="text" class="form-control" id="first_name" value="<?php echo $first_name; ?>" placeholder="First Name" required>
                  </div>
                  <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" id="last_name" value="<?php echo $last_name; ?>" placeholder="Last Name" required>
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" id="email" value="<?php echo $email; ?>" placeholder="email" required>
                  </div>
                  <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" id="phone_no" value="<?php echo $phone_no; ?>" placeholder="Phone Number (07...)" maxlength="10" minlength="10" onkeypress="return isNumber(event)" required>
                  </div>

                  <input type="text" id="result_response" style="display: none;">
                  <button type="submit" class="btn btn-primary mr-2">Update profile</button>
                </form>
                
              </div>
            </div>
          </div>
        </div>
      </div>